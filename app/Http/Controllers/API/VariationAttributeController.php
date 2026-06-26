<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class VariationAttributeController extends Controller
{
    /**
     * Fetch all  variation attributes.
     *
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Variation attributes fetched successfully.';
            $orgid   = null;

            try {
                $payload = JWTAuth::parseToken()->getPayload();
                $profile = $payload->get('profile');
                $orgid   = $profile['orgid'] ?? null;
            } catch (Exception $e) {
                $orgid = $request->orgid ?? null;
            }

            $data = DB::table('variation_attributes')
                ->select('id', 'name')
                ->where('status', 'Y')
                ->when($orgid, fn($q) => $q->where('orgid', $orgid))
                ->orderBy('name')
                ->get();

            if ($data->isEmpty()) {
                throw new Exception('No variation attributes found.', 1);
            }
        } catch (QueryException $e) {
            $type    = 'error';
            $message = 'Something went wrong.';
            $data    = [];
        } catch (Exception $e) {
            $type    = 'error';
            $message = $e->getMessage();
            $data    = [];
        }

        return response()->json([
            'type'    => $type,
            'message' => $message,
            'result'  => $data,
        ]);
    }
}
