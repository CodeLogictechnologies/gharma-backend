<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\ReturnRefund;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefundReturnController extends Controller
{
    public function getReturnPolicy(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile') ?? [];
            $orgid   = $profile['orgid'] ?? null;

            $returnPolicy = ReturnRefund::getPolicy($orgid, 'return');

            return response()->json([
                'type'    => 'success',
                'message' => 'Return policy fetched successfully.',
                'data' => [
                    'return' => $returnPolicy->description ?? '',
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Invalid or expired token',
                'data' => null
            ], 401);
        }
    }

    public function getRefundPolicy(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile') ?? [];
            $orgid   = $profile['orgid'] ?? null;

            $refundPolicy = ReturnRefund::getPolicy($orgid, 'refund');

            return response()->json([
                'type'    => 'success',
                'message' => 'Refund policy fetched successfully.',
                'data' => [
                    'refund' => $refundPolicy->description ?? '',
                ],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Invalid or expired token',
                'data' => null
            ], 401);
        }
    }
}