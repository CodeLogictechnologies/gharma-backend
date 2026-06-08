<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Brand;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;

class ItemController extends Controller
{
    public function index()
    {
        $post['orgid'] = session('orgid');
        $categories    = Category::getCategory($post);
        $subCategories = SubCategory::getSubCategory($post);

        return view('backend.item.index', [
            'categories'    => $categories,
            'subCategories' => $subCategories,
        ]);
    }

    public function form(Request $request)
    {
        $id            = $request->id ?? null;
        $post          = $request->all();
        $post['orgid'] = session('orgid');

        $categories    = Category::getCategory($post);
        $subCategories = SubCategory::getSubCategory($post);
        $brands        = Brand::getBrand($post);

        // Default (Add mode)
        $data = [
            'id'                   => null,
            'title'                => '',
            'product_code'         => '',
            'company_product_code' => '',
            'brand'                => '',
            'type'                 => 'Regular',
            'categories'           => [],
            'sub_categories'       => [],
            'description'          => '',
            'images'               => [],
            'variations'           => [[
                'variationid' => '',
                'name'        => 'Size',
                'value'       => '',
                'threshold'   => '',
                'price'       => '',
                // 'stock'       => '',
                'status'      => 'active',
            ]],
        ];

        // Edit mode
        if (!empty($id)) {
            $item = Item::findOrFail($id);

            $categoryIds = DB::table('category_items')
                ->where('itemid', $id)
                ->pluck('categoryid')
                ->map(fn($v) => (string) $v)
                ->toArray();

            $subCategoryIds = DB::table('sub_category_items')
                ->where('itemid', $id)
                ->pluck('subcategoryid')
                ->map(fn($v) => (string) $v)
                ->toArray();

            $existingImages = DB::table('item_images')
                ->where('item_id', $id)
                ->orderByRaw('CASE WHEN order_number IS NULL OR order_number = 0 THEN 1 ELSE 0 END')
                ->orderBy('order_number')
                ->orderBy('created_at')
                ->get()
                ->map(fn($img) => [
                    'id'           => $img->id,
                    'filename'     => $img->image,
                    'order_number' => $img->order_number ?? 0,
                ])
                ->toArray();

            $existingVariations = DB::table('itemvariations')
                ->where('item_id', $id)
                ->get()
                ->map(fn($v) => [
                    'variationid' => $v->id,
                    'name'        => $v->attribute,
                    'value'       => $v->value,
                     'product_code'         => $v->product_code ?? '',         
    'company_product_code' => $v->company_product_code ?? '',
                    'threshold'   => $v->threshold,
                    'price'       => $v->price,
                    // 'stock'       => $v->stock,
                    'status'      => ($v->status === 'Y') ? 'active' : 'inactive',
                ])
                ->toArray();

            $data = [
                'id'                   => $item->id,
                'title'                => $item->title,
                'product_code'         => $item->product_code,
                'company_product_code' => $item->company_product_code,
                'brand'                => $item->brand_id,
                'type'                 => $item->type,
                'categories'           => $categoryIds,
                'sub_categories'       => $subCategoryIds,
                'description'          => $item->description,
                'images'               => $existingImages,
                'variations'           => !empty($existingVariations)
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

    public function save(Request $request)
    {
        // try {
            $type  = 'success';
            $rules = [
                'title'                => 'required|string|max:255',
                'product_code'         => 'required|string|max:255',
                'company_product_code' => 'required|string|max:255',
                'brand'                => 'required|string|max:255',
                'type'                 => 'required|in:Regular,Special,Featured',
                'description'          => 'nullable|string',
                'categories'           => 'required|array|min:1',
                'sub_categories'       => 'required|array|min:1',
                'status'               => 'nullable|in:Y,N',
                'variations'           => 'nullable|array',
            ];

            if (empty($request->id)) {
                $rules['images'] = 'required|array|min:1';
            }

            $validation = Validator::make($request->all(), $rules);

            if ($validation->fails()) {
                return json_encode([
                    'type'    => 'error',
                    'message' => $validation->errors()->first(),
                ]);
            }

            $post                  = $request->all();
            $post['orgid']         = session('orgid');
            $post['userid']        = session('userid');
            $post['images']        = $request->file('images') ?? [];

            $message = 'Records saved successfully';

            DB::beginTransaction();

            if (!Item::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }

            DB::commit();

        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type    = 'error';
        //     $message = $this->queryMessage;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type    = 'error';
        //     $message = $e->getMessage();
        // }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Record deleted successfully';

            DB::beginTransaction();
            Item::deleteItem($request->all());
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

    public function list(Request $request)
    {
        $post         = $request->all();
        $data         = Item::list($post);
        $i            = 0;
        $array        = [];
        $totalrecs    = $data['totalrecs']        ?? 0;
        $filtereddata = $data['totalfilteredrecs'] ?? $totalrecs;

        unset($data['totalrecs'], $data['totalfilteredrecs']);

        foreach ($data as $row) {
            $array[$i]['sno']         = $request->input('start', 0) + $i + 1;
            $array[$i]['name']        = $row->title        ?? '—';
            $array[$i]['category']    = $row->categories   ?? [];
            $array[$i]['subcategory'] = $row->subcategories ?? [];
            $array[$i]['description'] = $row->description  ?? '—';
            $array[$i]['type']        = $row->type         ?? '—';
            $array[$i]['brand']       = $row->brand        ?? '—';

            $action  = '<a href="javascript:;" title="Delete" class="tooltipdiv deleteItem px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
            $action .= '<a href="javascript:;" title="View" class="tooltipdiv viewItem" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
            $action .= '<a href="javascript:;" title="Edit" class="tooltipdiv editItem" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';

            $array[$i]['action'] = $action;
            $i++;
        }

        return response()->json([
            'recordsTotal'    => (int) ($totalrecs    ?: 0),
            'recordsFiltered' => (int) ($filtereddata ?: 0),
            'data'            => $array,
        ]);
    }

    public function view(Request $request)
    {
        try {
            $itemDetails   = Item::getData($request->all());
            $data          = ['itemDetails' => $itemDetails];
            $data['type']  = 'success';
            $data['message'] = 'Successfully fetched data.';
        } catch (QueryException $e) {
            $data = ['type' => 'error', 'message' => $this->queryMessage];
        } catch (Exception $e) {
            $data = ['type' => 'error', 'message' => $e->getMessage()];
        }

        return view('backend.item.view', $data);
    }
}