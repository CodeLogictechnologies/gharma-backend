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
    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────────────────────────
    // Index — single page, no tabs. Only top-level categories are
    // rendered into the "Category" dropdown on page load; the
    // "Sub Category" dropdown is populated client-side via AJAX.
    // ─────────────────────────────────────────────────────────────────
    public function index()
    {
        $categories = Category::getTopLevelCategories(session('orgid'));
        return view('backend.categories.index', compact('categories'));
    }

    // ─────────────────────────────────────────────────────────────────
    // Save (insert or update)
    // The form now submits two separate fields: category_id and
    // subcategory_id. Whichever is the "deepest" selection becomes
    // the actual parent_id:
    //   - neither selected  -> parent_id = null      (top-level Category)
    //   - category only     -> parent_id = category   (Subcategory)
    //   - category + sub    -> parent_id = subcategory (Sub-subcategory)
    // ─────────────────────────────────────────────────────────────────
    // public function save(Request $request)
    // {
    //     try {
    //         $rules = [
    //             'name' => 'required|min:2|max:255',
    //         ];
    //         if (empty($request->id)) {
    //             $rules['image'] = 'required|mimes:jpg,jpeg,png|max:2048';
    //         }

    //         $validation = Validator::make($request->all(), $rules, [
    //             'name.required'  => 'Please enter category name.',
    //             'image.required' => 'Please select an image.',
    //         ]);

    //         if ($validation->fails()) {
    //             throw new Exception($validation->errors()->first(), 1);
    //         }

    //         $categoryId    = $request->input('category_id') ?: null;
    //         $subcategoryId = $request->input('subcategory_id') ?: null;

    //         $post              = $request->all();
    //         $post['image']     = $request->file('image');
    //         $post['orgid']     = session('orgid');
    //         $post['parent_id'] = $subcategoryId ?: $categoryId;

    //         $type    = 'success';
    //         $message = 'Category saved successfully.';

    //         DB::beginTransaction();
    //         if (!Category::saveData($post)) {
    //             throw new Exception('Could not save record.', 1);
    //         }
    //         DB::commit();

    //         // Refresh the top-level Category dropdown options (a brand new
    //         // top-level category may have just been created)
    //         $categories = Category::getTopLevelCategories(session('orgid'));
    //         $options = '<option value="">-- None (Top Level) --</option>';
    //         foreach ($categories as $cat) {
    //             $options .= '<option value="' . $cat->id . '">' . htmlspecialchars($cat->title) . '</option>';
    //         }

    //         return json_encode([
    //             'type'            => $type,
    //             'message'         => $message,
    //             'categoryOptions' => $options,
    //         ]);
    //     } catch (QueryException $e) {
    //         DB::rollBack();
    //         return json_encode(['type' => 'error', 'message' => $this->queryMessage]);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return json_encode(['type' => 'error', 'message' => $e->getMessage()]);
    //     }
    // }
    public function save(Request $request)
    {
        if (!auth()->user()->can($request->id ? 'edit.category' : 'add.category')) {
            return json_encode(['type' => 'error', 'message' => 'Unauthorized.']);
        }
        try {
            $isQuickAdd = $request->boolean('quick_add');

            $rules = [
                'name' => 'required|min:2|max:255',
            ];
            if (empty($request->id) && !$isQuickAdd) {
                $rules['image'] = 'required|mimes:jpg,jpeg,png|max:2048';
            }

            $validation = Validator::make($request->all(), $rules, [
                'name.required'  => 'Please enter category name.',
                'image.required' => 'Please select an image.',
            ]);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $categoryId    = $request->input('category_id') ?: null;
            $subcategoryId = $request->input('subcategory_id') ?: null;

            $post              = $request->all();
            $post['image']     = $request->file('image');
            $post['orgid']     = session('orgid');
            $post['parent_id'] = $subcategoryId ?: $categoryId;

            $type    = 'success';
            $message = !empty($request->id) ? 'Category updated successfully.' : 'Category saved successfully.';


            DB::beginTransaction();
            $categoryId2 = Category::saveData($post);
            if (!$categoryId2) {
                throw new Exception('Could not save record.', 1);
            }
            DB::commit();

            $categories = Category::getTopLevelCategories(session('orgid'));
            $options = '<option value="">-- None (Top Level) --</option>';
            foreach ($categories as $cat) {
                $options .= '<option value="' . $cat->id . '">' . htmlspecialchars($cat->title) . '</option>';
            }

            $created = DB::table('categories')->where('id', $categoryId2)->select('id', 'title')->first();

            return json_encode([
                'type'            => $type,
                'message'         => $message,
                'categoryOptions' => $options,
                'category'        => $created,
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
            $post['orgid'] = session('orgid');
            $data = Category::list($post);

            $filtereddata = $data['totalfilteredrecs'] > 0 ? $data['totalfilteredrecs'] : $data['totalrecs'];
            $totalrecs    = $data['totalrecs'];

            unset($data['totalfilteredrecs'], $data['totalrecs']);

            $array = [];
            $i     = 0;

            $typeBadges = [
                0 => ['label' => 'Category',       'bg' => '#eef2ff', 'color' => '#4338ca'],
                1 => ['label' => 'Subcategory',     'bg' => '#f0f0ff', 'color' => '#696cff'],
                2 => ['label' => 'Sub-subcategory', 'bg' => '#fff1f0', 'color' => '#ff4d4f'],
            ];

            foreach ($data as $row) {
                $array[$i]['sno'] = $i + 1;

                $array[$i]['title'] = '<span style="font-weight:600;color:#2d2d2d;">'
                    . htmlspecialchars($row->title) . '</span>';

                $level = (int) $row->level;
                $badge = $typeBadges[$level] ?? $typeBadges[0];
                $array[$i]['type'] = '<span class="badge" style="background:' . $badge['bg'] . ';color:' . $badge['color'] . ';font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;">'
                    . $badge['label'] . '</span>';

                $imagePath = storage_path('app/public/categories/' . $row->image);
                $imageUrl  = (!empty($row->image) && file_exists($imagePath))
                    ? asset('storage/categories/' . $row->image)
                    : asset('no-image.jpg');

                $array[$i]['image'] = '<img src="' . $imageUrl . '" height="38" width="38"
                style="border-radius:7px;object-fit:cover;border:1.5px solid #e0e0ef;" alt="img">';

                $array[$i]['parent_name'] = $row->parent_name
                    ? '<span class="badge" style="background:#f0f0ff;color:#696cff;font-size:12px;font-weight:500;padding:4px 10px;border-radius:20px;">'
                    . htmlspecialchars($row->parent_name) . '</span>'
                    : '<span style="color:#bbb;font-size:12px;">—</span>';

                $action = '';

                if (auth()->user()->can('edit.category')) {
                    $action .= '<a href="javascript:;" class="editCategory me-2"
                        data-id="'              . $row->id                              . '"
                        data-title="'           . htmlspecialchars($row->title)         . '"
                        data-image="'           . $row->image                           . '"
                        data-parent_id="'       . ($row->parent_id ?? '')               . '"
                        data-category_id="'     . ($row->top_category_id ?? '')         . '"
                        data-subcategory_id="'  . ($row->sub_category_id ?? '')         . '"
                        title="Edit">
                        <i class="fa-solid fa-pen-to-square" style="color:#696cff;font-size:15px;"></i>
                    </a>';
                }

                if (auth()->user()->can('delete.category')) {
                    $action .= '<a href="javascript:;" class="deleteCategory"
                        data-id="' . $row->id . '"
                        title="Delete">
                        <i class="fa-solid fa-trash" style="color:#ff4d4f;font-size:15px;"></i>
                    </a>';
                }

                $array[$i]['action'] = $action;

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
        if (!auth()->user()->can('delete.category')) {
            return json_encode(['type' => 'error', 'message' => 'Unauthorized.']);
        }
        try {
            $type    = 'success';
            $message = 'Category deleted successfully.';

            $post = $request->all();
            $post['orgid'] = session('orgid');
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
    // NEW: AJAX — top-level Category options (used to refresh/reload
    // the first dropdown on edit, excluding the item being edited)
    // ─────────────────────────────────────────────────────────────────
    public function topLevel(Request $request)
    {
        $excludeId  = $request->input('exclude');
        $categories = Category::getTopLevelCategories(session('orgid'), $excludeId);

        $options = '<option value="">-- None (Top Level) --</option>';
        foreach ($categories as $cat) {
            $options .= '<option value="' . $cat->id . '">' . htmlspecialchars($cat->title) . '</option>';
        }

        return response()->json(['options' => $options]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NEW: AJAX — Sub Category options for a given Category (used
    // whenever the "Category" dropdown changes, and to prefill on edit)
    // ─────────────────────────────────────────────────────────────────
    public function getSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');
        $excludeId  = $request->input('exclude');

        $subs = Category::getChildrenOf(session('orgid'), $categoryId, $excludeId);

        $options = '<option value="">-- None (direct under Category) --</option>';
        foreach ($subs as $sub) {
            $options .= '<option value="' . $sub->id . '">' . htmlspecialchars($sub->title) . '</option>';
        }

        return response()->json(['options' => $options]);
    }

    // Kept for backward compatibility (no longer used by the new cascading UI)
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
