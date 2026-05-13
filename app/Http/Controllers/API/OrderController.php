<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\OrderPlaceRequest;
use App\Models\API\Order as APIOrder;
use App\Models\API\OrderMaster;
use App\Models\BackPanel\Order as BackPanelOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class OrderController extends Controller
{
    public function save(OrderPlaceRequest $request)
    {
        // try {
            $type = 'success';
            $message = 'Order place successfully';

            DB::beginTransaction();
            $post = $request->all();
            if (!APIOrder::saveData($post)) {
                throw new Exception('Could not place order', 1);
        }
            DB::commit();
            return response()->json([
                'type'    => $type,
                'message' => $message
            ], 200);
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     return response()->json([
        //         'type'    => 'error',
        //         'message' => 'Something went wrong'
        //     ], 500);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => $e->getMessage(),
        //     ], 500);
        // }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function orderStatus(Request $request)
    {
        try {
            $getData = null;
            $post = $request->all();

            $payload = JWTAuth::parseToken()->getPayload();

            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];
            $getData = OrderMaster::getOrderStatus($post);
            $type = 'success';
            $message = 'Successfully fetched status of customer order.';
        } catch (QueryException $e) {
            $type = 'error';
            $message = 'Somthing went wrong.';
        } catch (Exception $e) {
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode([
            'type' => $type,
            'message' => $message,
            'status' => $getData->order_status
        ]);
    }
}