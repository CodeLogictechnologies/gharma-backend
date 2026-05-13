<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserAddressRequest;
use App\Models\API\UserAddress;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;

class UserAddressMasterController extends Controller
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
}