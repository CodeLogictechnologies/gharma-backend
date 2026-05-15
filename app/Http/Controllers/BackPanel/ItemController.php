<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Brand;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\ItemImage;
use App\Models\BackPanel\Itemvariation;
use App\Models\BackPanel\SubCategory;
use App\Models\Item\BackPanel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\QueryException;
use Exception;

class ItemController extends Controller
{
    /**
     * Load the create / edit modal form.
     */

    public function index()
    {
        $post['orgid'] = session('orgid');
        $categories = Category::getCategory($post);
        $subCategories = SubCategory::getSubCategory($post);
        $data = [
            'categories' => $categories,
            'subCategories' => $subCategories,
        ];
        return view('backend.item.index', $data);
    }



    // ── In your ItemController ────────────────────────────────────────────────────


    // ── form() method in your ItemController ─────────────────────────────────────

    public function form(Request $request)
    {
        $id   = $request->id ?? null;
        $post = $request->all();
        $post['orgid'] = session('orgid');

        $categories    = Category::getCategory($post);
        $subCategories = SubCategory::getSubCategory($post);
        $brands        = Brand::getBrand($post);

        // ── Default empty data (Add mode) ────────────────────────────────────────
        $data = [
            'id'             => null,
            'title'          => '',
            'brand'          => '',
            'type'           => 'Regular',
            'categories'     => [],
            'sub_categories' => [],
            'description'    => '',
            'images'         => [],
            'variations'     => [
                [
                    'variationid' => '',
                    'name'        => 'Size',
                    'value'       => '',
                    'threshold'   => '',
                    'price'       => '',
                    'stock'       => '',
                    'status'      => 'active',
                ],
            ],
        ];

        // ── Edit mode: hydrate from DB ────────────────────────────────────────────
        if (!empty($id)) {
            $item = Item::findOrFail($id);

            // ── FIX: pivot column is 'categoryid' (no underscore), not 'category_id'
            $categoryIds = DB::table('category_items')
                ->where('itemid', $id)
                ->pluck('categoryid')               // ← correct column name from your insert code
                ->map(fn($v) => (string) $v)
                ->toArray();

            // ── FIX: pivot column is 'subcategoryid' (no underscore), not 'sub_category_id'
            $subCategoryIds = DB::table('sub_category_items')
                ->where('itemid', $id)
                ->pluck('subcategoryid')            // ← correct column name from your insert code
                ->map(fn($v) => (string) $v)
                ->toArray();

            // ── Fetch existing images ordered by order_number then id
            $existingImages = DB::table('item_images')
                ->where('item_id', $id)
                // ->orderBy('order_number')
                ->orderBy('id')
                ->get()
                ->map(fn($img) => [
                    'id'           => $img->id,
                    'filename'     => $img->image,
                    'order_number' => $img->order_number ?? 0,
                ])
                ->toArray();

            // ── FIX: DB stores status as 'Y'/'N' — map to 'active'/'inactive' for blade
            $existingVariations = DB::table('itemvariations')
                ->where('item_id', $id)
                ->get()
                ->map(fn($v) => [
                    'variationid' => $v->id,
                    'name'        => $v->attribute,
                    'value'       => $v->value,
                    'threshold'   => $v->threshold,
                    'price'       => $v->price,
                    'stock'       => $v->stock,
                    'status'      => ($v->status === 'Y') ? 'active' : 'inactive', // ← map Y/N → active/inactive
                ])
                ->toArray();

            $data = [
                'id'             => $item->id,
                'title'          => $item->title,
                'brand'          => $item->brand_id,
                'type'           => $item->type,
                'categories'     => $categoryIds,      // string IDs from category_items.categoryid
                'sub_categories' => $subCategoryIds,   // string IDs from sub_category_items.subcategoryid
                'description'    => $item->description,
                'images'         => $existingImages,
                'variations'     => !empty($existingVariations)
                    ? $existingVariations
                    : $data['variations'],
            ];
        }

        return view('backend.item.addItem', [
            'data'          => $data,
            'id'            => $id,
            'categories'    => $categories,
            'subCategories' => $subCategories,
            'brands'        => $brands,
        ]);
    }

    /**
     * Save (create or update) an item.
     */
    public function save(Request $request)
    {      // try {
        $type = 'success';
        $rules = [
            'title'          => 'required|string|max:255',
            'brand'          => 'required|string|max:255',
            'type'           => 'required|in:Regular,Special,Featured',
            'description'    => 'nullable|string',
            'categories'     => 'required|exists:categories,id',
            'sub_categories' => 'required|exists:sub_categories,id',
            'status'         => 'nullable|in:Y,N',
            // 'images.*'       => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'variations'     => 'nullable|array',
        ];

        if (empty($request->id)) {
            $rules['images'] = 'required|array|min:1';
        }
        $message = [
            'name.required' => 'Please enter category title.',
        ];

        $validation = Validator::make($request->all(), $rules, $message);

        if ($validation->fails()) {
            throw new Exception($validation->errors()->first(), 1);
        }


        $request->validate($rules);
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $post['userid'] = session('userid');
        $message = 'Records saved successfully';
        DB::beginTransaction();

        if (!Item::saveData($post)) {
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


    /**
     * Delete an item and its stored images.
     */
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();

            DB::beginTransaction();
            $result = Item::deleteItem($post);
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


    // ── Helpers ──────────────────────────────────────────────────────────

    private function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug  = Str::slug($title);
        $query = Item::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists() ? $slug . '-' . time() : $slug;
    }


    public function list(Request $request)
    {
        $post = $request->all();
        $data = Item::list($post);
        $i            = 0;
        $array        = [];
        $totalrecs    = $data['totalrecs']        ?? 0;
        $filtereddata = $data['totalfilteredrecs'] ?? $totalrecs;

        unset($data['totalrecs'], $data['totalfilteredrecs']);

        foreach ($data as $row) {

            // Serial number with pagination offset
            $array[$i]['sno'] = $request->input('start', 0) + $i + 1;

            // Item columns (matching your items migration)
            $array[$i]['name']         = $row->title           ?? '—';
            $array[$i]['category'] = $row->categories ?? [];

            // ✅ MULTIPLE subcategories
            $array[$i]['subcategory'] = $row->subcategories ?? [];
            $array[$i]['description'] = $row->description ?? '—';
            $array[$i]['type'] = $row->type ?? '—';
            $array[$i]['brand'] = $row->brand ?? '—';
            $action = '';

            $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteItem px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';
            // for show
            $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewItem" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';

            $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editItem" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';
            $array[$i]["action"]  = $action;

            $i++;
        }

        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs)    $totalrecs    = 0;

        return response()->json([
            'recordsTotal'    => (int) $totalrecs,
            'recordsFiltered' => (int) $filtereddata,
            'data'            => $array,
        ]);
    }

public function view(Request $request)
{
    try {
        $post = $request->all();
        $itemDetails = Item::getData($post);
        $data = ['itemDetails' => $itemDetails];
        $data['type'] = 'success';
        $data['message'] = 'Successfully fetched data.';
    } catch (QueryException $e) {
        $data['type'] = 'error';
        $data['message'] = $this->queryMessage;
    } catch (Exception $e) {
        $data['type'] = 'error';
        $data['message'] = $e->getMessage();
    }
    return view('backend.item.view', $data);
}
    // public function view(Request $request)
    // {
    //     // try {
    //     $post = $request->all();

    //     $itemDetails = Item::getData($post);

    //     $data = [
    //         'itemDetails' => $itemDetails,
    //     ];

    //     $data['type'] = 'success';
    //     $data['message'] = 'Successfully fetched data of New and Blogs.';
    //     // } catch (QueryException $e) {
    //     //     $data['type'] = 'error';
    //     //     $data['message'] = $this->queryMessage;
    //     // } catch (Exception $e) {
    //     //     $data['type'] = 'error';
    //     //     $data['message'] = $e->getMessage();
    //     // }
    //     return view('backend.item.view', $data);
    // }
}