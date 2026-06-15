<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loyalty;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\QueryException;
use Exception;

class LoyaltyController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post['userid'] = $profile['userid'];
            $post['orgid']  = $profile['orgid'];

            $point = Loyalty::getData($post);

            return response()->json([
                'type' => 'success',
                'message' => 'Loylaity point fetched successfully.',
                'loyalitypoint' => $point ?? 0
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'type' => 'error',
                'loyalitypoint' => 0
            ], 500);
        }
    }
}
