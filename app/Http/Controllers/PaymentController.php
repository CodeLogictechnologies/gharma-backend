<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        // try {
        $user = auth('api')->user();

        // if (!$user) {
        //     throw new Exception("Unauthorized user");
        // }

        // Fix 1: json_decode after base64_decode, true = array
        $decoded = json_decode(base64_decode($request->data), true);

        if (!$decoded) {
            throw new Exception("Invalid payment data");
        }

        $post = [];
        $post['userid']            = "a34d90cb-b31a-40a4-bd4b-badf47851cfa";
        $post['transaction_code'] = $decoded['transaction_code'];
        $post['status']           = $decoded['status'];
        $post['total_amount']     = $decoded['total_amount'];
        $post['transaction_uuid'] = $decoded['transaction_uuid'];
        $post['method'] = 'ESWEA';
        // $post['product_code']     = $decoded['product_code'];
        // $post['signed_field_names'] = $decoded['signed_field_names'];
        // $post['signature']        = $decoded['signature'];

        $result = Payment::savePaymentEsewa($post);

        if (!$result) {
            throw new Exception("Payment Fail");
        }
        return view('backend.organization.index2');

        // return response()->json([
        //     'type'    => 'success',
        //     'message' => 'Payment saved successfully',
        //     // ]);
        // } catch (QueryException $e) {
        //     return response()->json([
        //         'type'    => 'error',
        //         'message' => 'Something went wrong'
        //     ], 500);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'type'    => 'error',
        //         'message' => $e->getMessage()
        //     ], 400);
        // }
    }

    public function successEsewa(Request $request)
    {
        try {
            $user = auth('api')->user();

            if (!$user) {
                throw new Exception("Unauthorized user");
            }
            $decoded = json_decode(base64_decode($request->data), true);

            if (!$decoded) {
                throw new Exception("Invalid payment data");
            }

            $post = [];
            $post['userid']            = $user->id;
            $post['transaction_code'] = $decoded['transaction_code'];
            $post['status']           = $decoded['status'];
            $post['total_amount']     = $decoded['total_amount'];
            $post['transaction_uuid'] = $decoded['transaction_uuid'];
            $post['method'] = 'ESWEA';
            // $post['product_code']     = $decoded['product_code'];
            // $post['signed_field_names'] = $decoded['signed_field_names'];
            // $post['signature']        = $decoded['signature'];

            $result = Payment::savePaymentEsewa($post);

            if (!$result) {
                throw new Exception("Payment Fail");
            }

            return response()->json([
                'type'    => 'success',
                'message' => 'Payment saved successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong'
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function failure(Request $request)
    {
        return response()->json([
            'type'    => 'error',
            'message' => 'Payment failed',
        ]);
    }

    public function index()
    {
        return view('backend.organization.index');
    }

    // Step 1: Initiate Payment
    public function initiate(Request $request)
    {
        $purchase_id = 'ORD-' . Str::random(6);

        $payload = [
            "return_url" => route('payment.callback'),
            "website_url" => url('/'),
            "amount" => $request->amount,
            "purchase_order_id" => $purchase_id,
            "purchase_order_name" => "Test Product",
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . env('KHALTI_SECRET_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://dev.khalti.com/api/v2/epayment/initiate/', $payload);
        $data = $response->json();
        // $data['payment_url'] = 'https://dev.khalti.com/api/v2/';
        if (isset($data['payment_url'])) {
            return redirect()->away($data['payment_url']);
        }
        return back()->with('error', 'Payment initiation failed');
    }

    // Step 2: Callback (return_url)
    public function callback(Request $request)
    {
        $pidx = $request->pidx;

        if (!$pidx) {
            return "Invalid Payment";
        }

        // Step 3: Verify Payment (Lookup API)
        $response = Http::withHeaders([
            'Authorization' => 'Key live_secret_key_xxxxxxxxx',
            'Content-Type' => 'application/json',
        ])->post('https://dev.khalti.com/api/v2/epayment/lookup/', [
            "pidx" => $pidx
        ]);

        $data = $response->json();

        // Step 4: Handle Status
        if ($data['status'] == 'Completed') {

            // ✅ SUCCESS
            // Save to DB (optional)
            /*
            Payment::create([
                'pidx' => $data['pidx'],
                'transaction_id' => $data['transaction_id'],
                'amount' => $data['total_amount'],
                'status' => 'Completed'
            ]);
            */

            return "Payment Successful ✅";
        } elseif ($data['status'] == 'Pending') {

            return "Payment Pending ⏳";
        } else {

            return "Payment Failed ❌ (" . $data['status'] . ")";
        }
    }
}
