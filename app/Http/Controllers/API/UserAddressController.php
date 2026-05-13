<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\GetLoactionDataRequest;
use App\Http\Requests\API\UserAddressRequest;
use App\Models\API\UserAddress;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;

use Tymon\JWTAuth\Facades\JWTAuth;

class UserAddressController extends Controller
{
    public function save(UserAddressRequest $request)
    {
        try {
            $type = 'success';
            $message = 'Address save successfully';

            $post = $request->all();
            if (empty($post['orgid'])) {
                throw new Exception('Could not address', 1);
            }
            DB::beginTransaction();
            if (!UserAddress::saveData($post)) {
                throw new Exception('Could not address', 1);
            }
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

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function updateAddress(UserAddressRequest $request)
    {
        try {
            $type = 'success';
            $message = 'Address updatde successfully';

            $post = $request->all();
            if (empty($post['orgid'])) {
                throw new Exception('Could not address', 1);
            }
            DB::beginTransaction();
            if (!UserAddress::updateData($post)) {
                throw new Exception('Could not address', 1);
            }
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

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function fetchAddress(Request $request)
    {
        try {
            $type = 'success';
            $message = 'Address fetch successfully';

            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();

            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            $result = UserAddress::getAllAddress($post);
        } catch (QueryException $e) {
            $type = 'error';
            $message = $e->getMessage();
        } catch (Exception $e) {
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message, 'address' => $result]);
    }

    public function updateAddressActive(Request $request)
    {
        try {
            $type = 'success';
            $message = 'Address updatded successfully';

            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            DB::beginTransaction();
            if (!UserAddress::updateDataActive($post)) {
                throw new Exception('Could not update active address', 1);
            }
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

        return json_encode(['type' => $type, 'message' => $message]);
    }

    public function getLocation(GetLoactionDataRequest $request)
    {
        try {
            $result = null;
            $type = 'success';
            $message = 'Address fetch successfully';

            $post = $request->all();
            $customerid = $request->route('customerid');
            $post['customerid'] = $customerid;

            if (empty($post['customerid'])) {
                throw new Exception('Customer ID is required', 1);
            }
            $result = UserAddress::getAddress($post);
        } catch (QueryException $e) {
            $type = 'error';
            $message = $e->getMessage();
        } catch (Exception $e) {
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message, 'location' => $result]);
    }
}
