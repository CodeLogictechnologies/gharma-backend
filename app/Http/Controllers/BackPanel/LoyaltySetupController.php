<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\LoyaltySetup;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class LoyaltySetupController extends Controller
{

    //function to redirect page
    public function index()
    {

        return view('backend.loyaltySetup.index');
    }

    //function to save 
    public function save(Request $request)
    {
        try {
            $rules = [
                'minprice'   => 'required|integer|min:1',
                'maxprice'   => 'required|integer|gte:minprice',
                'percentage' => 'required|integer|min:0|max:100',
            ];

            $message = [
                'minprice.required' => 'Min price is required',
                'maxprice.required' => 'Max price is required',
                'percentage.required' => 'Percentage is required',
                'maxprice.gte' => 'Max price must be greater than or equal to min price',
            ];

            $validation = Validator::make($request->all(), $rules, $message);

            if ($validation->fails()) {
                return json_encode(['type' => 'error', 'message' => $validation->errors()->first()]);
            }

            $post = $request->all();
            $post['orgid'] =  session('orgid');
            $post['userid'] =  session('userid');

            $type = 'success';
            $message = 'Records saved successfully';

            DB::beginTransaction();

            if (!LoyaltySetup::saveData($post)) {
                throw new Exception('Could not save record');
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }


    //function to get list 
    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] =  session('orgid');

            $data = LoyaltySetup::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);
            foreach ($data as $row) {
                $array[$i]["sno"] = $i + 1;
                $array[$i]["minprice"]    = $row->minprice;
                $array[$i]["maxprice"]    = $row->maxprice;
                $array[$i]["percentage"]    = $row->percentage;
                $action = '';
                $action .= '<a href="javascript:void(0)" 
                            title="Edit Data" 
                            class="tooltipdiv editLoyalty" 
                            style="color:blue;" 
                            data-id="' . $row->id . '" 
                            data-minprice="' . $row->minprice . '" 
                            data-maxprice="' . $row->maxprice . '" 
                            data-percentage="' . $row->percentage . '">
                            <i class="bx bx-edit-alt"></i>
                        </a>|';
                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteLoyalty px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';


                $array[$i]["action"]  = $action;
                $i++;
            }
            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs) $totalrecs = 0;
        } catch (QueryException $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        }
        return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    }


    //function to delete 
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();

            DB::beginTransaction();
            $result = LoyaltySetup::deleteLoyalty($post);
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
