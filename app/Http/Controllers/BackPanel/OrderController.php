<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Order;
use App\Models\BackPanel\SalesVoucher;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;
use App\Models\EnumType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $post['orgid'] = session('orgid');

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
            $action .= ' &nbsp;<a href="javascript:;" title="Invoice" class="tooltipdiv viewInvoice" style="color:#e67e22;" data-id="' . $row->id . '"><i class="bx bx-receipt"></i></a>';

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
        return response()->json(Order::statusCounts(session('orgid')));
    }

    public function view(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $orderDetail = SalesVoucher::getData($post);

            $voucherDetail = SalesVoucher::getVoucherData($post);

            $data['orderDetail'] = $orderDetail;
            $data['voucherDetail'] = $voucherDetail;
            $data['type']    = 'success';
            $data['message'] = 'Successfully fetched sales voucher.';
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
        $request->validate([
            'id'     => 'required',
            'status' => 'required'
        ]);

        $updated = DB::table('order_masters')
            ->where('id', $request->id)
            ->where('orgid', session('orgid'))
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
                ->where('orgid', session('orgid'))
                ->first();

            try {
                $token = DB::table('user_devices')
                    ->where('user_id', $order->userid)
                    ->where('orgid', session('orgid'))
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

    public function generateInvoice(Request $request)
    {
        try {
            $post = $request->all();
            $post['id'] = $post['order_id'];

            $orderDetail   = SalesVoucher::getData($post);
            $voucherDetail = SalesVoucher::getVoucherData($post);

            if ($orderDetail->isEmpty() || !$voucherDetail) {
                throw new Exception('Order not found.');
            }
            $data = [
                'orderDetail'   => $orderDetail,
                'voucherDetail' => $voucherDetail,
            ];

            $pdf = Pdf::loadView('backend.order.invoice_pdf', $data)
                ->setPaper('a4', 'portrait');

            $fileName = 'invoice_' . $post['order_id'] . '_' . time() . '.pdf';

            Storage::disk('public')->put('invoices/' . $fileName, $pdf->output());

            return response()->json([
                'type'    => 'success',
                'message' => 'Invoice generated successfully.',
                'url'     => asset('storage/invoices/' . $fileName),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }
}