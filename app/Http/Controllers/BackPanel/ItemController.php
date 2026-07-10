<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Brand;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\ItemImage;
use App\Models\BackPanel\Itemvariation;
use App\Models\BackPanel\SubCategory;
use App\Models\BackPanel\VariationAttribute;
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
    public function index()
    {
        $post['orgid'] = session('orgid');
        $categories = Category::getCategory($post);
        $subCategories = SubCategory::getSubCategory($post);
        $data = [
            'categories'    => $categories,
            'subCategories' => $subCategories,
        ];
        return view('backend.item.index', $data);
    }

    public function form(Request $request)
    {
        $id   = $request->id ?? null;
        $post = $request->all();
        $post['orgid'] = session('orgid');

        $categories          = Category::getCategory($post);
        $subCategories       = SubCategory::getSubCategory($post);
        $brands              = Brand::getBrand($post);
        $variationAttributes = VariationAttribute::fetchVariationAttributes($post);

        $data = [
            'id'                   => null,
            'title'                => '',
            'brand'                => '',
            'type'                 => 'Regular',
            'product_code'         => '',
            'company_product_code' => '',
            'hs_code'              => '',
            'is_wholesale'         => false,
            'vat_status'           => false,
            'excise_status'        => false,
            'excise_type'          => '',
            'excise_percentage'    => '',
            'excise_value'         => '',
            'categories'           => [],
            'sub_categories'       => [],
            'description'          => '',
            'images'               => [],
            'variations' => [
                [
                    'variationid'          => '',
                    'attribute_id'         => '',
                    'name'                 => '',
                    'value'                => '',
                    'product_code'         => '',
                    'hs_code'              => '',
                    'company_product_code' => '',
                    'threshold'            => '',
                    'discount_type'        => '',
                    'discount_percentage'  => '',
                    'discount_amount'      => '',
                    'price'                => '',
                ],
            ],
        ];

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
                    'variationid'          => $v->id,
                    'attribute_id'         => $v->variation_attribute_id ?? '',
                    'name'                 => $v->attribute,
                    'value'                => $v->value,
                    'product_code'         => $v->product_code         ?? '',
                    'company_product_code' => $v->company_product_code ?? '',
                    'hs_code'              => $v->hs_code               ?? '',
                    'threshold'            => $v->threshold             ?? '',
                    'discount_type'        => $v->discount_type         ?? '',
                    'discount_percentage'  => $v->discount              ?? '',
                    'discount_amount'      => $v->discount_amount       ?? '',
                    'price'                => $v->price                 ?? '',
                ])
                ->toArray();

            $data = [
                'id'                   => $item->id,
                'title'                => $item->title,
                'brand'                => $item->brand_id,
                'type'                 => $item->type,
                'product_code'         => $item->product_code         ?? '',
                'company_product_code' => $item->company_product_code ?? '',
                'hs_code'              => $item->hs_code               ?? '',
                'is_wholesale'         => $item->is_wholesale === 'Y',
                'vat_status'           => $item->vat_status === 'Y',
                'excise_status'        => $item->excise_status === 'Y',
                'excise_type'          => $item->excise_type       ?? '',
                'excise_percentage'    => $item->excise_percentage ?? '',
                'excise_value'         => $item->excise_value      ?? '',
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
            'data'                => $data,
            'id'                  => $id,
            'categories'          => $categories,
            'subCategories'       => $subCategories,
            'brands'              => $brands,
            'variationAttributes' => $variationAttributes,
        ]);
    }

    public function save(Request $request)
    {
        try {
            $type = 'success';
            $rules = [
                'title'                => 'required|string|max:255',
                'brand'                => 'required|string|max:255',
                'type'                 => 'required|in:Regular,Special,Featured',
                'description'          => 'nullable|string',
                'categories'           => 'required|exists:categories,id',
                'sub_categories'       => 'required|exists:sub_categories,id',
                'product_code'         => 'nullable|string|max:255',
                'company_product_code' => 'nullable|string|max:255',
                'hs_code' => 'nullable|string|max:50',
                'is_wholesale'         => 'nullable|boolean',
                'vat_status'           => 'nullable|boolean',
                'excise_status'        => 'nullable|boolean',
                'excise_type'          => 'required_if:excise_status,1|nullable|in:percentage,fixed',
                'excise_percentage'    => 'required_if:excise_type,percentage|nullable|numeric|min:0|max:100',
                'excise_value'         => 'required_if:excise_type,fixed|nullable|numeric|min:0',
                'status'               => 'nullable|in:Y,N',
                'variations'           => 'nullable|array',
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

            // At least one product code required
            if (empty(trim($request->product_code)) && empty(trim($request->company_product_code))) {
                throw new Exception('Please enter at least one: Product Code or Company Product Code.', 1);
            }

            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');
            $message        = 'Records saved successfully';

            DB::beginTransaction();

            if (!Item::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }

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

    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();

            DB::beginTransaction();
            $result = Item::deleteItem($post);
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
        $post          = $request->all();
        $post['orgid'] = session('orgid'); // ← fix: pass orgid
        $data          = Item::list($post);

        $i            = 0;
        $array        = [];
        $totalrecs    = $data['totalrecs']        ?? 0;
        $filtereddata = $data['totalfilteredrecs'] ?? $totalrecs;

        unset($data['totalrecs'], $data['totalfilteredrecs']);

        foreach ($data as $row) {
            $array[$i]['sno']         = $request->input('start', 0) + $i + 1;
            $array[$i]['name']        = $row->title         ?? '—';
            $array[$i]['category']    = $row->categories    ?? [];
            $array[$i]['subcategory'] = $row->subcategories ?? [];
            $array[$i]['description'] = $row->description   ?? '—';
            $array[$i]['type']        = $row->type          ?? '—';
            $array[$i]['brand']       = $row->brand         ?? '—';

            $action  = '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteItem px-2" style="color:red;" data-id="'  . $row->id . '"><i class="bx bx-trash"></i></a>';
            $action .= '<a href="javascript:;" title="View Data"   class="tooltipdiv viewItem"  style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
            $action .= '<a href="javascript:;" title="Edit Data"   class="tooltipdiv editItem"  style="color:blue;"  data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';

            $array[$i]['action'] = $action;
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
            $post        = $request->all();
            $itemDetails = Item::getData($post);
            $data        = ['itemDetails' => $itemDetails];
            $data['type']    = 'success';
            $data['message'] = 'Successfully fetched data.';
        } catch (QueryException $e) {
            $data['type']    = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type']    = 'error';
            $data['message'] = $e->getMessage();
        }

        return view('backend.item.view', $data);
    }
}
