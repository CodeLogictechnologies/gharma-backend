<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\Itemvariation;
use App\Models\BackPanel\PurchaseVoucher;
use App\Models\BackPanel\Vendor;
use App\Services\WebPushNotifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Exception;

class PurchaseVoucherController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view.purchase-voucher')) {
            abort(403);
        }

        return view('backend.purchase-voucher.index');
    }

    public function list(Request $request)
    {
        try {
            if (!auth()->user()->can('view.purchase-voucher')) {
                throw new Exception('You do not have permission to view this data.');
            }

            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data = PurchaseVoucher::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs    = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);

            $purchaseTypeLabels = [
                'trading'                     => 'Trading',
                'non_trading_capitalized'     => 'Non-Trading-Capitalized',
                'non_trading_non_capitalized' => 'Non-Trading-Non-Capitalized',
            ];

            foreach ($data as $row) {
                $array[$i]["sno"]          = $i + 1;
                $array[$i]["voucher_no"]   = $row->voucher_no;
                $array[$i]["voucher_date"] = $row->voucher_date;
                $array[$i]["vendor"]       = $row->vendor_name . ($row->vendor_pan ? ' (' . $row->vendor_pan . ')' : '');
                $array[$i]["type"]         = $purchaseTypeLabels[$row->purchase_type] ?? $row->purchase_type;
                $array[$i]["qty"]          = $row->total_qty !== null ? rtrim(rtrim(number_format($row->total_qty, 2), '0'), '.') : '-';
                $array[$i]["rate"]         = ((int) $row->item_count === 1 && $row->single_rate !== null) ? number_format($row->single_rate, 2) : '-';

                if ((int) $row->item_count !== 1) {
                    $array[$i]["excise_duty"] = '-';
                } elseif ($row->single_excise_type === 'percentage') {
                    $array[$i]["excise_duty"] = number_format($row->single_excise_percentage, 2) . '%';
                } elseif ($row->single_excise_type === 'fixed') {
                    $array[$i]["excise_duty"] = 'Rs ' . number_format($row->single_excise_value, 2) . '/unit';
                } else {
                    $array[$i]["excise_duty"] = 'N/A';
                }
                $array[$i]["vat"]          = $row->vat_amount > 0 ? 'Taxable' : 'Non-Taxable';
                $array[$i]["total"]        = number_format($row->total_amount, 2);

                $action  = '<a href="javascript:;" title="View Data" class="tooltipdiv viewPurchaseVoucher" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
                if (auth()->user()->can('edit.purchase-voucher')) {
                    $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editPurchaseVoucher" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                }
                if (auth()->user()->can('delete.purchase-voucher')) {
                    $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deletePurchaseVoucher" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                }

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
            $action = !empty($request->id) ? 'edit.purchase-voucher' : 'add.purchase-voucher';

            if (!auth()->user()->can($action)) {
                throw new Exception('You do not have permission to perform this action.');
            }

            $post = $request->all();
            $post['orgid'] = session('orgid');

            $items          = Item::getItem($post);
            $itemVariations = Itemvariation::getProductCodes($post);
            $vendors        = Vendor::getVendor($post);

            $data = [
                'items'          => $items,
                'itemVariations' => $itemVariations,
                'vendors'        => $vendors,
            ];

            if (!empty($request->id)) {
                $result = PurchaseVoucher::getData($post);
                if (!$result) {
                    throw new Exception("Purchase voucher not found", 1);
                }

                $data['id']                     = $result->id;
                $data['voucher_date']           = Carbon::parse($result->voucher_date)->format('Y-m-d');
                $data['voucher_no']             = $result->voucher_no;
                $data['purchase_type']          = $result->purchase_type;
                $data['vendor_id']              = $result->vendor_id;
                $data['pan']                    = $result->pan;
                $data['remarks']                = $result->remarks;
                $data['bill_discount_percent']  = $result->bill_discount_percent;
                $data['lineItems']              = $result->items;
            } else {

                $data['voucher_no'] = PurchaseVoucher::getVoucherNumber($post);
                $data['vendorVouchers'] = [];
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.purchase-voucher.form', $data);
    }

    public function save(Request $request)
    {
        try {
            $action = !empty($request->id) ? 'edit.purchase-voucher' : 'add.purchase-voucher';

            if (!auth()->user()->can($action)) {
                return response()->json(['type' => 'error', 'message' => 'You do not have permission to perform this action.']);
            }

            $allowedVatRates = array_values(array_unique(array_merge(
                [(float) config('vat.non-taxable')],
                array_map('floatval', config('vat.taxable'))
            )));

            $rules = [
                'voucher_date'    => 'required|date',
                // 'voucher_no'      => 'required|string|max:255',
                'purchase_type'   => 'required|in:trading,non_trading_capitalized,non_trading_non_capitalized',
                'vendor_id'       => 'required',
                'items'           => 'required|array|min:1',
                'items.*.item_id' => 'required',
                'items.*.qty'     => 'required|numeric|min:0.01',
                'items.*.unit_rate' => 'required|numeric|min:0',
                'items.*.vat_percent' => ['nullable', Rule::in($allowedVatRates)],
            ];

            $messages = [
                'voucher_date.required'     => 'Date is required.',
                // 'voucher_no.required'       => 'Bill / Voucher No. is required.',
                'purchase_type.required'    => 'Purchase type is required.',
                'vendor_id.required'        => 'Vendor is required.',
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

            // $post           = $request->all();
            // $post['orgid']  = session('orgid');
            // $post['userid'] = session('userid');

            // $isEdit = !empty($post['id']);

            // $duplicate = DB::table('purchase_vouchers')
            //     ->where('orgid', $post['orgid'])
            //     ->where('voucher_no', $post['voucher_no'])
            //     ->where('status', 'Y')
            //     ->when($isEdit, fn($q) => $q->where('id', '!=', $post['id']))
            //     ->exists();

            // if ($duplicate) {
            //     return response()->json([
            //         'type'    => 'error',
            //         'message' => 'This Bill / Voucher No. already exists.',
            //     ]);
            // }

            // PurchaseVoucher::saveData($post);
            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            $isEdit = !empty($post['id']);

            PurchaseVoucher::saveData($post);

            WebPushNotifier::notifyLowStock($post['orgid']);

            return response()->json([
                'type'    => 'success',
                'message' => $isEdit ? 'Purchase voucher updated successfully.' : 'Purchase voucher saved successfully.',
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
            if (!auth()->user()->can('view.purchase-voucher')) {
                throw new Exception('You do not have permission to view this record.');
            }

            $post = $request->all();
            $post['orgid'] = session('orgid');

            $voucherDetail = PurchaseVoucher::getData($post);

            $data['voucherDetail'] = $voucherDetail;
            $data['type']    = 'success';
            $data['message'] = 'Successfully fetched purchase voucher.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }

        return view('backend.purchase-voucher.view', $data);
    }

    public function delete(Request $request)
    {
        try {
             if (!auth()->user()->can('delete.purchase-voucher')) {
                throw new Exception('You do not have permission to delete this record.');
            }
            
            $type = 'success';
            $message = 'Purchase voucher deleted successfully';

            $post = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            DB::beginTransaction();
            PurchaseVoucher::deleteDate($post);
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
