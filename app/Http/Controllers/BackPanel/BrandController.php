<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Brand;
use App\Models\BackPanel\TeamCategory;
use App\Models\BackPanel\Category;
use App\Models\Common;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    // construct
    public function __construct()
    {
        parent::__construct();
    }

    //function to redirect to team category page
    public function index()
    {

        return view('backend.brand.index');
    }

    //function to save team category 
    public function save(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|min:3|max:255',
            ];

            if (empty($request->id)) {
                // ✅ Fixed: use pipes | not colons :
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:2048';
            }

            $message = [
                'name.required' => 'Please enter brand name.',
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

            if (!Brand::saveData($post)) {
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


    //function to list team category
    public function list(Request $request)
    {
        // try {
        $post = $request->all();
        $post['orgid'] =  session('orgid');

        $data = Brand::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["name"]    = $row->name;
            if (!empty($row->logo)) {
                $imagePath = public_path('uploads/brands/' . $row->logo);

                if (file_exists($imagePath)) {
                    $imageUrl = asset('uploads/brands/' . $row->logo);
                } else {
                    $imageUrl = asset('no-image.jpg');
                }
            } else {
                $imageUrl = asset('no-image.jpg');
            }
            $array[$i]["image"] = '<img src="' . $imageUrl . '" height="30px" width="30px" alt="image"/>';
            $action = '';

            $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteBrand px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';
            // for show

            $action .= '<a href="javascript:;" 
            title="Edit Data" class="tooltipdiv 
            editBrand" style="color:blue;" 
              data-id="' . $row->id . '" 
                                data-name="' . $row->name . '" 
                                data-description="' . $row->description . '" 
                                data-image="' . $row->logo . '">
            <i class="bx bx-edit-alt"></i></a>';


            $array[$i]["action"]  = $action;
            $i++;
        }
        // dd($data);
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
        try {
            $type = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();

            DB::beginTransaction();
            $result = Brand::deletBrand($post);
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