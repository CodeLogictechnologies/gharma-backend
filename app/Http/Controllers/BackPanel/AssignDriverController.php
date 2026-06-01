<?php


namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\API\GetLoactionDataRequest;
use App\Http\Requests\API\UserAddressRequest;
use App\Models\API\UserAddress;
use App\Models\BackPanel\AssignDriver;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Tymon\JWTAuth\Facades\JWTAuth;

class AssignDriverController extends Controller
{
    public static function getOrderList(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post             = $request->all();
            $post['userid']   = $profile['userid'] ?? null;
            $post['orgid']    = $profile['orgid']  ?? null;
            $post['page']     = (int) $request->input('page', 1);
            $post['per_page'] = (int) $request->input('per_page', 10);
            if (empty($post['assignorderid'])) {
                throw new Exception('Order ID is required.');
            }
            // ── Validate token payload ─────────────────────────────────
            if (empty($post['userid']) || empty($post['orgid'])) {
                return new JsonResponse([
                    'type'    => 'error',
                    'message' => 'Invalid token payload.',
                    'result'  => [],
                ], 401);
            }

            $data = AssignDriver::getOrderListApi($post);

            $page     = $post['page'];
            $perPage  = $post['per_page'];
            $total    = $data['total'];
            $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

            // ── No results ─────────────────────────────────────────────
            if ($total === 0) {
                return new JsonResponse([
                    'type'    => 'error',
                    'message' => 'No results found.',
                    'result'  => [
                        'data'       => [],
                        'pagination' => [
                            'current_page' => (int) $page,
                            'next_page'    => null,
                            'prev_page'    => null,
                            'last_page'    => 1,
                            'per_page'     => (int) $perPage,
                            'total'        => 0,
                            'has_more'     => false,
                        ],
                    ],
                ], 200);
            }

            // ── Success with pagination ────────────────────────────────
            return new JsonResponse([
                'type'    => 'success',
                'message' => 'Orders fetched successfully',
                'result'  => [
                    'data'       => $data['list'],
                    'pagination' => [
                        'current_page' => $page,
                        'next_page'    => $page < $lastPage ? $page + 1 : null,
                        'prev_page'    => $page > 1         ? $page - 1 : null,
                        'last_page'    => $lastPage,
                        'per_page'     => $perPage,
                        'total'        => $total,
                        'has_more'     => $page < $lastPage,
                    ],
                ],
            ], 200);
        } catch (QueryException $e) {
            return new JsonResponse([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => [],
            ], 500);
        } catch (\Exception $e) {
            return new JsonResponse([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => [],
            ], 500);
        }
    }

    public static function getOrderListDatewise(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post             = $request->all();
            $post['type'] = 'datewise';
            $post['userid']   = $profile['userid'] ?? null;
            $post['orgid']    = $profile['orgid']  ?? null;
            $post['page']     = (int) $request->input('page', 1);
            $post['per_page'] = (int) $request->input('per_page', 10);

            // ── Validate token payload ─────────────────────────────────
            if (empty($post['userid']) || empty($post['orgid'])) {
                return new JsonResponse([
                    'type'    => 'error',
                    'message' => 'Invalid token payload.',
                    'result'  => [],
                ], 401);
            }

            $data = AssignDriver::getOrderListApi($post);

            $page     = $post['page'];
            $perPage  = $post['per_page'];
            $total    = $data['total'];
            $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

            // ── No results ─────────────────────────────────────────────
            if ($total === 0) {
                return new JsonResponse([
                    'type'    => 'error',
                    'message' => 'No results found.',
                    'result'  => [
                        'data'       => [],
                        'pagination' => [
                            'current_page' => (int) $page,
                            'next_page'    => null,
                            'prev_page'    => null,
                            'last_page'    => 1,
                            'per_page'     => (int) $perPage,
                            'total'        => 0,
                            'has_more'     => false,
                        ],
                    ],
                ], 200);
            }

            // ── Success with pagination ────────────────────────────────
            return new JsonResponse([
                'type'    => 'success',
                'message' => 'Orders fetched successfully',
                'result'  => [
                    'data'       => $data['list'],
                    'pagination' => [
                        'current_page' => $page,
                        'next_page'    => $page < $lastPage ? $page + 1 : null,
                        'prev_page'    => $page > 1         ? $page - 1 : null,
                        'last_page'    => $lastPage,
                        'per_page'     => $perPage,
                        'total'        => $total,
                        'has_more'     => $page < $lastPage,
                    ],
                ],
            ], 200);
        } catch (QueryException $e) {
            return new JsonResponse([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => [],
            ], 500);
        } catch (\Exception $e) {
            return new JsonResponse([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => [],
            ], 500);
        }
    }
    public static function getOrderListAll(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post             = $request->all();
            $post['userid']   = $profile['userid'] ?? null;
            $post['orgid']    = $profile['orgid']  ?? null;
            $post['page']     = (int) $request->input('page', 1);
            $post['per_page'] = (int) $request->input('per_page', 10);
            $post['type'] = 'all';

            // ── Validate token payload ─────────────────────────────────
            if (empty($post['userid']) || empty($post['orgid'])) {
                return new JsonResponse([
                    'type'    => 'error',
                    'message' => 'Invalid token payload.',
                    'result'  => [],
                ], 401);
            }

            $data = AssignDriver::getOrderListApi($post);

            $page     = $post['page'];
            $perPage  = $post['per_page'];
            $total    = $data['total'];
            $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

            // ── No results ─────────────────────────────────────────────
            if ($total === 0) {
                return new JsonResponse([
                    'type'    => 'error',
                    'message' => 'No results found.',
                    'result'  => [
                        'data'       => [],
                        'pagination' => [
                            'current_page' => (int) $page,
                            'next_page'    => null,
                            'prev_page'    => null,
                            'last_page'    => 1,
                            'per_page'     => (int) $perPage,
                            'total'        => 0,
                            'has_more'     => false,
                        ],
                    ],
                ], 200);
            }

            // ── Success with pagination ────────────────────────────────
            return new JsonResponse([
                'type'    => 'success',
                'message' => 'Orders fetched successfully',
                'result'  => [
                    'data'       => $data['list'],
                    'pagination' => [
                        'current_page' => $page,
                        'next_page'    => $page < $lastPage ? $page + 1 : null,
                        'prev_page'    => $page > 1         ? $page - 1 : null,
                        'last_page'    => $lastPage,
                        'per_page'     => $perPage,
                        'total'        => $total,
                        'has_more'     => $page < $lastPage,
                    ],
                ],
            ], 200);
        } catch (QueryException $e) {
            return new JsonResponse([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => [],
            ], 500);
        } catch (\Exception $e) {
            return new JsonResponse([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => [],
            ], 500);
        }
    }

    public static function getOrderDetail(Request $request)
    {
        $type = 'success';
        $message = 'Orders fetched successfully';
        $data = [];

        // try {

        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');
        $post = $request->all();
        $post['userid'] = $profile['userid'] ?? null;
        $post['orgid'] = $profile['orgid'] ?? null;

        $data = AssignDriver::getOrderListApi($post);
        // } catch (QueryException $e) {

        //     $type = 'error';
        //     $message = $e->getMessage();
        // } catch (\Exception $e) {

        //     $type = 'error';
        //     $message = $e->getMessage();
        // }

        return response()->json([
            'type' => $type,
            'message' => $message,
            'data' => $data
        ], $type === 'success' ? 200 : 500);
    }
}
