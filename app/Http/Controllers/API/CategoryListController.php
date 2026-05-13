<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AddToCartRequest;
use App\Http\Requests\API\FavouriteDeleteRequest;
use App\Http\Requests\API\FavouriteSaveRequest;
use App\Http\Requests\API\OrderPlaceRequest;
use App\Models\API\Cart;
use App\Models\API\CategoryList;
use App\Models\API\Favourite;
use App\Models\API\Order as APIOrder;
use App\Models\BackPanel\Order as BackPanelOrder;
use App\Models\Cart\API;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;


class CategoryListController extends Controller
{
    public function getCategoryList(Request $request)
    {
        try {
            $type = 'success';
            $message = 'Categories fetch successfully.';

            DB::beginTransaction();
            $payload = JWTAuth::parseToken()->getPayload();

            $post = $request->all();
            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];
            $data = CategoryList::getListData($post);
            if (!$data) {
                throw new Exception('Product not found.', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            $type = 'error';
            $message = 'Something went wrong';
        } catch (Exception $e) {
            $type = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message, 'categories' => $data]);
    }
}