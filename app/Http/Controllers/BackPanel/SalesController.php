<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\SalesVoucher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class SalesController extends Controller
{
    public function index()
    {
        return view('backend.sales.index');
    }

    public function list(Request $request)
    {
        // try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data = SalesVoucher::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs    = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);

            foreach ($data['data'] as $row) {
                $array[$i]["sno"]       = $i + 1;
                $array[$i]["name"]     = $row->name;
                $array[$i]["phone"]  = $row->phone;
                $array[$i]["email"]     = $row->email;
                $array[$i]["voucher_number"]     = $row->voucher_number;
                $array[$i]["created_at"]     = $row->created_at;
                $action  = '';
                $action .= '<a href="javascript:;" title="View"   class="tooltipdiv viewSalesVoucher   " style="color:green;font-size:18px;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a> ';
                $array[$i]["action"] = $action;
                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs)    $totalrecs    = 0;

            return response()->json([
                'recordsFiltered' => $filtereddata,
                'recordsTotal'    => $totalrecs,
                'data'            => $array,
            ]);
        // } catch (QueryException $e) {
        //     return response()->json(['recordsFiltered' => 0, 'recordsTotal' => 0, 'data' => []], 500);
        // } catch (Exception $e) {
        //     return response()->json(['recordsFiltered' => 0, 'recordsTotal' => 0, 'data' => []], 500);
        // }
    }

    public function form(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $items     = Item::getItem($post);
            $customers = User::getUserData($post);

            $data = [
                'items'     => $items,
                'customers' => $customers,
            ];

            if (!empty($request->id)) {
                $result = SalesVoucher::getData($post);
                if (!$result) {
                    throw new Exception("Sales voucher not found", 1);
                }

                $data['id']                    = $result->id;
                $data['voucher_date']          = Carbon::parse($result->voucher_date)->format('Y-m-d');
                $data['voucher_no']            = $result->voucher_no;
                $data['customer_id']           = $result->customer_id;
                $data['order_id']              = $result->order_id;
                $data['remarks']               = $result->remarks;
                $data['bill_discount_percent'] = $result->bill_discount_percent;
                $data['lineItems']             = $result->items;
                $data['customerOrders']        = !empty($result->customer_id)
                    ? SalesVoucher::getCustomerOrders([
                        'orgid'              => $post['orgid'],
                        'customer_id'        => $result->customer_id,
                        'exclude_voucher_id' => $result->id,
                    ])
                    : [];
            } else {
                $data['voucher_no']     = SalesVoucher::generateUniqueVoucherNo($post['orgid']);
                $data['customerOrders'] = [];
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.sales.form', $data);
    }

    public function customerOrders(Request $request)
    {
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['exclude_voucher_id'] = $request->exclude_voucher_id;

        $orders = empty($post['customer_id']) ? [] : SalesVoucher::getCustomerOrders($post);

        return response()->json($orders);
    }

    public function itemPrice(Request $request)
    {
        $post = $request->all();
        $post['orgid'] = session('orgid');

        $pricing = SalesVoucher::getItemPricing($post);

        return response()->json($pricing);
    }

    public function orderItems(Request $request)
    {
        $orgid = session('orgid');

        if (empty($request->order_id)) {
            return response()->json(['items' => []]);
        }

        $items = SalesVoucher::getOrderItems([
            'orgid'    => $orgid,
            'order_id' => $request->order_id,
        ]);

        return response()->json(['items' => $items]);
    }

    public function save(Request $request)
    {
        try {
            $rules = [
                'voucher_date'      => 'required|date',
                'voucher_no'        => 'required|string|max:255',
                'customer_id'       => 'required',
                'items'             => 'required|array|min:1',
                'items.*.item_id'   => 'required',
                'items.*.qty'       => 'required|numeric|min:0.01',
                'items.*.unit_rate' => 'required|numeric|min:0',
            ];

            $messages = [
                'voucher_date.required'     => 'Date is required.',
                'voucher_no.required'       => 'Bill / Voucher No. is required.',
                'customer_id.required'      => 'Customer is required.',
                'items.required'            => 'At least one item is required.',
                'items.*.item_id.required'  => 'Item is required for each row.',
                'items.*.qty.required'      => 'Quantity is required for each row.',
                'items.*.unit_rate.required' => 'Rate is required for each row.',
            ];

            $validation = Validator::make($request->all(), $rules, $messages);
            if ($validation->fails()) {
                return response()->json([
                    'type'    => 'error',
                    'message' => $validation->errors()->first(),
                ]);
            }

            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            $isEdit = !empty($post['id']);

            $duplicate = DB::table('sales_vouchers')
                ->where('orgid', $post['orgid'])
                ->where('voucher_no', $post['voucher_no'])
                ->where('status', 'Y')
                ->when($isEdit, fn($q) => $q->where('id', '!=', $post['id']))
                ->exists()
                || DB::table('order_masters')
                    ->where('orgid', $post['orgid'])
                    ->where('voucher_number', $post['voucher_no'])
                    ->exists();

            if ($duplicate) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'This Bill / Voucher No. already exists.',
                ]);
            }

            SalesVoucher::saveData($post);

            return response()->json([
                'type'    => 'success',
                'message' => $isEdit ? 'Sales voucher updated successfully.' : 'Sales voucher saved successfully.',
            ]);
        } catch (QueryException $e) {
            return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function view(Request $request)
    {
        // try {
        $post = $request->all();
        $post['orgid'] = session('orgid');

        $orderDetail = SalesVoucher::getData($post);

        $voucherDetail = SalesVoucher::getVoucherData($post);

        $data['orderDetail'] = $orderDetail;
        $data['voucherDetail'] = $voucherDetail;
        $data['type']    = 'success';
        $data['message'] = 'Successfully fetched sales voucher.';
        // } catch (QueryException $e) {
        //     $data['type'] = 'error';
        //     $data['message'] = $this->queryMessage;
        // } catch (Exception $e) {
        //     $data['type'] = 'error';
        //     $data['message'] = $e->getMessage();
        // }

        return view('backend.sales.view', $data);
    }

    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = 'Sales voucher deleted successfully';

            $post = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            DB::beginTransaction();
            SalesVoucher::deleteDate($post);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }
}