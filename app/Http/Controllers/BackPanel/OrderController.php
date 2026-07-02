<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Order;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;
use App\Models\EnumType;

class OrderController extends Controller
{
    public function index()
    {
        return view('backend.order.index', [
            'statuses' => EnumType::orderStatuses(),
        ]);
    }

    public function list(Request $request)
{
    $post   = $request->all();
    $offset = (int) ($request->input('iDisplayStart', 0));

    $data         = Order::list($post);
    $result       = $data['data'];
    $totalrecs    = $data['totalrecs'];
    $filtereddata = $data['totalfilteredrecs'];

    $i     = 0;
    $array = [];

    $statuses = EnumType::orderStatuses();

    foreach ($result as $row) {
        $array[$i]['sno']        = $offset + $i + 1;
        $array[$i]['username']   = $row->username;
        $array[$i]['email']      = $row->email;
        $array[$i]['created_at'] = $row->created_at;

        $options = '';
        foreach ($statuses as $status) {
            $selected = ($row->order_status === $status) ? 'selected' : '';
            $options .= "<option value='{$status}' {$selected}>" . ucfirst($status) . "</option>";
        }

        $array[$i]['order_status'] = "
            <select class='form-select changeStatus'
                    data-id='{$row->id}'
                    data-current='{$row->order_status}'>
                {$options}
            </select>";

        $action  = '<a href="javascript:;" title="View Order" class="tooltipdiv viewOrder" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
        $action .= ' &nbsp;<a href="javascript:;" title="Assign Driver" class="tooltipdiv assignDriver" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-user-plus"></i></a>';

        $array[$i]['action'] = $action;
        $i++;
    }

    return response()->json([
        'recordsFiltered' => $filtereddata,
        'recordsTotal'    => $totalrecs,
        'data'            => $array,
    ]);
}

    public function statusCounts()
    {
        return response()->json(Order::statusCounts());
    }

    public function view(Request $request)
    {
        try {
            $post          = $request->all();
            $post['orgid'] = session('orgid');
            $orderDetails  = Order::getData($post);
            $data = [
                'orderDetails' => $orderDetails,
                'type'         => 'success',
                'message'      => 'Successfully fetched data of order.',
            ];
        } catch (QueryException $e) {
            $data = ['type' => 'error', 'message' => $this->queryMessage];
        } catch (Exception $e) {
            $data = ['type' => 'error', 'message' => $e->getMessage()];
        }

        return view('backend.order.view', $data);
    }

    public function updateStatus(Request $request, FirebaseService $firebase)
    {
        $request->validate([
            'id'     => 'required',
            'status' => 'required'
        ]);

        $updated = DB::table('order_masters')
            ->where('id', $request->id)
            ->update(['order_status' => $request->status]);

        // DB::table('loyalties')
        //     ->where('ordermasterid', $request->id)
        //     ->update(['order_status' => $request->status]);

        $orderDetailIds = DB::table('order_details')
            ->where('ordermasterid', $request->id)
            ->pluck('id')
            ->toArray();

        if (!empty($orderDetailIds)) {
            DB::table('loyalties')
                ->whereIn('order_detail_id', $orderDetailIds)
                ->update(['order_status' => $request->status]);
        }

        if ($updated) {
            $order = DB::table('order_masters')
                ->where('id', $request->id)
                ->first();

            try {
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
                            'status'   => $request->status
                        ]
                    );
                }
            } catch (\Exception $e) {
                \Log::warning('Firebase notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'type'    => 'success',
                'message' => 'Order status updated successfully'
            ]);
        }

        return response()->json([
            'type'    => 'error',
            'message' => 'No changes made or invalid ID'
        ]);
    }
}
