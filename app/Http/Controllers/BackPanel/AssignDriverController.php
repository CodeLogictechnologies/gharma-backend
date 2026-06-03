<?php


namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\API\GetLoactionDataRequest;
use App\Http\Requests\API\UserAddressRequest;
use App\Models\API\UserAddress;
use App\Models\BackPanel\AssignDriver;
use App\Models\OrderNotificationOtp;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\FirebaseService;

use Tymon\JWTAuth\Facades\JWTAuth;

class AssignDriverController extends Controller
{
    public  function getOrderList(Request $request)
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

    public  function getOrderListDatewise(Request $request)
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
    public  function getOrderListAll(Request $request, $type)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post             = $request->all();
            $post['userid']   = $profile['userid'] ?? null;
            $post['orgid']    = $profile['orgid']  ?? null;
            $post['page']     = (int) $request->input('page', 1);
            $post['per_page'] = (int) $request->input('per_page', 10);
            $post['type'] = $type;
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

    public  function getOrderDetail(Request $request)
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

    public function getCustomerDetail(Request $request)
    {
        $type = 'success';
        $message = 'Customer detail fetched successfully';
        $data = [];

        try {

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');
            $post = $request->all();
            $post['userid'] = $profile['userid'] ?? null;
            $post['orgid'] = $profile['orgid'] ?? null;

            $data = AssignDriver::getCustomerDetail($post);
        } catch (QueryException $e) {

            $type = 'error';
            $message = $e->getMessage();
        } catch (\Exception $e) {

            $type = 'error';
            $message = $e->getMessage();
        }

        return response()->json([
            'type' => $type,
            'message' => $message,
            'data' => $data
        ], $type === 'success' ? 200 : 500);
    }

    public function changeOrderStatus(Request $request, FirebaseService $firebase)
    {
        $type    = 'error';
        $message = 'Something went wrong.';
        $data = [];
        // try {
        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');

        $post = $request->all();
        $post['userid'] = $profile['userid'] ?? null;
        $post['orgid']  = $profile['orgid'] ?? null;

        if (empty($post['assignorderid'])) {
            return response()->json([
                'type' => 'error',
                'message' => 'Order ID is required.',
            ], 422);
        }

        if (empty($post['status'])) {
            return response()->json([
                'type' => 'error',
                'message' => 'Status is required.',
            ], 422);
        }
        if ($post['status'] === 'Start') {
            if (empty($post['latitude'])) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Latitude is required.',
                ], 422);
            }
            if (empty($post['longitude'])) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Longitude is required.',
                ], 422);
            }
        }
        $data = AssignDriver::changeOrderStatus($post, $firebase);

        if ($data) {
            $type = 'success';
            $message = 'Order status updated successfully.';
        } else {
            $type = 'error';
            $message = 'Order not found or already updated.';
        }
        // } catch (TokenExpiredException $e) {
        //     return response()->json([
        //         'type' => 'error',
        //         'message' => 'Token expired. Please login again.',
        //         'location' => $data
        //     ], 401);
        // } catch (JWTException $e) {
        //     return response()->json([
        //         'type' => 'error',
        //         'message' => 'Invalid token.',
        //         'location' => $data
        //     ], 401);
        // } catch (QueryException $e) {
        //     return response()->json([
        //         'type' => 'error',
        //         'message' => 'Something went wrong.',
        //         'location' => $data
        //     ], 500);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'type' => 'error',
        //         'message' => $e->getMessage(),
        //         'location' => $data
        //     ], 500);
        // }

        return response()->json([
            'type' => $type,
            'message' => $message,
            'location' => $data
        ], $type === 'success' ? 200 : 400);
    }

    public function verifyOtp(Request $request)
    {
        try {

            $post = $request->all();

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post['orgid']  = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            DB::beginTransaction();

            if (!OrderNotificationOtp::verifyOrderOtp($post)) {
                throw new Exception('OTP does not match or verification failed.');
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'type'    => 'success',
                'message' => 'OTP matched successfully'
            ], 200);
        } catch (QueryException $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => $this->queryMessage ?? 'Database error',
                'error'   => $e->getMessage()
            ], 500);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}