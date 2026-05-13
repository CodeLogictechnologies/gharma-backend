<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\API\SearchHistory;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Laravel\Prompts\search;

class SearchHistoryController extends Controller
{
    public function searchSave(Request $request)
    {
        try {
            DB::beginTransaction();

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post = $request->all();
            $post['orgid']  = $profile['orgid'] ?? null;
            $post['userid'] = $profile['userid'] ?? null;

            if (empty($post['orgid'])) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Organization ID missing'
                ], 400);
            }

            if (empty($post['search'])) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Search keyword is required'
                ], 400);
            }

            if (!SearchHistory::saveData($post)) {
                throw new Exception('Could not save search');
            }

            DB::commit();

            return response()->json([
                'type' => 'success',
                'message' => 'Search saved successfully'
            ], 200);
        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function searchDelete(Request $request, $searchid)
    {
        try {
            DB::beginTransaction();
            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();

            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];
            $post['searchid'] = $searchid;
            if (empty($post['searchid'])) {
                throw new \Exception('Searchid is required.');
            }
            if ($post['searchid'] === 'all') {
                if (!SearchHistory::deleteSearchBulk($post)) {
                    throw new \Exception('Could not delete search.');
                }
            } else {
                if (!SearchHistory::deleteSearch($post)) {
                    throw new \Exception('Could not delete search.');
                }
            }
            DB::commit();

            return response()->json([
                'type'    => 'success',
                'message' => 'Search delete'
            ], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
