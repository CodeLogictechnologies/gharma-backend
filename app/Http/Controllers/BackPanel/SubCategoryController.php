<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\Post;
use App\Models\BackPanel\SubCategory;
use App\Models\Common;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SubCategoryController extends Controller
{
    // construct
    public function __construct()
    {
        parent::__construct();
    }

    //function to redirect to team category page
    public function index()
    {
        $categories = Category::getCategory();
        $data = [
            'categories' => $categories
        ];
        return view('backend.subcategory.index', $data);
    }

    //function to save team category 
    public function save(Request $request)
    {
        // try {

        $rules = [
            'title' => 'required|min:3|max:255',
            // 'category' => 'required',
        ];
        if (empty($request->id)) {
            $rules['image'] = 'required:mimes:jpg,jpeg,png:max:2048';
        }

        $message = [
            'title.required' => 'Please enter sub category title.',
            // 'category.required' => 'Please select category.',
        ];

        $validation = Validator::make($request->all(), $rules, $message);

        if ($validation->fails()) {
            throw new Exception($validation->errors()->first(), 1);
        }

        $post = $request->all();
        $post['orgid'] =  session('orgid');

        $type = 'success';
        $message = 'Records saved successfully';
        DB::beginTransaction();

        if (!SubCategory::saveData($post)) {
            throw new Exception('Could not save record', 1);
        }
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


    //function to list team category
    public function list(Request $request)
    {
        // try {
        $post = $request->all();
        $data = SubCategory::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["title"]    = $row->title;
            $array[$i]["category_name"]    = $row->category_name;
            if (!empty($row->image)) {
                $imagePath = public_path('uploads/subcategories/' . $row->image);

                if (file_exists($imagePath)) {
                    $imageUrl = asset('uploads/subcategories/' . $row->image);
                } else {
                    $imageUrl = asset('no-image.jpg');
                }
            } else {
                $imageUrl = asset('no-image.jpg');
            }
            $array[$i]["image"] = '<img src="' . $imageUrl . '" height="30px" width="30px" alt="image"/>';
            $action = '';
            $action .= '<a href="javascript:;" 
                                class="editSubCategory" 
                                data-id="' . $row->id . '" 
                                data-title="' . $row->title . '" 
                                data-image="' . $row->image . '"
                                data-category="' . $row->category_id . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';
            $action .= '| <a href="javascript:;" class="deleteSubCategory" name="Delete Data" data-id="' . $row->id . '"><i class="fa fa-trash text-danger"></i></a>';

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
        try {
            $type = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();

            DB::beginTransaction();
            $result = SubCategory::deleteCategory($post);
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

    //restore
    public function restore(Request $request)
    {
        try {
            $post = $request->all();
            $type = 'success';
            $message = "Team Category restored successfully";
            DB::beginTransaction();
            $result = TeamCategory::restoreData($post);
            if (!$result) {
                throw new Exception("Could not restore Team Category. Please try again.", 1);
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
        return response()->json(['type' => $type, 'message' => $message]);
    }
}