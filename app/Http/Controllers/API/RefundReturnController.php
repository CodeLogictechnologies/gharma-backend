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
    public function getReturnRefundPolicy(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile') ?? [];
            $orgid   = $profile['orgid'] ?? null;

            $returnPolicy = ReturnRefund::getPolicy($orgid, 'return');
            $refundPolicy = ReturnRefund::getPolicy($orgid, 'refund');

            return response()->json([
                'type'    => 'success',
                'message' => 'Return and refund policy fetched successfully.',
                'data' => [
                    [
                        'type'        => 'return',
                        'description' => $returnPolicy->description ?? '',
                    ],
                    [
                        'type'        => 'refund',
                        'description' => $refundPolicy->description ?? '',
                    ],
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
