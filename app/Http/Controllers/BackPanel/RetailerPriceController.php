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
                'itemid'     => 'required|uuid',
                'variationid' => 'required|uuid',
                'price'      => 'required|numeric|min:0',
            ];

            $messages = [
                'itemid.required'      => 'Item is required.',
                'itemid.uuid'          => 'Invalid item ID format.',

                'variationid.required' => 'Variation is required.',
                'variationid.uuid'     => 'Invalid variation ID format.',

                'price.required'       => 'Price is required.',
                'price.numeric'        => 'Price must be a number.',
            ];

            $validation = Validator::make($request->all(), $rules, $messages);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post = $request->all();
            $post['orgid'] =  session('orgid');
            $post['userid'] =  session('userid');

            $exists = RetailerPrice::where('itemid', $post['itemid'])
                ->where('variation_id', $post['variationid'])
                ->exists();

            if ($exists) {
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
        $data = RetailerPrice::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["title"]    = $row->title;
            $array[$i]["value"]    = $row->value;
            $array[$i]["price"]    = $row->price;

            $action = '';
            $action .= '<a href="javascript:;" 
                    class="editRetailer" 
                    data-id="' . $row->id . '" 
                    data-itemid="' . $row->itemid . '" 
                    data-price="' . $row->price . '" 
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