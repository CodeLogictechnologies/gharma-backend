<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Store;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;

class StoreController extends Controller
{
    public function list(Request $request)
{
    try {
        $data = Store::getList();

        return response()->json([
            'type'    => 'success',
            'message' => 'Stores fetched successfully.',
            'data'    => $data,
        ], 200);

    } catch (QueryException $e) {
        return response()->json([
            'type'    => 'error',
            'message' => 'Database error. Please try again.',
        ], 500);
    } catch (Exception $e) {
        return response()->json([
            'type'    => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

// detail() stays the same 
public function detail(Request $request)
{
    try {
        if (empty($request->id)) {
            throw new Exception('Store ID is required.');
        }

        $post = $request->all();
        $data = Store::getDetail($post);

        return response()->json([
            'type'    => 'success',
            'message' => 'Store fetched successfully.',
            'data'    => $data,
        ], 200);

    } catch (QueryException $e) {
        return response()->json([
            'type'    => 'error',
            'message' => 'Something went wrong. Please try again.',
        ], 500);
    } catch (Exception $e) {
        return response()->json([
            'type'    => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}
}