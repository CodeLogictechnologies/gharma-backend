<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SessionController;
use App\Models\BackPanel\TeamCategory;
use App\Models\BackPanel\Category;
use App\Models\Common;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class CategoryController extends Controller
{
    // construct
    public function __construct()
    {
        parent::__construct();
    }

    //function to redirect to team category page
    public function index()
    {

        return view('backend.category.index');
    }

    //function to save team category 
    public function save(Request $request)
    {
        // try {

        $rules = [
            'name' => 'required|min:3|max:255',
        ];
        if (empty($request->id)) {
            $rules['image'] = 'required:mimes:jpg,jpeg,png:max:2048';
        }

        $message = [
            'name.required' => 'Please enter category title.',
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

        if (!Category::saveData($post)) {
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
        try {
            $post = $request->all();
            $data = Category::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);
            foreach ($data as $row) {
                $array[$i]["sno"] = $i + 1;
                $array[$i]["title"]    = $row->title;
                if (!empty($row->image)) {
                    $imagePath = public_path('uploads/categories/' . $row->image);

                    if (file_exists($imagePath)) {
                        $imageUrl = asset('uploads/categories/' . $row->image);
                    } else {
                        $imageUrl = asset('no-image.jpg');
                    }
                } else {
                    $imageUrl = asset('no-image.jpg');
                }
                $array[$i]["image"] = '<img src="' . $imageUrl . '" height="30px" width="30px" alt="image"/>';
                $action = '';
                $action .= '<a href="javascript:;" 
                                class="editCategory" 
                                data-id="' . $row->id . '" 
                                data-title="' . $row->title . '" 
                                data-image="' . $row->image . '">
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';
                $action .= '| <a href="javascript:;" class="deleteCategory" name="Delete Data" data-id="' . $row->id . '"><i class="fa fa-trash text-danger"></i></a>';

                $array[$i]["action"]  = $action;
                $i++;
            }
            // dd($data);
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


    //function to delete team category
    public function delete(Request $request)
    {
        // try {
        $type = 'success';
        $message = "Record deleted successfully";

        $post = $request->all();

        DB::beginTransaction();
        $result = Category::deleteCategory($post);
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

    // CategoryController.php
    public function tabs(Request $request)
    {
        $tabid = $request->input('tabid');

        switch ($tabid) {
            case 'category':
                return view('backend.category.category');
            case 'subcategory':
                return view('backend.category.subcategory');
            default:
                return '<div class="alert alert-warning">Invalid tab</div>';
        }
    }
}