<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\TermsPrivacy;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class TermsAndPrivacyController extends Controller
{
    public function getTermsAndPrivacyPolicy(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile') ?? [];
            $orgid   = $profile['orgid'] ?? null;

            $termsPolicy   = TermsPrivacy::getPolicy($orgid, 'terms');
            $privacyPolicy = TermsPrivacy::getPolicy($orgid, 'privacy');

            return response()->json([
                'type'    => 'success',
                'message' => 'Terms and privacy policy fetched successfully.',
                'data' => [
                    [
                        'type'        => 'terms',
                        'description' => $termsPolicy->description ?? '',
                    ],
                    [
                        'type'        => 'privacy',
                        'description' => $privacyPolicy->description ?? '',
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