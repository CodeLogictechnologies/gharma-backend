<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function saveEsewaTransaction(TransactionRequest $request)
    {
        try {

        // $user = auth('api')->user();
        // if (!$user) {
        //     throw new Exception("Unauthorized user");
        // }

        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');
        $post = $request->all();
        $post['userid'] = $profile['userid'];
        $post['orgid'] = $profile['orgid'];

        $result = Transaction::saveTransaction($post);

        if (!$result) {
            throw new Exception("Transaction Fail");
        }

        $totalAmount = $post['total_amount'] ?? 100;
        $txnCode = $result;
        $productCode = $post['product_code'] ?? 'EPAYTEST';

        $secret = "8gBm/:&EnhH.1/q";
        $string = "total_amount={$totalAmount},transaction_uuid={$txnCode},product_code={$productCode}";

        $signature = base64_encode(
            hash_hmac('sha256', $string, $secret, true)
        );

        return response()->json([
            'type' => 'success',
            'message' => 'Transaction save successfully',
            'txncode' => $txnCode,
            'signature' => $signature,
        ]);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong.'
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function saveKhaltiTransaction(TransactionRequest $request)
    {
        try {

            $user = auth('api')->user();

            if (!$user) {
                throw new Exception("Unauthorized user");
            }

            $post = $request->all();
            $post['userid'] = $user->id;
            $post['orgid'] = $user->orgid;

            $result = Transaction::saveTransaction($post);

            if (!$result) {
                throw new Exception("Transaction Fail");
            }

            // 🔥 USE REAL VALUES FROM REQUEST/DB
            $totalAmount = $post['total_amount'] ?? 100;
            $txnCode = $result; // OR $post['txncode'] if you generate it
            $productCode = $post['product_code'] ?? 'EPAYTEST';

            $secret = "8gBm/:&EnhH.1/q"; // sandbox key

            // 🔥 MUST MATCH EXACT ORDER
            $string = "total_amount={$totalAmount},transaction_uuid={$txnCode},product_code={$productCode}";

            $signature = base64_encode(
                hash_hmac('sha256', $string, $secret, true)
            );

            return response()->json([
                'type' => 'success',
                'message' => 'Transaction save successfully',
                'txncode' => $txnCode,
                'signature' => $signature,
            ]);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong'
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}