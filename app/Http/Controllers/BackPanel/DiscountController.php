<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SessionController;
use App\Http\Requests\DiscountRequest;
use App\Models\BackPanel\TeamCategory;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Discount;
use App\Models\Common;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            $post['orgid'] = session('orgid');

            Discount::saveData($post);

            return response()->json([
                'type'    => 'success',
                'message' => !empty($post['id']) ? 'Discount updated successfully.' : 'Discount saved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // public function list(Request $request)
    // {
    //     try {
    //         $post = $request->all();
    //         $data = Discount::list($post);
    //         $i = 0;
    //         $array = [];
    //         $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
    //         $totalrecs = $data["totalrecs"];

    //         unset($data["totalfilteredrecs"]);
    //         unset($data["totalrecs"]);
    //         foreach ($data as $row) {
    //             $array[$i]["sno"] = $i + 1;
    //             $array[$i]["title"]    = $row->title;
    //             $array[$i]["type"]    = $row->type;
    //             $array[$i]["applies_to"]    = $row->applies_to;
    //             $array[$i]["min_requirement"]    = $row->min_requirement;
    //             $array[$i]["starts_at"]    = $row->starts_at;
    //             $array[$i]["ends_at"]    = $row->ends_at;


    //             $action = '';

    //             $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteDiscount px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';

    //             // $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewDiscount" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';

    //             $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editDiscount" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';
    //             $array[$i]["action"]  = $action;
    //             $i++;
    //         }

    //         if (!$filtereddata) $filtereddata = 0;
    //         if (!$totalrecs) $totalrecs = 0;
    //     } catch (QueryException $e) {
    //         $array = [];
    //         $totalrecs = 0;
    //         $filtereddata = 0;
    //     } catch (Exception $e) {
    //         $array = [];
    //         $totalrecs = 0;
    //         $filtereddata = 0;
    //     }
    //     return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    // }

    public function list(Request $request)
    {
        try {
            $get = $request->all();

            $cond  = "status = 'Y'";
            $binds = [];

            if (!empty($get['sSearch_1'])) {
                $cond   .= " AND LOWER(title) LIKE ?";
                $binds[] = '%' . strtolower(trim($get['sSearch_1'])) . '%';
            }
            if (!empty($get['sSearch_2'])) {
                $cond   .= " AND LOWER(type) LIKE ?";
                $binds[] = '%' . strtolower(trim($get['sSearch_2'])) . '%';
            }
            if (!empty($get['sSearch_3'])) {
                $cond   .= " AND LOWER(applies_to) LIKE ?";
                $binds[] = '%' . strtolower(trim($get['sSearch_3'])) . '%';
            }

            $limit  = (int) ($get['length'] ?? 15);
            $offset = (int) ($get['start']  ?? 0);

            $query = DB::table('discounts')
                ->selectRaw("
                (SELECT COUNT(*) FROM discounts WHERE {$cond}) AS totalrecs,
                id, title, type, applies_to, min_requirement, starts_at, ends_at
            ", $binds)
                ->whereRaw($cond, $binds)
                ->orderBy('created_at', 'desc');

            $result = ($limit > -1)
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            $totalrecs = $result->isNotEmpty() ? $result[0]->totalrecs : 0;

            $rows = $result->map(function ($row, $i) use ($offset) {

                // Actions
                $action  = '';
                $action .= '<a href="javascript:;" title="View"   class="tooltipdiv viewOrg        px-2" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a>';
                $action .= '<a href="javascript:;" title="Edit"   class="tooltipdiv editDiscount   px-2" style="color:blue;"  data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deleteDiscount px-2" style="color:red;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';

                return [
                    'sno'             => $offset + $i + 1,
                    'title'           => $row->title ?? '-',
                    'type'            => ucfirst($row->type),
                    'applies_to'      => ucfirst($row->applies_to),
                    'min_requirement' => ucfirst($row->min_requirement),
                    'starts_at'       => $row->starts_at
                        ? \Carbon\Carbon::parse($row->starts_at)->format('d M Y') : '-',
                    'ends_at'         => $row->ends_at
                        ? \Carbon\Carbon::parse($row->ends_at)->format('d M Y') : '-',
                    'action'          => $action,
                ];
            });

            return response()->json([
                'iTotalRecords'        => $totalrecs,
                'iTotalDisplayRecords' => $totalrecs,
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
                $result = Discount::find($request->id);

                if (!$result) {
                    throw new \Exception("Discount not found.");
                }

                $data['id']                   = $result->id;
                $data['userid']               = $result->postedby;

                // ── Basic ──────────────────────────────────────────
                $data['title']                = $result->title;
                $data['type']                 = $result->type;
                $data['percentage']           = $result->percentage;
                $data['value']                = $result->value;
                $data['coupon_code']  = $result->coupon_code;   
                $data['discount_type']                = $result->discount_type;

                // ── Applies To ─────────────────────────────────────
                $data['applies_to']           = $result->applies_to;
                $data['item_id']              = $result->item_id;
                $data['variation_id']         = $result->variation_id;

                // ── Minimum Requirement ────────────────────────────
                $data['min_requirement']      = $result->min_requirement;
                $data['min_value']            = $result->min_value;

                // ── Usage Limits ───────────────────────────────────
                $data['usage_limit_type']     = $result->usage_limit_type;
                $data['usage_limit']          = $result->usage_limit;
                $data['usage_limit_per_user'] = $result->usage_limit_per_user;

                // ── Dates ──────────────────────────────────────────
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


    // Delete
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";
            $post = $request->all();

            DB::beginTransaction();
            $result = Discount::deleteDate($post);
            if (!$result) {
                throw new Exception("Organization not delete", 1);
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
        return json_encode(['type' => $type, 'message' => $message]);
    }

    //view


    public function view(Request $request)
    {
        try {
            $discount = DB::table('discounts')->where('id', $request->id)->first();

            if (!$discount) {
                return view('backend.discount.view', [
                    'type'    => 'error',
                    'message' => 'Discount not found.',
                ]);
            }

            // Optionally resolve item title and variation label
            if (!empty($discount->item_id)) {
                $item = DB::table('items')->where('id', $discount->item_id)->first();
                $discount->item_title = $item->title ?? null;
            }

            if (!empty($discount->variation_id)) {
                $variation = DB::table('item_variations')->where('id', $discount->variation_id)->first();
                $discount->variation_label = $variation
                    ? ($variation->attribute . ' - ' . $variation->value)
                    : null;
            }

            return view('backend.discount.view', [
                'type'     => 'success',
                'discount' => $discount,
            ]);
        } catch (\Exception $e) {
            return view('backend.discount.view', [
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }


    // app/Http/Controllers/API/ItemController.php

    public function lists()
    {
        $items = DB::table('items')
            ->select('id', 'title')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function variations($id)
    {
        $variations = DB::table('itemvariations')
            ->select('id', 'attribute', 'value')
            ->where('item_id', $id)
            ->get();

        return response()->json(['data' => $variations]);
    }
}
