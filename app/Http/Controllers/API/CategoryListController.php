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
        $type    = 'success';
        $message = 'Categories fetched successfully.';
        $data    = collect();
        $orgid   = null;

        // Try to get orgid from JWT token if logged in
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');
            $orgid   = $profile['orgid'] ?? null;
        } catch (\Exception $e) {
            // Not logged in — try from query param
            $orgid = $request->orgid ?? null;
        }

        $post['orgid'] = $orgid;

        $data = CategoryList::getListData($post);

        if ($data->isEmpty()) {
            throw new Exception('No categories found.', 1);
        }

    } catch (QueryException $e) {
        $type    = 'error';
        $message = 'Something went wrong';
        $data    = [];
    } catch (Exception $e) {
        $type    = 'error';
        $message = $e->getMessage();
        $data    = [];
    }

    return response()->json([
        'type'       => $type,
        'message'    => $message,
        'categories' => $data,
    ]);
}
}