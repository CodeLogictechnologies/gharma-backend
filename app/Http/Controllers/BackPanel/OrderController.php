<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Order;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;

class OrderController extends Controller
{
    public function index()
    {
        return view('backend.order.index');
    }

    public function list(Request $request)
    {
        $post = $request->all();
        $data = Order::list($post); // use your Order model, not Inventory
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);

        $statuses = [
            'Pending',
            'Confirmed',
            'Packed',
            'Shipped',
            'Delivered',
            'Cancelled',
            'Returned',
            'Refunded'
        ];
        foreach ($data as $row) {
            $array[$i]["sno"]        = $i + 1;
            $array[$i]["username"]   = $row->username;
            $array[$i]["email"]      = $row->email;
            $array[$i]["created_at"] = $row->created_at;

            // Build the status dropdown with data-current and data-id
            $options = '';
            foreach ($statuses as $status) {
                $selected = ($row->order_status === $status) ? 'selected' : '';
                $options .= "<option value='{$status}' {$selected}>" . ucfirst($status) . "</option>";
            }
            $array[$i]["order_status"] = "
            <select class='form-select changeStatus'
                    data-id='{$row->id}'
                    data-current='{$row->order_status}'>
                {$options}
            </select>";

            $action  = '<a href="javascript:;" title="View Order"
                            class="tooltipdiv viewOrder"
                            style="color:green;"
                            data-id="' . $row->id . '">
                            <i class="bx bx-show-alt"></i></a>';

                                        $action .= ' &nbsp;

                            <a href="javascript:;"
                            title="Assign Driver"
                            class="tooltipdiv assignDriver"
                            style="color:blue;"
                            data-id="' . $row->id . '">
                            <i class="bx bx-user-plus"></i>
                            </a>';
            $array[$i]["action"] = $action;
            $i++;
        }

        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs)    $totalrecs    = 0;

        return json_encode([
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array
        ]);
    }

    public function view(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $orderDetails = Order::getData($post);
            $data = [
                'orderDetails' => $orderDetails,
            ];
            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of order.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.order.view', $data);
    }


    public function updateStatus(Request $request, FirebaseService $firebase)
    {
        // ✅ Validate input
        $request->validate([
            'id' => 'required',
            'status' => 'required'
        ]);

        // ✅ Update order status
        $updated = DB::table('order_masters')
            ->where('id', $request->id)
            ->update([
                'order_status' => $request->status
            ]);

        $updateLoyalty = DB::table('loyalties')
            ->where('ordermasterid', $request->id)
            ->update([
                'order_status' => $request->status
            ]);

        if ($updated) {

            $order = DB::table('order_masters')
                ->where('id', $request->id)
                ->first();

            $token = DB::table('user_devices')
                ->where('user_id', $order->userid)
                ->value('device_token');

            if ($token) {
                $firebase->sendNotification(
                    $token,
                    "Order Update",
                    "Your order status changed to " . $request->status,
                    [
                        'order_id' => $request->id,
                        'status' => $request->status
                    ]
                );
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Order status updated successfully'
            ]);
        }

        return response()->json([
            'type' => 'error',
            'message' => 'No changes made or invalid ID'
        ]);
    }
}