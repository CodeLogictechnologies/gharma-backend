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
use Illuminate\Support\Facades\DB;

use Tymon\JWTAuth\Facades\JWTAuth;

class AssignDriverController extends Controller
{
    public static function getOrderList(Request $request)
    {
        $type = 'success';
        $message = 'Orders fetched successfully';
        $data = [];

        try {

            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');
            $post['userid'] = $profile['userid'] ?? null;
            $post['orgid'] = $profile['orgid'] ?? null;



            $data = AssignDriver::getOrderListApi($post);
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
