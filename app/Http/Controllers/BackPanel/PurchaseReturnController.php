<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\Itemvariation;
use App\Models\BackPanel\PurchaseReturnVoucher;
use App\Models\BackPanel\PurchaseVoucher;
use App\Models\BackPanel\Vendor;
use App\Services\WebPushNotifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        return view('backend.purchase-return.index');
    }

    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data = PurchaseReturnVoucher::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs    = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);

            foreach ($data as $row) {
                $array[$i]["sno"]           = $i + 1;
                $array[$i]["debit_note_no"] = $row->debit_note_no;
                $array[$i]["return_date"]   = $row->return_date;
                $array[$i]["vendor"]        = $row->vendor_name . ($row->vendor_pan ? ' (' . $row->vendor_pan . ')' : '');
                $array[$i]["against_voucher"] = $row->against_voucher_no ?? '-';
                $array[$i]["qty"]           = $row->total_qty !== null ? rtrim(rtrim(number_format($row->total_qty, 2), '0'), '.') : '-';
                $array[$i]["rate"]          = ((int) $row->item_count === 1 && $row->single_rate !== null) ? number_format($row->single_rate, 2) : '-';

                if ((int) $row->item_count !== 1) {
                    $array[$i]["excise_duty"] = '-';
                } elseif ($row->single_excise_type === 'percentage') {
                    $array[$i]["excise_duty"] = number_format($row->single_excise_percentage, 2) . '%';
                } elseif ($row->single_excise_type === 'fixed') {
                    $array[$i]["excise_duty"] = 'Rs ' . number_format($row->single_excise_value, 2) . '/unit';
                } else {
                    $array[$i]["excise_duty"] = 'N/A';
                }
                $array[$i]["vat"]           = $row->vat_amount > 0 ? 'Taxable' : 'Non-Taxable';
                $array[$i]["total"]         = number_format($row->total_amount, 2);

                $statusBadges = [
                    'Pending'  => '<span class="badge bg-label-warning">Pending</span>',
                    'Approved' => '<span class="badge bg-label-success">Approved</span>',
                    'Rejected' => '<span class="badge bg-label-danger">Rejected</span>',
                ];
                $array[$i]["status"] = $statusBadges[$row->return_status] ?? $row->return_status;

                $action = '';
                if ($row->return_status === 'Pending') {
                    $action .= '<a href="javascript:;" title="Approve" class="tooltipdiv approvePurchaseReturn" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-check-circle"></i></a>';
                    $action .= '<a href="javascript:;" title="Reject" class="tooltipdiv rejectPurchaseReturn" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-x-circle"></i></a>';
                }
                $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewPurchaseReturn" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editPurchaseReturn" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deletePurchaseReturn" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';

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

            $items          = Item::getItem($post);
            $itemVariations = Itemvariation::getProductCodes($post);
            $vendors        = Vendor::getVendor($post);

            $data = [
                'items'          => $items,
                'itemVariations' => $itemVariations,
                'vendors'        => $vendors,
            ];

            if (!empty($request->id)) {

                $result = PurchaseReturnVoucher::getData($post);

                if (!$result) {
                    throw new Exception("Purchase return voucher not found");
                }

                $data['id']                    = $result->id;
                $data['return_date']           = Carbon::parse($result->return_date)->format('Y-m-d');
                $data['debit_note_no']         = $result->debit_note_no;
                $data['vendor_id']             = $result->vendor_id;
                $data['against_voucher_id']    = $result->against_voucher_id;
                $data['remarks']               = $result->remarks;
                $data['bill_discount_percent'] = $result->bill_discount_percent;
                $data['lineItems']             = $result->items;

                $data['vendorVouchers'] = !empty($result->vendor_id)
                    ? PurchaseVoucher::getVendorVouchers([
                        'orgid'             => $post['orgid'],
                        'vendor_id'         => $result->vendor_id,
                        'exclude_return_id' => $result->id,
                    ])
                    : [];
            } else {

                $data['debit_note_no'] = PurchaseReturnVoucher::getVoucherNumber($post);
                $data['vendorVouchers'] = [];
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.purchase-return.form', $data);
    }

    public function vendorVouchers(Request $request)
    {
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['exclude_return_id'] = $request->exclude_return_id;

        $vouchers = empty($post['vendor_id']) ? [] : PurchaseVoucher::getVendorVouchers($post);

        return response()->json($vouchers);
    }

    public function voucherItems(Request $request)
    {
        $orgid = session('orgid');

        if (empty($request->voucher_id)) {
            return response()->json(['bill_discount_percent' => 0, 'items' => []]);
        }

        $voucher = DB::table('purchase_vouchers')
            ->select('bill_discount_percent')
            ->where('id', $request->voucher_id)
            ->where('orgid', $orgid)
            ->first();

        if (!$voucher) {
            return response()->json(['bill_discount_percent' => 0, 'items' => []]);
        }

        $items = DB::table('purchase_voucher_items as pvi')
            ->join('purchase_vouchers as pv', 'pv.id', '=', 'pvi.purchase_voucher_id')
            ->join('items as i', 'i.id', '=', 'pvi.item_id')
            ->leftJoin('itemvariations as iv', 'iv.id', '=', 'pvi.variation_id')
            ->select(
                'pvi.item_id',
                'i.title as item_title',
                'pvi.variation_id',
                'iv.attribute as variation_attribute',
                'iv.value as variation_value',
                'pvi.unit',
                'pvi.qty',
                'pvi.unit_rate'
            )
            ->where('pvi.purchase_voucher_id', $request->voucher_id)
            ->where('pv.orgid', $orgid)
            ->orderBy('pvi.created_at')
            ->get();

        return response()->json([
            'bill_discount_percent' => $voucher->bill_discount_percent,
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

            PurchaseReturnVoucher::updateStatus($post);

            return response()->json([
                'type'    => 'success',
                'message' => 'Purchase return marked as ' . $post['return_status'] . '.',
            ]);
        } catch (QueryException $e) {
            return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // public function save(Request $request)
    // {
    //     try {
    //         $rules = [
    //             'return_date'       => 'required|date',
    //             // 'debit_note_no'     => 'required|string|max:255',
    //             'vendor_id'         => 'required',
    //             'items'             => 'required|array|min:1',
    //             'items.*.item_id'   => 'required',
    //             'items.*.qty'       => 'required|numeric|min:0.01',
    //             'items.*.unit_rate' => 'required|numeric|min:0',
    //         ];

    //         $messages = [
    //             'return_date.required'      => 'Date is required.',
    //             // 'debit_note_no.required'    => 'Debit Note No. is required.',
    //             'vendor_id.required'        => 'Vendor is required.',
    //             'items.required'            => 'At least one item is required.',
    //             'items.*.item_id.required'  => 'Item is required for each row.',
    //             'items.*.qty.required'      => 'Quantity is required for each row.',
    //             'items.*.unit_rate.required' => 'Rate is required for each row.',
    //         ];

    //         $validation = Validator::make($request->all(), $rules, $messages);
    //         if ($validation->fails()) {
    //             return response()->json([
    //                 'type'    => 'error',
    //                 'message' => $validation->errors()->first(),
    //             ]);
    //         }

    //         $post           = $request->all();
    //         $post['orgid']  = session('orgid');
    //         $post['userid'] = session('userid');

    //         $isEdit = !empty($post['id']);

    //         // $duplicate = DB::table('purchase_return_vouchers')
    //         //     ->where('orgid', $post['orgid'])
    //         //     ->where('debit_note_no', $post['debit_note_no'])
    //         //     ->where('status', 'Y')
    //         //     ->when($isEdit, fn($q) => $q->where('id', '!=', $post['id']))
    //         //     ->exists();

    //         // if ($duplicate) {
    //         //     return response()->json([
    //         //         'type'    => 'error',
    //         //         'message' => 'This Debit Note No. already exists.',
    //         //     ]);
    //         // }

    //         PurchaseReturnVoucher::saveData($post);

    //         WebPushNotifier::notifyLowStock($post['orgid']);

    //         return response()->json([
    //             'type'    => 'success',
    //             'message' => $isEdit ? 'Purchase return updated successfully.' : 'Purchase return saved successfully.',
    //         ]);
    //     } catch (QueryException $e) {
    //         return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
    //     } catch (Exception $e) {
    //         return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
    //     }
    // }

    public function save(Request $request)
{
    try {
        $rules = [
            'return_date'       => 'required|date',
            // 'debit_note_no'     => 'required|string|max:255',
            'vendor_id'         => 'required',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required',
            'items.*.qty'       => 'required|numeric|min:0.01',
            'items.*.unit_rate' => 'required|numeric|min:0',
        ];

        $messages = [
            'return_date.required'      => 'Date is required.',
            // 'debit_note_no.required'    => 'Debit Note No. is required.',
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

        $post           = $request->all();
        $post['orgid']  = session('orgid');
        $post['userid'] = session('userid');

        $isEdit = !empty($post['id']);

        // Quantity being returned must not exceed what was actually purchased (minus what's already been returned)
        if (!empty($post['against_voucher_id'])) {
            $qtyByLine = [];
            foreach ($post['items'] as $it) {
                if (empty($it['item_id'])) {
                    continue;
                }
                $key = $it['item_id'] . '|' . ($it['variation_id'] ?? '');
                $qtyByLine[$key] = ($qtyByLine[$key] ?? 0) + (float) $it['qty'];
            }

            $purchasedByKey = DB::table('purchase_voucher_items')
                ->where('purchase_voucher_id', $post['against_voucher_id'])
                ->select('item_id', 'variation_id', 'qty')
                ->get()
                ->keyBy(fn($r) => $r->item_id . '|' . ($r->variation_id ?? ''));

            $returnedQuery = DB::table('purchase_return_voucher_items as prvi')
                ->join('purchase_return_vouchers as prv', 'prv.id', '=', 'prvi.purchase_return_voucher_id')
                ->where('prv.against_voucher_id', $post['against_voucher_id'])
                ->where('prv.orgid', $post['orgid'])
                ->where('prv.status', 'Y');

            if ($isEdit) {
                $returnedQuery->where('prv.id', '!=', $post['id']);
            }

            $returnedByKey = $returnedQuery
                ->select('prvi.item_id', 'prvi.variation_id', DB::raw('SUM(prvi.qty) as returned_qty'))
                ->groupBy('prvi.item_id', 'prvi.variation_id')
                ->get()
                ->keyBy(fn($r) => $r->item_id . '|' . ($r->variation_id ?? ''));

            foreach ($qtyByLine as $key => $qty) {
                $purchasedQty    = (float) ($purchasedByKey[$key]->qty ?? 0);
                $alreadyReturned = (float) ($returnedByKey[$key]->returned_qty ?? 0);
                $maxReturnable   = max(0, $purchasedQty - $alreadyReturned);

                if ($qty > $maxReturnable) {
                    [$itemId] = explode('|', $key, 2);
                    $itemTitle = DB::table('items')->where('id', $itemId)->value('title') ?? 'this item';

                    return response()->json([
                        'type'    => 'error',
                        'message' => "Only {$maxReturnable} unit(s) of {$itemTitle} can be returned (requested {$qty}).",
                    ]);
                }
            }
        }

        // $duplicate = DB::table('purchase_return_vouchers')
        //     ->where('orgid', $post['orgid'])
        //     ->where('debit_note_no', $post['debit_note_no'])
        //     ->where('status', 'Y')
        //     ->when($isEdit, fn($q) => $q->where('id', '!=', $post['id']))
        //     ->exists();

        // if ($duplicate) {
        //     return response()->json([
        //         'type'    => 'error',
        //         'message' => 'This Debit Note No. already exists.',
        //     ]);
        // }

        PurchaseReturnVoucher::saveData($post);

        WebPushNotifier::notifyLowStock($post['orgid']);

        return response()->json([
            'type'    => 'success',
            'message' => $isEdit ? 'Purchase return updated successfully.' : 'Purchase return saved successfully.',
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

            $voucherDetail = PurchaseReturnVoucher::getData($post);

            $data['voucherDetail'] = $voucherDetail;
            $data['type']    = 'success';
            $data['message'] = 'Successfully fetched purchase return voucher.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }

        return view('backend.purchase-return.view', $data);
    }

    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = 'Purchase return voucher deleted successfully';

            $post = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            DB::beginTransaction();
            PurchaseReturnVoucher::deleteDate($post);
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
