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

            try {
                $payload = JWTAuth::parseToken()->getPayload();
                $profile = $payload->get('profile');
                $orgid   = $profile['orgid'] ?? null;
            } catch (\Exception $e) {
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

    public function getSubCategoryList(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');

            $query = DB::table('sub_categories as s')
                ->select(
                    's.id',
                    's.title',
                    's.slug',
                    's.status',
                    DB::raw("CONCAT('" . url('storage/subcategories') . "/', s.image) as image")
                )
                ->where('s.status', 'Y');

            if (!empty($categoryId)) {
                $query->join('sub_category_items as sci', 'sci.subcategoryid', '=', 's.id')
                    ->join('items as i', 'i.id', '=', 'sci.itemid')
                    ->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                    ->where('ci.categoryid', $categoryId)
                    ->distinct();
            }

            $data = $query->orderBy('s.created_at', 'asc')->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'No subcategories found.',
                    'result'  => []
                ]);
            }

            return response()->json([
                'type'    => 'success',
                'message' => 'Subcategories fetched successfully.',
                'result'  => $data
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong.',
                'result'  => []
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
                'result'  => []
            ], 400);
        }
    }
}
