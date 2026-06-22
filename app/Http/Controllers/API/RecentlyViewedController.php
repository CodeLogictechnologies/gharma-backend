<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\RecentlyViewed;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class RecentlyViewedController extends Controller
{
    /**
     * GET index
     */
    public function index(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile') ?? [];

            $post = [
                'userid'   => $profile['userid'] ?? null,
                'orgid'    => $profile['orgid']  ?? null,
                'page'     => $request->get('page', 1),
                'per_page' => $request->get('per_page', 10),
            ];

            if (empty($post['userid']) || empty($post['orgid'])) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Invalid token payload.',
                    'data'    => [],
                ], 401);
            }

            $result = RecentlyViewed::getList($post);

            if (collect($result['data'])->isEmpty()) {
                return response()->json([
                    'type'       => 'success',
                    'message'    => 'No recently viewed items found.',
                    'data'       => [],
                    'pagination' => $result['pagination'],
                ], 200);
            }

            return response()->json([
                'type'       => 'success',
                'message'    => 'Recently viewed items fetched successfully.',
                'data'       => $result['data'],
                'pagination' => $result['pagination'],
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Database error occurred.',
                'data'    => [],
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Invalid or expired token',
                'data'    => [],
            ], 401);
        }
    }

    /**
     * POST /api/recently-viewed/save
     */
    public function store(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile') ?? [];

            $post = [
                'userid'      => $profile['userid'] ?? null,
                'orgid'       => $profile['orgid']  ?? null,
                'variationid' => $request->input('variationid'),
            ];

            if (empty($post['userid']) || empty($post['orgid'])) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Invalid token payload.',
                    'data'    => null,
                ], 401);
            }

            if (empty($post['variationid'])) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'variationid is required.',
                    'data'    => null,
                ], 422);
            }

            $saved  = RecentlyViewed::saveData($post);
            $detail = $saved ? RecentlyViewed::getVariationDetail($post['variationid']) : null;

            return response()->json([
                'type'    => $saved ? 'success' : 'error',
                'message' => $saved
                    ? 'Item logged as recently viewed.'
                    : 'Could not log this view.',
                'data'    => $detail,
            ], $saved ? 200 : 400);

        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Database error occurred.',
                'data'    => null,
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Invalid or expired token',
                'data'    => null,
            ], 401);
        }
    }

    /**
     * DELETE /api/recently-viewed/delete/{variationid}
     */
    // public function destroy(Request $request, $variationid)
    // {
    //     try {
    //         $payload = JWTAuth::parseToken()->getPayload();
    //         $profile = $payload->get('profile') ?? [];

    //         $post = [
    //             'userid'      => $profile['userid'] ?? null,
    //             'orgid'       => $profile['orgid']  ?? null,
    //             'variationid' => $variationid,
    //         ];

    //         if (empty($post['userid']) || empty($post['orgid'])) {
    //             return response()->json([
    //                 'type'    => 'error',
    //                 'message' => 'Invalid token payload.',
    //                 'data'    => null,
    //             ], 401);
    //         }

    //         $deleted = RecentlyViewed::deleteOne($post);

    //         return response()->json([
    //             'type'    => $deleted ? 'success' : 'error',
    //             'message' => $deleted
    //                 ? 'Removed from recently viewed.'
    //                 : 'Item not found in recently viewed.',
    //             'data'    => null,
    //         ], $deleted ? 200 : 404);

    //     } catch (QueryException $e) {
    //         return response()->json([
    //             'type'    => 'error',
    //             'message' => 'Database error occurred.',
    //             'data'    => null,
    //         ], 500);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'type'    => 'error',
    //             'message' => 'Invalid or expired token',
    //             'data'    => null,
    //         ], 401);
    //     }
    // }
}