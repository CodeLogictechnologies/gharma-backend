<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SessionController;
use App\Models\BackPanel\TeamCategory;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\RetailerPrice;
use App\Models\Common;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class RetailerPriceController extends Controller
{
    // construct
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = [];
        $post['orgid'] = session('orgid');

        $items = Item::getItem($post);
        $data = [
            'items' => $items
        ];
        return view('backend.retailer.index', $data);
    }

    //function to save team category 
    public function save(Request $request)
    {
        try {

            $rules = [
                'itemid'               => 'required|uuid',
                'variationid'          => 'required|uuid',
                'price'                => 'required|numeric|min:0',
                'discount_type'        => 'nullable|in:percentage,fixed',
                'discount_percentage'  => 'required_if:discount_type,percentage|nullable|numeric|min:0|max:100',
                'discount_amount'      => 'required_if:discount_type,fixed|nullable|numeric|min:0',
                'excise_type'          => 'nullable|in:percentage,fixed',
                'excise_percentage'    => 'required_if:excise_type,percentage|nullable|numeric|min:0|max:100',
                'excise_value'         => 'required_if:excise_type,fixed|nullable|numeric|min:0',
                'vat_percent'          => 'required_if:vat_status,1|nullable|numeric|in:' . implode(',', config('vat.taxable')),
            ];

            $messages = [
                'itemid.required'      => 'Item is required.',
                'itemid.uuid'          => 'Invalid item ID format.',

                'variationid.required' => 'Variation is required.',
                'variationid.uuid'     => 'Invalid variation ID format.',

                'price.required'       => 'Price is required.',
                'price.numeric'        => 'Price must be a number.',

                'discount_percentage.max'     => 'Discount percentage cannot exceed 100%.',
                'discount_percentage.numeric' => 'Discount percentage must be a number.',
                'discount_amount.numeric'     => 'Discount amount must be a number.',

                'excise_percentage.max'       => 'Excise percentage cannot exceed 100%.',
                'excise_percentage.numeric'   => 'Excise percentage must be a number.',
                'excise_value.numeric'        => 'Excise amount must be a number.',
            ];

            $validation = Validator::make($request->all(), $rules, $messages);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post = $request->all();
            $post['orgid'] =  session('orgid');
            $post['userid'] =  session('userid');

            $duplicateQuery = RetailerPrice::where('itemid', $post['itemid'])
                ->where('variation_id', $post['variationid'])
                ->where('status', 'Y');

            if (!empty($post['id'])) {
                $duplicateQuery->where('id', '!=', $post['id']);
            }

            if ($duplicateQuery->exists()) {
                throw new Exception('This item variation already exists.', 1);
            }

            $type = 'success';
            $message = 'Records saved successfully';
            DB::beginTransaction();

            if (!RetailerPrice::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }
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


    //function to list team category
    public function list(Request $request)
    {
        // try {
        $post = $request->all();
        $post['orgid'] = session('orgid');

        $data = RetailerPrice::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $price              = (float) $row->price;
            $discountType       = $row->discount_type ?? null;
            $discountPercentage = (float) ($row->discount ?? 0);
            $discountAmount     = (float) ($row->discount_amount ?? 0);

            if ($discountType === 'fixed') {
                $discountLabel = $discountAmount > 0 ? 'Rs ' . number_format($discountAmount, 2) : '—';
                $afterDiscount = max($price - $discountAmount, 0);
            } elseif ($discountType === 'percentage') {
                $discountLabel = $discountPercentage > 0 ? $discountPercentage . '%' : '—';
                $afterDiscount = $price - ($price * $discountPercentage / 100);
            } else {
                $discountLabel = '—';
                $afterDiscount = $price;
            }

            $vatStatus  = ($row->vat_status ?? 'N') === 'Y';
            $vatPercent = (float) ($row->vat_percent ?? config('vat.default'));
            $vatLabel   = $vatStatus ? 'Taxable (' . rtrim(rtrim(number_format($vatPercent, 2), '0'), '.') . '%)' : 'Non-Taxable';

            $exciseStatus     = ($row->excise_status ?? 'N') === 'Y';
            $excisePercentage = (float) ($row->excise_percentage ?? 0);
            $exciseValue      = (float) ($row->excise_value ?? 0);

            if ($exciseStatus && $row->excise_type === 'percentage') {
                $exciseLabel  = $excisePercentage . '%';
                $exciseAmount = $afterDiscount * $excisePercentage / 100;
            } elseif ($exciseStatus && $row->excise_type === 'fixed') {
                $exciseLabel  = 'Rs ' . number_format($exciseValue, 2);
                $exciseAmount = $exciseValue;
            } else {
                $exciseLabel  = '—';
                $exciseAmount = 0;
            }

            $beforeVat    = $afterDiscount + $exciseAmount;
            $vatAmount    = $vatStatus ? $beforeVat * $vatPercent / 100 : 0;
            $sellingPrice = $beforeVat + $vatAmount;

            $array[$i]["sno"]           = $i + 1;
            $array[$i]["title"]         = $row->title;
            $array[$i]["value"]         = $row->value;
            $array[$i]["price"]         = number_format($price, 2);
            $array[$i]["discount"]      = $discountLabel;
            $array[$i]["vat_status"]    = $vatLabel;
            $array[$i]["excise_duty"]   = $exciseLabel;
            $array[$i]["selling_price"] = number_format($sellingPrice, 2);

            $action = '';
            $action .= '<a href="javascript:;"
                    class="editRetailer"
                    data-id="' . $row->id . '"
                    data-itemid="' . $row->itemid . '"
                    data-price="' . $row->price . '"
                    data-discounttype="' . ($row->discount_type ?? '') . '"
                    data-discountpercentage="' . $row->discount . '"
                    data-discountamount="' . $row->discount_amount . '"
                    data-variationid="' . $row->variationid . '">
                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                </a>';
            $action .= '| <a href="javascript:;" class="deleteRetailer" name="Delete Data" data-id="' . $row->id . '"><i class="fa fa-trash text-danger"></i></a>';

            $array[$i]["action"]  = $action;
            $i++;
        }
        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs) $totalrecs = 0;
        // } catch (QueryException $e) {
        //     $array = [];
        //     $totalrecs = 0;
        //     $filtereddata = 0;
        // } catch (Exception $e) {
        //     $array = [];
        //     $totalrecs = 0;
        //     $filtereddata = 0;
        // }
        return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    }


    //function to delete team category
    public function delete(Request $request)
    {
        // try {
        $type = 'success';
        $message = "Record deleted successfully";

        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['userid'] = session('userid');
        DB::beginTransaction();
        $result = RetailerPrice::deleteRetailerPrice($post);
        DB::commit();
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $this->queryMessage;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $e->getMessage();
        // }
        return json_encode(['type' => $type, 'message' => $message]);
    }
}