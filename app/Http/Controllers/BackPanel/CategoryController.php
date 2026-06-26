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


// class CategoryController extends Controller
// {
//     // construct
//     public function __construct()
//     {
//         parent::__construct();
//     }

//     //function to redirect to team category page
//     public function index()
//     {

//         return view('backend.category.index');
//     }

//     //function to save team category 
//     public function save(Request $request)
//     {
//         // try {

//         $rules = [
//             'name' => 'required|min:3|max:255',
//         ];
//         if (empty($request->id)) {
//             $rules['image'] = 'required:mimes:jpg,jpeg,png:max:2048';
//         }

//         $message = [
//             'name.required' => 'Please enter category title.',
//         ];

//         $validation = Validator::make($request->all(), $rules, $message);

//         if ($validation->fails()) {
//             throw new Exception($validation->errors()->first(), 1);
//         }

//         $post = $request->all();
//         $post['orgid'] =  session('orgid');
//         $type = 'success';
//         $message = 'Records saved successfully';
//         DB::beginTransaction();

//         if (!Category::saveData($post)) {
//             throw new Exception('Could not save record', 1);
//         }
//         DB::commit();
//         // } catch (QueryException $e) {
//         //     DB::rollBack();
//         //     $type = 'error';
//         //     $message = $this->queryMessage;
//         // } catch (Exception $e) {
//         //     DB::rollBack();
//         //     $type = 'error';
//         //     $message = $e->getMessage();
//         // }
//         return json_encode(['type' => $type, 'message' => $message]);
//     }


//     //function to list team category
//     public function list(Request $request)
//     {
//         try {
//             $post = $request->all();
//             $data = Category::list($post);
//             $i = 0;
//             $array = [];
//             $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
//             $totalrecs = $data["totalrecs"];

//             unset($data["totalfilteredrecs"]);
//             unset($data["totalrecs"]);
//             foreach ($data as $row) {
//                 $array[$i]["sno"] = $i + 1;
//                 $array[$i]["title"]    = $row->title;
//                 if (!empty($row->image)) {
//                     $imagePath = storage_path('app/public/categories/' . $row->image);
//                     if (file_exists($imagePath)) {
//                         $imageUrl = asset('storage/categories/' . $row->image); // ✅ storage path
//                     } else {
//                         $imageUrl = asset('no-image.jpg');
//                     }
//                 } else {
//                     $imageUrl = asset('no-image.jpg');
//                 }
//                 $array[$i]["image"] = '<img src="' . $imageUrl . '" height="30px" width="30px" alt="image"/>';
//                 $action = '';
//                 $action .= '<a href="javascript:;" 
//                                 class="editCategory" 
//                                 data-id="' . $row->id . '" 
//                                 data-title="' . $row->title . '" 
//                                 data-image="' . $row->image . '">
//                                 <i class="fa-solid fa-pen-to-square text-primary"></i>
//                             </a>';
//                 $action .= '| <a href="javascript:;" class="deleteCategory" name="Delete Data" data-id="' . $row->id . '"><i class="fa fa-trash text-danger"></i></a>';

//                 $array[$i]["action"]  = $action;
//                 $i++;
//             }
//             // dd($data);
//             if (!$filtereddata) $filtereddata = 0;
//             if (!$totalrecs) $totalrecs = 0;
//         } catch (QueryException $e) {
//             $array = [];
//             $totalrecs = 0;
//             $filtereddata = 0;
//         } catch (Exception $e) {
//             $array = [];
//             $totalrecs = 0;
//             $filtereddata = 0;
//         }
//         return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
//     }


//     //function to delete team category
//     public function delete(Request $request)
//     {
//         try {
//         $type = 'success';
//         $message = "Record deleted successfully";

//         $post = $request->all();

//         DB::beginTransaction();
//         $result = Category::deleteCategory($post);
//         DB::commit();
//         } catch (QueryException $e) {
//             DB::rollBack();
//             $type = 'error';
//             $message = $this->queryMessage;
//         } catch (Exception $e) {
//             DB::rollBack();
//             $type = 'error';
//             $message = $e->getMessage();
//         }
//         return json_encode(['type' => $type, 'message' => $message]);
//     }

//     // CategoryController.php
//     public function tabs(Request $request)
//     {
//         $tabid = $request->input('tabid');

//         switch ($tabid) {
//             case 'category':
//                 return view('backend.category.category');
//             case 'subcategory':
//                 return view('backend.category.subcategory');
//             default:
//                 return '<div class="alert alert-warning">Invalid tab</div>';
//         }
//     }
// }


class CategoryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
 
    // ─────────────────────────────────────────────────────────────────
    // Index — single page, no tabs
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        // Load only top-level (no parent) active categories for the dropdown
        $parentCategories = Category::getParentOptions(session('orgid'));
        return view('backend.categories.index', compact('parentCategories'));
    }
 
    // ─────────────────────────────────────────────────────────────────
    // Save (insert or update)
    // ─────────────────────────────────────────────────────────────────
    public function save(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|min:2|max:255',
            ];
            if (empty($request->id)) {
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:2048';
            }
 
            $validation = Validator::make($request->all(), $rules, [
                'name.required' => 'Please enter category name.',
                'image.required' => 'Please select an image.',
            ]);
 
            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }
 
            $post              = $request->all();
            $post['image']     = $request->file('image');
            $post['orgid']     = session('orgid');
            $post['parent_id'] = $request->input('parent_id') ?: null;
 
            $type    = 'success';
            $message = 'Category saved successfully.';
 
            DB::beginTransaction();
            if (!Category::saveData($post)) {
                throw new Exception('Could not save record.', 1);
            }
            DB::commit();
 
            // After save, return updated parent dropdown HTML so the
            // frontend can refresh the dropdown without reloading the page
            $parentCategories = Category::getParentOptions(session('orgid'));
            $options = '<option value="">-- None (Top Level) --</option>';
            foreach ($parentCategories as $cat) {
                $options .= '<option value="' . $cat->id . '">' . htmlspecialchars($cat->title) . '</option>';
            }
 
            return json_encode([
                'type'            => $type,
                'message'         => $message,
                'parentOptions'   => $options,
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            return json_encode(['type' => 'error', 'message' => $this->queryMessage]);
        } catch (Exception $e) {
            DB::rollBack();
            return json_encode(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
 
    // ─────────────────────────────────────────────────────────────────
    // DataTable list
    // ─────────────────────────────────────────────────────────────────
    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $data = Category::list($post);
 
            $filtereddata = $data['totalfilteredrecs'] > 0 ? $data['totalfilteredrecs'] : $data['totalrecs'];
            $totalrecs    = $data['totalrecs'];
 
            unset($data['totalfilteredrecs'], $data['totalrecs']);
 
            $array = [];
            $i     = 0;
 
            foreach ($data as $row) {
                $array[$i]['sno'] = $i + 1;
 
                // Name cell
                $array[$i]['title'] = '<span style="font-weight:600;color:#2d2d2d;">'
                    . htmlspecialchars($row->title) . '</span>';
 
                // Image cell
                $imagePath = storage_path('app/public/categories/' . $row->image);
                $imageUrl  = (!empty($row->image) && file_exists($imagePath))
                    ? asset('storage/categories/' . $row->image)
                    : asset('no-image.jpg');
 
                $array[$i]['image'] = '<img src="' . $imageUrl . '" height="38" width="38"
                    style="border-radius:7px;object-fit:cover;border:1.5px solid #e0e0ef;" alt="img">';
 
                // Parent category cell
                $array[$i]['parent_name'] = $row->parent_name
                    ? '<span class="badge" style="background:#f0f0ff;color:#696cff;font-size:12px;font-weight:500;padding:4px 10px;border-radius:20px;">'
                        . htmlspecialchars($row->parent_name) . '</span>'
                    : '<span style="color:#bbb;font-size:12px;">—</span>';
 
                // Actions
                $array[$i]['action'] =
                    '<a href="javascript:;" class="editCategory me-2"
                        data-id="'          . $row->id                          . '"
                        data-title="'       . htmlspecialchars($row->title)     . '"
                        data-image="'       . $row->image                       . '"
                        data-parent_id="'   . ($row->parent_id ?? '')           . '"
                        title="Edit">
                        <i class="fa-solid fa-pen-to-square" style="color:#696cff;font-size:15px;"></i>
                    </a>
                    <a href="javascript:;" class="deleteCategory"
                        data-id="' . $row->id . '"
                        title="Delete">
                        <i class="fa-solid fa-trash" style="color:#ff4d4f;font-size:15px;"></i>
                    </a>';
 
                $i++;
            }
        } catch (QueryException | Exception $e) {
            $array        = [];
            $totalrecs    = 0;
            $filtereddata = 0;
        }
 
        return json_encode([
            'recordsFiltered' => (int) $filtereddata,
            'recordsTotal'    => (int) $totalrecs,
            'data'            => $array,
        ]);
    }
 
    // ─────────────────────────────────────────────────────────────────
    // Delete (soft)
    // ─────────────────────────────────────────────────────────────────
    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Category deleted successfully.';
 
            DB::beginTransaction();
            Category::deleteCategory($request->all());
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }
 
        return json_encode(['type' => $type, 'message' => $message]);
    }
 
    // ─────────────────────────────────────────────────────────────────
    // AJAX: get fresh parent dropdown options (called after save)
    // ─────────────────────────────────────────────────────────────────
    public function getParentOptions(Request $request)
    {
        $excludeId = $request->input('exclude');
        $cats      = Category::getParentOptions(session('orgid'), $excludeId);
 
        $options = '<option value="">-- None (Top Level) --</option>';
        foreach ($cats as $cat) {
            $options .= '<option value="' . $cat->id . '">' . htmlspecialchars($cat->title) . '</option>';
        }
 
        return response()->json(['options' => $options]);
    }
}