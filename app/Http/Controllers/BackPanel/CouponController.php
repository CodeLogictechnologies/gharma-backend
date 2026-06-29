<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Coupon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function index()
    {
        return view('backend.coupon.index');
    }

    public function save(Request $request)
    {
        $request->validate([
            'coupon_code'          => 'required|string|max:100',
            'discount_type'        => 'required|in:percentage,fixed',
            'percentage'           => 'nullable|numeric|min:0|max:100',
            'value'                => 'nullable|numeric|min:0',
            'applies_to'           => 'required|in:entire,item,variation',
            'item_id'              => 'nullable|uuid',
            'variation_id'         => 'nullable|uuid',
            'min_requirement'      => 'nullable|in:none,purchase,quantity',
            'min_value'            => 'nullable|numeric|min:0',
            'usage_limit_type'     => 'nullable|in:once,limited,per_user',
            'usage_limit'          => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at'            => 'required|date',
            'ends_at'              => 'required|date|after_or_equal:starts_at',
        ]);

        try {
            $post           = $request->all();
            $post['userid'] = session('userid');
            $post['orgid']  = session('orgid');

            Coupon::saveData($post);

            return response()->json([
                'type'    => 'success',
                'message' => !empty($post['id']) ? 'Coupon updated successfully.' : 'Coupon saved successfully.',
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
            $get   = $request->all();
            $cond  = "status = 'Y'";
            $binds = [];

            if (!empty($get['sSearch_1'])) {
                $cond   .= " AND LOWER(coupon_code) LIKE ?";
                $binds[] = '%' . strtolower(trim($get['sSearch_1'])) . '%';
            }
            if (!empty($get['sSearch_2'])) {
                $cond   .= " AND LOWER(applies_to) LIKE ?";
                $binds[] = '%' . strtolower(trim($get['sSearch_2'])) . '%';
            }

            $limit  = (int) ($get['iDisplayLength'] ?? 15);
            $offset = (int) ($get['iDisplayStart']  ?? 0);

            $query = DB::table('coupons')
                ->selectRaw("
                    (SELECT COUNT(*) FROM coupons WHERE {$cond}) AS totalrecs,
                    id, coupon_code, discount_type, percentage, value,
                    applies_to, min_requirement, usage_limit_type,
                    used_count, starts_at, ends_at
                ", $binds)
                ->whereRaw($cond, $binds)
                ->orderBy('created_at', 'desc');

            $result = ($limit > -1)
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            $totalrecs = $result->isNotEmpty() ? $result[0]->totalrecs : 0;

            $rows = $result->map(function ($row, $i) use ($offset) {
                $discountLabel = $row->discount_type === 'fixed'
                    ? 'Rs ' . number_format($row->value, 2) . ' off'
                    : number_format($row->percentage, 2) . '% off';

                $action  = '';
                $action .= '<a href="javascript:;" title="View"   class="tooltipdiv viewCoupon   px-2" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a>';
                $action .= '<a href="javascript:;" title="Edit"   class="tooltipdiv editCoupon   px-2" style="color:blue;"  data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Delete" class="tooltipdiv deleteCoupon px-2" style="color:red;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';

                return [
                    'sno'              => $offset + $i + 1,
                    'coupon_code'      => strtoupper($row->coupon_code ?? '-'),
                    'discount'         => $discountLabel,
                    'applies_to'       => ucfirst($row->applies_to),
                    'min_requirement'  => ucfirst($row->min_requirement ?? 'none'),
                    'usage_limit_type' => ucfirst(str_replace('_', ' ', $row->usage_limit_type ?? 'once')),
                    'used_count'       => $row->used_count ?? 0,
                    'starts_at'        => $row->starts_at
                        ? \Carbon\Carbon::parse($row->starts_at)->format('d M Y') : '-',
                    'ends_at'          => $row->ends_at
                        ? \Carbon\Carbon::parse($row->ends_at)->format('d M Y') : '-',
                    'action'           => $action,
                ];
            });

            return response()->json([
                'sEcho'                => (int) ($get['sEcho'] ?? 1),
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
                $result = DB::table('coupons')->where('id', $request->id)->first();

                if (!$result) {
                    throw new \Exception("Coupon not found.");
                }

                $data['id']                   = $result->id;
                $data['userid']               = $result->postedby;
                $data['coupon_code']          = $result->coupon_code;
                $data['discount_type']        = $result->discount_type;
                $data['percentage']           = $result->percentage;
                $data['value']                = $result->value;
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
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.coupon.form', $data);
    }

    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Coupon deleted successfully.';

            DB::beginTransaction();
            $result = Coupon::deleteData($request->all());
            if (!$result) {
                throw new Exception("Could not delete coupon.");
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
            $coupon = DB::table('coupons')->where('id', $request->id)->first();

            if (!$coupon) {
                return view('backend.coupon.view', [
                    'type'    => 'error',
                    'message' => 'Coupon not found.',
                ]);
            }

            if (!empty($coupon->item_id)) {
                $item = DB::table('items')->where('id', $coupon->item_id)->first();
                $coupon->item_title = $item->title ?? null;
            }

            if (!empty($coupon->variation_id)) {
                $variation = DB::table('itemvariations')->where('id', $coupon->variation_id)->first();
                $coupon->variation_label = $variation
                    ? ($variation->attribute . ' - ' . $variation->value)
                    : null;
            }

            return view('backend.coupon.view', [
                'type'   => 'success',
                'coupon' => $coupon,
            ]);
        } catch (\Exception $e) {
            return view('backend.coupon.view', [
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function items()
    {
        $items = DB::table('items')->select('id', 'title')->get();
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
