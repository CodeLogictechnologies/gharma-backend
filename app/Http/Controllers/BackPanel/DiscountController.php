<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountRequest;
use App\Models\BackPanel\Discount;
use App\Models\BackPanel\Item;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    public function index()
    {
        return view('backend.discount.index');
    }

    public function save(DiscountRequest $request)
    {
        try {

            $post = $request->validated();

            $post['userid'] = session('userid');
            $post['orgid']  = session('orgid');

            Discount::saveData($post);

            return response()->json([
                'status'  => true,
                'type'    => 'success',
                'message' => !empty($post['id'])
                    ? 'Discount updated successfully.'
                    : 'Discount saved successfully.',
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'Something went wrong. Please try again.',
            ], 500);
        }
    }
    public function list(Request $request)
    {
        try {
            $get = $request->all();

            $cond  = "status = 'Y'  AND orgid = ?";

            $binds = [session('orgid')];

            if (!empty($get['sSearch_1'])) {
                $cond   .= " AND LOWER(applies_to) LIKE ?";
                $binds[] = '%' . strtolower(trim($get['sSearch_1'])) . '%';
            }

            $limit  = (int) ($get['iDispllayLength'] ?? 15);
            $offset = (int) ($get['iDisplayStart']  ?? 0);


            $totalrecs = DB::table('discount_masters')->whereRaw($cond, $binds)->count();

            $query = DB::table('discount_masters')
                ->selectRaw("id, applies_to, start_date_bs as starts_at, end_date_bs as ends_at")
                ->whereRaw($cond, $binds)
                ->orderBy('created_at', 'desc');

            $filteredCount = (clone $query)->count();

            $result = ($limit > -1)
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            $rows = $result->map(function ($row, $i) use ($offset) {
                $action  = '';
                $action .= '<a href="javascript:;" title="View"   class="tooltipdiv viewDiscount px-2" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a>';
                $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deleteDiscount px-2" style="color:red;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';

                return [
                    'sno'             => $offset + $i + 1,
                    'applies_to'      => ucfirst($row->applies_to),
                    'starts_at'       => $row->starts_at
                        ? \Carbon\Carbon::parse($row->starts_at)->format('d M Y') : '-',
                    'ends_at'         => $row->ends_at
                        ? \Carbon\Carbon::parse($row->ends_at)->format('d M Y') : '-',
                    'action'          => $action,
                ];
            });

            return response()->json([
                'iTotalRecords'        => $totalrecs,
                'iTotalDisplayRecords' => $filteredCount,
                'aaData'               => $rows,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function form(Request $request)
    {
        try {
            $data = [];

            if (!empty($request->id)) {
                $result = Discount::where('id', $request->id)
                    ->where('orgid', session('orgid'))
                    ->where('type', '!=', 'coupon')
                    ->first();

                if (!$result) {
                    throw new \Exception("Discount not found.");
                }

                $data['id']                   = $result->id;
                $data['userid']               = $result->postedby;
                $data['title']                = $result->title;
                $data['type']                 = $result->type;
                $data['percentage']           = $result->percentage;
                $data['value']                = $result->value;
                $data['discount_type']        = $result->discount_type;
                $data['applies_to']           = $result->applies_to;
                $data['item_id']              = $result->item_id;
                $data['variation_id']         = $result->variation_id;
                $data['min_requirement']      = $result->min_requirement;
                $data['min_value']            = $result->min_value;
                $data['usage_limit_type']     = $result->usage_limit_type;
                $data['usage_limit']          = $result->usage_limit;
                $data['usage_limit_per_user'] = $result->usage_limit_per_user;
                $data['starts_at']            = $result->starts_at;
                $data['ends_at']              = $result->ends_at;
                $data['orgid']                = $result->orgid;
                $data['status']               = $result->status;
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $data['error'] = 'Database error: ' . $e->getMessage();
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.discount.form', $data);
    }

    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Record deleted successfully';
            $post    = $request->all();
            $post['orgid'] = session('orgid');

            DB::beginTransaction();
            $result = Discount::deleteDate($post);
            if (!$result) {
                throw new Exception("Discount could not be deleted.", 1);
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

    public function view(Request $request)
    {
        try {
            $discounts = DB::table('discount_details as dd')
                ->join('itemvariations as iv', 'iv.id', '=', 'dd.variation_id')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->select(
                    'iv.id as variation_id',
                    'i.title',
                    'iv.attribute',
                    'iv.value',
                    'dd.discount_type',
                    'dd.discount_amount',
                    'dd.total_amount'
                )
                ->where('dd.discount_master_id', $request->id)
                ->where('dd.orgid', session('orgid'))
                ->where('iv.orgid', session('orgid'))
                ->where('i.orgid', session('orgid'))
                ->get();

            if (!$discounts) {
                return view('backend.discount.view', [
                    'type' => 'error',
                    'message' => 'Discount not found.'
                ]);
            }

            return view('backend.discount.view', [
                'type' => 'success',
                'discounts' => $discounts
            ]);
        } catch (\Exception $e) {
            return view('backend.discount.view', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function lists(Request $request)
    {
        $query = DB::table('itemvariations as iv')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->select(
                'iv.id',
                'i.title',
                'iv.attribute',
                'iv.value'
            );

        if ($request->filled('category_id')) {
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->where('ci.categoryid', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->join('sub_category_items as sci', 'sci.itemid', '=', 'i.id')
                ->where('sci.subcategoryid', $request->sub_category_id);
        }

        if ($request->filled('sub_sub_category_id')) {
            $query->join('sub_sub_category_items as ssci', 'ssci.itemid', '=', 'i.id')
                ->where('ssci.subsubcategoryid', $request->sub_sub_category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('i.brandid', $request->brand_id);
        }

        $data = $query->get()->map(function ($row) {
            return [
                'id'    => $row->id, // variation id
                'title' => $row->title .
                    (!empty($row->attribute)
                        ? ' (' . $row->attribute . ': ' . $row->value . ')'
                        : ''),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }

    public function variations($id)
    {
        $variations = DB::table('itemvariations')
            ->select('id', 'attribute', 'value')
            ->where('item_id', $id)
            ->get();
        return response()->json(['data' => $variations]);
    } // GET /admin/discount/categories?level=1
    public function categoriesByLevel(Request $request)
    {
        $items = DB::table('categories')
            ->select('id', 'title')
            ->where('level', $request->level)
            ->where('orgid', session('orgid'))
            ->where('status', 'Y')
            ->orderBy('title')
            ->get();

        return response()->json(['data' => $items]);
    }

    // GET /admin/discount/brands
    public function brandsList()
    {
        $items = DB::table('brands')
            ->select('id', 'title')
            ->where('orgid', session('orgid'))
            ->where('status', 'Y')
            ->orderBy('title')
            ->get();

        return response()->json(['data' => $items]);
    }
}