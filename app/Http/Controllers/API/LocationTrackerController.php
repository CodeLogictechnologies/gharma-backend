<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SaveLocationRequest;
use App\Models\API\LocationTracker;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Locale;
use Tymon\JWTAuth\Facades\JWTAuth;

class LocationTrackerController extends Controller
{
    public function saveLocation(SaveLocationRequest $request)
    {
        $result = null;
        try {
            $type = 'success';
            $message = 'LocationTracker save successfully';

            $post = $request->all();

            DB::beginTransaction();
            $result = LocationTracker::saveLocation($post);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message, 'location' => $result]);
    }
    public function getOrderLocation(Request $request)
    {
        try {
            $post = $request->all();

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $userId = $profile['userid'];
            $orgId  = $profile['orgid'];

            // No need transaction for SELECT
            $result = LocationTracker::getLocation($post);
            if (!$result) {
                throw new Exception('Location failed to fetch', 1);
            }
            return response()->json([
                'type' => 'success',
                'message' => 'Location fetched successfully.',
                'location' => $result
            ], 200);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
                'location' => null
            ], 500);
        } catch (\Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
                'location' => null
            ], 500);
        }
    }
}
