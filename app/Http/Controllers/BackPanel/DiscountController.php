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
        if (!auth()->user()->can($request->id ? 'edit.discount' : 'add.discount')) {
            return response()->json(['status' => false, 'type' => 'error', 'message' => 'Unauthorized.'], 403);
        }
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
        if (!auth()->user()->can('view.discount')) {
            return response()->json(['iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'aaData' => []]);
        }
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
                ->selectRaw("id,title,applies_to, start_date_bs as starts_at, end_date_bs as ends_at, starts_time, ends_time")
                ->whereRaw($cond, $binds)
                ->orderBy('created_at', 'desc');

            $filteredCount = (clone $query)->count();

            $result = ($limit > -1)
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            $rows = $result->map(function ($row, $i) use ($offset) {
                $action  = '';

                if (auth()->user()->can('view.discount')) {
                    $action .= '<a href="javascript:;" title="View" class="tooltipdiv viewDiscount px-2" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a>';
                }

                if (auth()->user()->can('edit.discount')) {
                    $action .= '<a href="javascript:;" title="Edit" class="tooltipdiv editDiscount px-2" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                }

                if (auth()->user()->can('delete.discount')) {
                    $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deleteDiscount px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
                }
                return [
                    'sno'             => $offset + $i + 1,
                    'discount_title'  => $row->title,
                    'applies_to'      => ucfirst($row->applies_to),
                    'start_date'      => $row->starts_at
                        ? \Carbon\Carbon::parse($row->starts_at)->format('d M Y') : '-',
                    'start_time'      => $row->starts_time
                        ? \Carbon\Carbon::createFromFormat('H:i', $row->starts_time)->format('h:i A') : '-',
                    'end_date'        => $row->ends_at
                        ? \Carbon\Carbon::parse($row->ends_at)->format('d M Y') : '-',
                    'end_time'        => $row->ends_time
                        ? \Carbon\Carbon::createFromFormat('H:i', $row->ends_time)->format('h:i A') : '-',
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
        if (!auth()->user()->can($request->id ? 'edit.discount' : 'add.discount')) {
            abort(403);
        }
        try {
            $data = [];

            if (!empty($request->id)) {
                $result = Discount::where('id', $request->id)
                    ->where('orgid', session('orgid'))
                    ->first();

                if (!$result) {
                    throw new \Exception("Discount not found.");
                }

                $data['id']                   = $result->id;
                $data['title']                = $result->title;
                $data['applies_to']           = $result->applies_to;
                $data['min_requirement']      = $result->min_requirement;
                $data['min_value']            = $result->min_value;
                $data['max_value']            = $result->max_value;
                $data['usage_limit_type']     = $result->usage_limit_type;
                $data['usage_limit']          = $result->usage_limit;
                $data['usage_limit_per_user'] = $result->usage_limit_per_user;
                $data['starts_at']            = $result->start_date_bs;
                $data['ends_at']               = $result->end_date_bs;
                $data['starts_time']          = $result->starts_time;
                $data['ends_time']            = $result->ends_time;
                $data['orgid']                = $result->orgid;
                $data['status']               = $result->status;

                // "Applies to" target dropdowns, based on applies_to_id.
                // Only the leaf category id is stored, so walk parent_id up
                // the categories table to reconstruct the full chain for the cascading dropdowns.
                if ($result->applies_to === 'category') {

                    $data['category_target_id'] = $result->applies_to_id;
                } elseif ($result->applies_to === 'sub_category') {

                    $data['sub_category_target_id'] = $result->applies_to_id;

                    $subCat = DB::table('categories')->where('id', $result->applies_to_id)->first();
                    $data['category_target_id'] = $subCat->parent_id ?? null;
                } elseif ($result->applies_to === 'sub_sub_category') {

                    $data['sub_sub_category_target_id'] = $result->applies_to_id;

                    $subSubCat = DB::table('categories')->where('id', $result->applies_to_id)->first();

                    if ($subSubCat) {
                        $data['sub_category_target_id'] = $subSubCat->parent_id;

                        $subCat = DB::table('categories')->where('id', $subSubCat->parent_id)->first();
                        $data['category_target_id'] = $subCat->parent_id ?? null;
                    }
                } elseif ($result->applies_to === 'brand') {

                    $data['brand_target_id'] = $result->applies_to_id;
                }

                // Pre-check items for applies_to = item
                $data['selected_item_ids'] = DB::table('discount_details')
                    ->where('discount_master_id', $result->id)
                    ->where('orgid', session('orgid'))
                    ->pluck('variation_id')
                    ->toArray();

                // Grab discount type/value from the first detail row (item-level discounts only)
                $firstDetail = DB::table('discount_details')
                    ->where('discount_master_id', $result->id)
                    ->where('orgid', session('orgid'))
                    ->first();

                if ($firstDetail) {
                    $data['type']  = $firstDetail->discount_type === 'percentage' ? 'percentage' : 'fixed';
                    if ($firstDetail->discount_type === 'percentage') {
                        $data['percentage'] = $firstDetail->discount_value;
                    } else {
                        $data['value'] = $firstDetail->discount_value;
                    }
                }
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
        if (!auth()->user()->can('delete.discount')) {
            return json_encode(['type' => 'error', 'message' => 'Unauthorized.']);
        }
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
        if (!auth()->user()->can('view.discount')) {
            return view('backend.discount.view', [
                'type' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }
        try {
            $discounts = DB::table('discount_details as dd')
                ->join('itemvariations as iv', 'iv.id', '=', 'dd.variation_id')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
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
                ->where('dm.orgid', session('orgid'))
                ->get();

            // Fetch the discount master title separately since it's one value shared by all rows
            $discountTitle = DB::table('discount_masters')
                ->where('id', $request->id)
                ->where('orgid', session('orgid'))
                ->value('title');

            if (!$discounts) {
                return view('backend.discount.view', [
                    'type' => 'error',
                    'message' => 'Discount not found.'
                ]);
            }

            return view('backend.discount.view', [
                'type' => 'success',
                'discount_title' => $discountTitle,
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
        $orgid = session('orgid');

        $query = DB::table('itemvariations as iv')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->where('iv.orgid', $orgid)
            ->where('i.orgid', $orgid)
            ->where('i.status', 'Y')
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
            $query->where('i.brand_id', $request->brand_id);
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
            ->select('id', 'name')
            ->where('orgid', session('orgid'))
            ->where('status', 'Y')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $items]);
    }
}
