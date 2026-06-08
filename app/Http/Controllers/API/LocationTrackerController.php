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

            $post['userid'] = $profile['userid'];
            $post['orgid']  = $profile['orgid'];

            // No need transaction for SELECT
            $result = LocationTracker::getLocation($post);

            $result2 = LocationTracker::getLocationCustomer($post);
            if (!$result) {
                throw new Exception('Location failed to fetch', 1);
            }
            if (!$result2) {
                throw new Exception('Location failed to fetch', 1);
            }
            return response()->json([
                'type' => 'success',
                'message' => 'Location fetched successfully.',
                'driverlocation' => $result,
                'customerlocation' => $result2
            ], 200);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong',
                'driverlocation' => null,
                'customerlocation' => null,
            ], 500);
        } catch (\Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
                'driverlocation' => null,
                'customerlocation' => null,
            ], 500);
        }
    }
}