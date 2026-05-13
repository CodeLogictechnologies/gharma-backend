<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AddToCartRequest;
use App\Http\Requests\API\FavouriteDeleteRequest;
use App\Http\Requests\API\FavouriteSaveRequest;
use App\Http\Requests\API\OrderPlaceRequest;
use App\Models\API\Cart;
use App\Models\API\Favourite;
use App\Models\API\Order as APIOrder;
use App\Models\BackPanel\Order as BackPanelOrder;
use App\Models\Cart\API;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;



class FavouriteController extends Controller
{
    public function saveData(FavouriteSaveRequest $request)
    {
        try {
            $type = 'success';
            $message = 'Item added to Favourite successfully';

            DB::beginTransaction();
            $post = $request->all();
            if (!Favourite::saveData($post)) {
                throw new Exception('Could not add item to favourite', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = 'Something went wrong';
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function getFavouriteList(Request $request)
    {
        try {

            $type = 'success';
            $message = 'Favourite items fetched successfully.';
            $data = [];

            // ✅ SAFE JWT (no crash if token missing)
            try {
                if ($request->bearerToken()) {
                    $payload = JWTAuth::parseToken()->getPayload();
                    $profile = $payload->get('profile') ?? [];

                    $post['orgid']  = $profile['orgid'] ?? null;
                    $post['userid'] = $profile['userid'] ?? null;
                } else {
                    return response()->json([
                        'type' => 'error',
                        'message' => 'Unauthorized user',
                        'favourite' => []
                    ], 401);
                }
            } catch (Exception $e) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Invalid or expired token',
                    'favourite' => []
                ], 401);
            }

            // ✅ Get data
            $data = Favourite::getListData($post);

            // ✅ Empty case (optional)
            if (empty($data) || count($data) == 0) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'No favourite items found',
                    'favourite' => []
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'message' => $message,
                'favourite' => $data
            ], 200);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Database error occurred',
                'favourite' => []
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
                'favourite' => []
            ], 400);
        }
    }

    public function deleteFavourite(Request $request, $variationid)
    {
        try {

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $data = Favourite::deleteFavourite([
                'variationid' => $variationid,
                'userid'      => $profile['userid'],
                'orgid'       => $profile['orgid'],
            ]);

            if (!$data) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Favourite not found or already deleted'
                ], 404);
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Favourite item deleted successfully.'
            ], 200);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Database error occurred'
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}