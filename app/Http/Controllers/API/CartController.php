<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AddToCartRequest;
use App\Models\API\Cart;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;


class CartController extends Controller
{
    public function saveAddToCart(AddToCartRequest $request)
    {
        try {
            DB::beginTransaction();

            $post = $request->all();

            if (!Cart::saveData($post)) {
                throw new \Exception('Could not add product to cart');
            }

            DB::commit();

            return response()->json([
                'type'    => 'success',
                'message' => 'Product added to cart'
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

    public function getList(Request $request)
    {
        try {
            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            $getData = Cart::getData($post);

            if (!$getData || $getData->isEmpty()) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'No product available in cart.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Cart data fetched successfully.',
                'data' => $getData
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong',
                'data' => []
            ], 500);
        } catch (\Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function deleteCart(Request $request, $variationid)
    {
        try {
            DB::beginTransaction();
            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();

            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];
            $post['variationid'] = $variationid;
            if (empty($post['variationid'])) {
                throw new \Exception('Variation id is required.');
            }
            if (!Cart::deleteCart($post)) {
                throw new \Exception('Could not delete product from cart.');
            }

            DB::commit();

            return response()->json([
                'type'    => 'success',
                'message' => 'Product removed from cart'
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

    public function removeCart(Request $request, $variationid)
    {
        try {
            DB::beginTransaction();
            $post = $request->all();
            $payload = JWTAuth::parseToken()->getPayload();

            $profile = $payload->get('profile');
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];
            $post['variationid'] = $variationid;
            if (empty($post['variationid'])) {
                throw new \Exception('Variation id is required.');
            }
            if (!Cart::removeCart($post)) {
                throw new \Exception('Could not delete product from cart.');
            }

            DB::commit();

            return response()->json([
                'type'    => 'success',
                'message' => 'Product removed from cart'
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
