<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CouponController extends Controller
{
    public function getCouponList(Request $request)
    {
        try {
            $post = $request->all();

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post['userid'] = $profile['userid'];
            $post['orgid']  = $profile['orgid'];

            $result = Coupon::getList($post);

            return response()->json([
                'type'    => 'success',
                'message' => 'Coupons fetched successfully.',
                'data'    => $result,
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong',
                'data'    => null,
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
