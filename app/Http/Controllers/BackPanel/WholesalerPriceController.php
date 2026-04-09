<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Brand;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\ItemImage;
use App\Models\BackPanel\Itemvariation;
use App\Models\BackPanel\SubCategory;
use App\Models\BackPanel\WholesalerPrice as BackPanelWholesalerPrice;
use App\Models\Item\BackPanel;
use App\Models\WholesalerPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\QueryException;
use Exception;

class WholesalerPriceController extends Controller
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
        return view('backend.wholesaler.index', $data);
    }

    public function form(Request $request)
    {
        $id   = $request->id ?? null;
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $items = Item::getItem($post);

        $data = [
            'id'                       => null,
            'title'                    => '',
            'wholesaler_price_details' => [
                [
                    'wholesaler_price_details_id' => '',
                    'min_qty'                     => '',
                    'max_qty'                     => '',
                    'price'                       => '',
                ],
            ],
        ];

        if (!empty($id)) {
            $wholesaler = BackPanelWholesalerPrice::findOrFail($id);

            $existingDetails = DB::table('wholesaler_price_details')
                ->where('wholesalermasterid', $id)
                ->get()
                ->map(fn($v) => [
                    'wholesaler_price_details_id' => $v->id,
                    'min_qty'                     => $v->min_qty,
                    'max_qty'                     => $v->max_qty,
                    'price'                       => $v->price,
                ])
                ->toArray();

            $data = [
                'id'                       => $wholesaler->id,
                'itemid'                   => $wholesaler->itemid,
                'variation_id'             => $wholesaler->variation_id,
                'wholesaler_price_details' => !empty($existingDetails)
                    ? $existingDetails
                    : $data['wholesaler_price_details'],
            ];
        }

        return view('backend.wholesaler.form', [
            'data'  => $data,
            'items' => $items,
            'id'    => $id,
        ]);
    }

    /**
     * Save (create or update) an item.
     */
    public function save(Request $request)
    {      // try {
        $type = 'success';
        $rules = [
            'itemid'          => 'required',
            'variationid'          => 'required',
        ];

        $message = [
            'itemid.required' => 'Select Product.',
            'itemid.required' => 'Select Variation.',
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

        if (!BackPanelWholesalerPrice::saveData($post)) {
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



    public function list(Request $request)
    {
        $post = $request->all();
        $data = BackPanelWholesalerPrice::list($post);
        $i            = 0;
        $array        = [];
        $totalrecs    = $data['totalrecs']        ?? 0;
        $filtereddata = $data['totalfilteredrecs'] ?? $totalrecs;
        unset($data['totalrecs'], $data['totalfilteredrecs']);
        foreach ($data as $row) {

            // Serial number with pagination offset
            $array[$i]['sno'] = $request->input('start', 0) + $i + 1;

            // Item columns (matching your items migration)
            $array[$i]['title']         = $row->title           ?? '—';
            $array[$i]['value']         = $row->variation_name           ?? '—';
            $array[$i]['price'] = $row->price ?? '—';
            $array[$i]['min_qty'] = $row->min_qty ?? '—';
            $array[$i]['max_qty'] = $row->max_qty ?? '—';
            $action = '';

            $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteWholesaleprice px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';
            // for show
            $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewWholesaleprice" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';

            $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editWholesaleprice" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';
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
        // try {
        $post = $request->all();

        $itemDetails = Item::getData($post);

        $data = [
            'itemDetails' => $itemDetails,
        ];

        $data['type'] = 'success';
        $data['message'] = 'Successfully fetched data of New and Blogs.';
        // } catch (QueryException $e) {
        //     $data['type'] = 'error';
        //     $data['message'] = $this->queryMessage;
        // } catch (Exception $e) {
        //     $data['type'] = 'error';
        //     $data['message'] = $e->getMessage();
        // }
        return view('backend.item.view', $data);
    }
}