<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\Order;
use App\Models\BackPanel\SalesReturnVoucher;
use App\Models\BackPanel\SalesVoucher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class SalesReturnController extends Controller
{
    public function index()
    {
        return view('backend.sales-return.index');
    }

    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data = SalesReturnVoucher::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs    = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);

            foreach ($data as $row) {
                $array[$i]["sno"]            = $i + 1;
                $array[$i]["credit_note_no"] = $row->credit_note_no;
                $array[$i]["return_date"]    = $row->return_date;
                $array[$i]["customer"]       = $row->customer_name;
                $array[$i]["against_voucher"] = $row->against_voucher_no ?? '-';
                $array[$i]["qty"]            = $row->total_qty !== null ? rtrim(rtrim(number_format($row->total_qty, 2), '0'), '.') : '-';
                $array[$i]["rate"]           = ((int) $row->item_count === 1 && $row->single_rate !== null) ? number_format($row->single_rate, 2) : '-';
                $array[$i]["vat"]            = $row->vat_amount > 0 ? 'Taxable' : 'Non-Taxable';
                $array[$i]["total"]          = number_format($row->total_amount, 2);

                $statusBadges = [
                    'Pending'  => '<span class="badge bg-label-warning">Pending</span>',
                    'Approved' => '<span class="badge bg-label-success">Approved</span>',
                    'Rejected' => '<span class="badge bg-label-danger">Rejected</span>',
                ];
                $array[$i]["status"] = $statusBadges[$row->return_status] ?? $row->return_status;

                $action = '';
                if ($row->return_status === 'Pending') {
                    $action .= '<a href="javascript:;" title="Approve" class="tooltipdiv approveSalesReturn" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-check-circle"></i></a>';
                    $action .= '<a href="javascript:;" title="Reject" class="tooltipdiv rejectSalesReturn" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-x-circle"></i></a>';
                }
                $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewSalesReturn" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editSalesReturn" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteSalesReturn px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';

                $array[$i]["action"] = $action;
                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs)    $totalrecs    = 0;
        } catch (QueryException $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        }

        return json_encode(["recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array]);
    }

    public function form(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $items     = Item::getItem($post);
            $customers = User::getUserData($post);

            $getVoucher = Order::getVoucher($post);

            $data = [
                'items'   => $items,
                'getVoucher'   => $getVoucher,
                'customers' => $customers,
            ];

            if (!empty($request->id)) {
                $result = SalesReturnVoucher::getData($post);
                if (!$result) {
                    throw new Exception("Sales return voucher not found", 1);
                }

                $data['id']                    = $result->id;
                $data['return_date']           = Carbon::parse($result->return_date)->format('Y-m-d');
                $data['credit_note_no']        = $result->credit_note_no;
                $data['customer_id']           = $result->customer_id;
                $data['against_voucher_id']    = $result->against_voucher_id;
                $data['remarks']               = $result->remarks;
                $data['bill_discount_percent'] = $result->bill_discount_percent;
                $data['customer_name']         = $result->customer_name;
                $data['lineItems']             = $result->items;
                $data['customerVouchers']  = !empty($result->customer_id)
                    ? SalesVoucher::getCustomerVouchers([
                        'orgid'             => $post['orgid'],
                        'customer_id'       => $result->customer_id,
                        'exclude_return_id' => $result->id,
                    ])
                    : [];
            } else {
                $data['credit_note_no']     = SalesReturnVoucher::generateUniqueVoucherNo($post);
                $data['customerVouchers'] = [];
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.sales-return.form', $data);
    }

    public function customerVouchers(Request $request)
    {
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['exclude_return_id'] = $request->exclude_return_id;

        $vouchers = empty($post['customer_id']) ? [] : SalesVoucher::getCustomerVouchers($post);

        return response()->json($vouchers);
    }

    public function voucherItems(Request $request)
    {
        $post = $request->all();
        $post['orgid'] = session('orgid');

        $voucher = DB::table('order_masters')
            ->where('id', $post['voucher_id'])
            ->where('orgid', $post['orgid'])
            ->first();

        if (!$voucher) {
            return response()->json(['items' => [], 'bill_discount_percent' => 0], 404);
        }

        $items = DB::table('order_details as od')
            ->join('itemvariations as v', 'v.id', '=', 'od.variation_id')
            ->join('items as i', 'i.id', '=', 'v.item_id')
            ->where('od.ordermasterid', $post['voucher_id'])
            ->select(
                'i.id as item_id',
                'i.title',
                'v.id as variation_id',
                'v.value',
                'od.price as unit_rate',
                'od.quantity as qty',
                'od.order_detail_total_price'
            )
            ->get();

        return response()->json([
            'bill_discount_percent' => $voucher->bill_discount_percent ?? 0,
            'items'                 => $items,
        ]);
    }

    public function updateStatus(Request $request)
    {
        try {
            $rules = [
                'id'            => 'required',
                'return_status' => 'required|in:Approved,Rejected',
            ];

            $validation = Validator::make($request->all(), $rules);
            if ($validation->fails()) {
                return response()->json([
                    'type'    => 'error',
                    'message' => $validation->errors()->first(),
                ]);
            }

            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            SalesReturnVoucher::updateStatus($post);

            return response()->json([
                'type'    => 'success',
                'message' => 'Sales return marked as ' . $post['return_status'] . '.',
            ]);
        } catch (QueryException $e) {
            return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        try {
            $rules = [
                'return_date'       => 'required|date',
                'credit_note_no'    => 'required|string|max:255',
                'customer_id'       => 'required',
                'items'             => 'required|array|min:1',
                'items.*.item_id'   => 'required',
                'items.*.qty'       => 'required|numeric|min:0.01',
                'items.*.unit_rate' => 'required|numeric|min:0',
            ];

            $messages = [
                'return_date.required'      => 'Date is required.',
                'credit_note_no.required'   => 'Credit Note No. is required.',
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

            $duplicate = DB::table('sales_return_vouchers')
                ->where('orgid', $post['orgid'])
                ->where('credit_note_no', $post['credit_note_no'])
                ->where('status', 'Y')
                ->when($isEdit, fn($q) => $q->where('id', '!=', $post['id']))
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'This Credit Note No. already exists.',
                ]);
            }

            SalesReturnVoucher::saveData($post);

            return response()->json([
                'type'    => 'success',
                'message' => $isEdit ? 'Sales return updated successfully.' : 'Sales return saved successfully.',
            ]);
        } catch (QueryException $e) {
            return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function view(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $voucherDetail = SalesReturnVoucher::getData($post);

            $data['voucherDetail'] = $voucherDetail;
            $data['type']    = 'success';
            $data['message'] = 'Successfully fetched sales return voucher.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }

        return view('backend.sales-return.view', $data);
    }

    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = 'Sales return voucher deleted successfully';

            $post = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            DB::beginTransaction();
            SalesReturnVoucher::deleteDate($post);
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


    public function voucherCustomer(Request $request)
    {
        // try {
        // dd($request->all());
        $voucher = DB::table('order_masters')
            ->join('users', 'users.id', '=', 'order_masters.userid')
            ->where('order_masters.id', $request->voucher_id)
            ->select('users.id as customer_id', 'users.name as customer_name')
            ->first();

        if (!$voucher) {
            return response()->json(['customer_id' => null]);
        }


        return response()->json([
            'customer_id'   => $voucher->customer_id,
            'customer_name' => $voucher->customer_name,
        ]);
        // } catch (\Exception $e) {
        //     return response()->json(['customer_id' => null], 500);
        // }
    }
}