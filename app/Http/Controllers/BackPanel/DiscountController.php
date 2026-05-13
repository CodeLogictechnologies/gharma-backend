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

    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $data = Discount::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);
            foreach ($data as $row) {
                $array[$i]["sno"] = $i + 1;
                $array[$i]["title"]    = $row->title;
                $array[$i]["type"]    = $row->type;
                $array[$i]["applies_to"]    = $row->applies_to;
                $array[$i]["min_requirement"]    = $row->min_requirement;
                $array[$i]["starts_at"]    = $row->starts_at;
                $array[$i]["ends_at"]    = $row->ends_at;


                $action = '';

                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteDiscount px-2" style="color:red;" data-id="' . $row->id .  '"><i class="bx bx-trash"></i></a>';

                // $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewDiscount" style="color:green;" data-id="' . $row->id .  '"><i class="bx bx-show-alt"></i></a>';

                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editDiscount" style="color:blue;" data-id="' . $row->id .  '"><i class="bx bx-edit-alt"></i></a>';
                $array[$i]["action"]  = $action;
                $i++;
            }

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
            $post = $request->all();

            $orgDetails = Discount::getData($post);

            $data = [
                'orgDetails' => $orgDetails,
            ];

            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of Organization.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.discount.view', $data);
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