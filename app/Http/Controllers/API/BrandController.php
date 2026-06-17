<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Brand;
use Illuminate\Http\Request;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class BrandController extends Controller
{
    public function index(Request $request, $brandid = null)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $orgid = $profile['orgid'] ?? null;

            if (empty($orgid)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'orgid not found in token.',
                    'data'    => []
                ], 400);
            }

            $params = ['orgid' => $orgid];

            if (!empty($brandid)) {
                $params['brandid'] = $brandid;
            }

            $brands = Brand::getBrand($params);

            return response()->json([
                'status'  => true,
                'message' => 'Brands fetched successfully.',
                'data'    => $brands
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ], 500);
        }
    }
}