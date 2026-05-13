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
}
