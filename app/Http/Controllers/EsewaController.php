<?php

namespace App\Http\Controllers;

use App\Models\Esewa;
use App\Models\EsewaTransaction;
use App\Services\EsewaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EsewaController extends Controller
{
    private string $productCode;
    private string $accessKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->productCode = config('esewa.product_code', 'INTENT');
        $this->accessKey   = config('esewa.access_key', 'LB0REg8HUSw3MTYrI1s6JTE8Kyc6JyAqJiA3MQ==');
        $this->baseUrl     = config('esewa.base_url', 'https://rc-checkout.esewa.com.np/api/client/intent/payment');
    }

    // ---------------------------------------------------------------
    // Signature Helper
    // ---------------------------------------------------------------

    private function generateSignature(array $fields, string $signedFieldNames): string
    {
        $parts = [];
        foreach (explode(',', $signedFieldNames) as $name) {
            $key     = trim($name);
            $parts[] = $key . '=' . ($fields[$key] ?? '');
        }
        $message = implode(',', $parts);
        return base64_encode(hash_hmac('sha256', $message, $this->accessKey, true));
    }

    private function verifySignature(array $payload): bool
    {
        $signedFieldNames = $payload['signed_field_names'] ?? '';
        $receivedSig      = $payload['signature']          ?? '';

        if (empty($signedFieldNames) || empty($receivedSig)) {
            return false;
        }

        $expectedSig = $this->generateSignature($payload, $signedFieldNames);
        return hash_equals($expectedSig, $receivedSig);
    }

    // ---------------------------------------------------------------
    // 1. POST /api/esewa/initiate
    // ---------------------------------------------------------------

    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'customer_id' => 'nullable|string',
            'remarks'     => 'nullable|string',
        ]);

        $transactionUuid  = 'txn-' . now()->format('Ymd') . '-' . Str::uuid();
        $amount           = (float) $request->amount;
        $signedFieldNames = 'product_code,amount,transaction_uuid';

        $fields = [
            'product_code'     => $this->productCode,
            'amount'           => $amount,
            'transaction_uuid' => $transactionUuid,
        ];

        $signature = $this->generateSignature($fields, $signedFieldNames);

        $payload = [
            'product_code'       => $this->productCode,
            'amount'             => $amount,
            'transaction_uuid'   => $transactionUuid,
            'signed_field_names' => $signedFieldNames,
            'signature'          => $signature,
            'callback_url'       => route('esewa.callback'),
            'redirect_url'       => config('esewa.redirect_url', 'http://127.0.0.1:8000/payment/complete'),
            'properties'         => [
                'customer_id' => $request->customer_id ?? '',
                'remarks'     => $request->remarks     ?? '',
            ],
        ];

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($this->baseUrl . '/book', $payload);

            $data = $response->json();

            if (!in_array($data['code'] ?? '', ['IP-200', 'IP-201'])) {
                return response()->json([
                    'success' => false,
                    'message' => $data['error_message'] ?? 'Booking failed',
                    'code'    => $data['code'] ?? null,
                ], 422);
            }

            // Save to DB
            Esewa::create([
                'transaction_uuid' => $transactionUuid,
                'booking_id'       => $data['data']['booking_id']    ?? null,
                'correlation_id'   => $data['data']['correlation_id'] ?? null,
                'deeplink'         => $data['data']['deeplink']        ?? null,
                'amount'           => $amount,
                'status'           => Esewa::STATUS_BOOKED,
                'callback_url'     => route('esewa.callback'),
                'redirect_url'     => config('esewa.redirect_url'),
                'properties'       => [
                    'customer_id' => $request->customer_id ?? '',
                    'remarks'     => $request->remarks     ?? '',
                ],
            ]);

            return response()->json([
                'success'          => true,
                'message'          => 'Payment booked successfully',
                'transaction_uuid' => $transactionUuid,
                'signature' => $signature,
                'booking_id'       => $data['data']['booking_id']     ?? null,
                'deeplink'         => $data['data']['deeplink']        ?? null,
                'correlation_id'   => $data['data']['correlation_id']  ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa initiate error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ---------------------------------------------------------------
    // 2. GET /api/esewa/status/{bookingId}
    // ---------------------------------------------------------------

    public function status(string $bookingId): JsonResponse
    {
        $transaction = Esewa::where('booking_id', $bookingId)->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        $signedFieldNames = 'booking_id,product_code,correlation_id';

        $fields = [
            'booking_id'     => $bookingId,
            'product_code'   => $this->productCode,
            'correlation_id' => $transaction->correlation_id,
        ];

        $payload = array_merge($fields, [
            'signed_field_names' => $signedFieldNames,
            'signature'          => $this->generateSignature($fields, $signedFieldNames),
        ]);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($this->baseUrl . '/status', $payload);

            $data = $response->json();

            if (($data['code'] ?? '') !== 'IP-200') {
                return response()->json([
                    'success' => false,
                    'message' => $data['error_message'] ?? 'Status check failed',
                ], 422);
            }

            $d = $data['data'] ?? [];

            // Sync status in DB
            $updateData = ['status' => $d['status'] ?? $transaction->status];

            if (($d['status'] ?? '') === Esewa::STATUS_SUCCESS) {
                $updateData['reference_code']       = $d['reference_code']  ?? null;
                $updateData['esewa_transaction_id'] = $d['transaction_id']  ?? null;
                $updateData['paid_at']              = now();
            }

            $transaction->update($updateData);

            return response()->json([
                'success'        => true,
                'status'         => $d['status']         ?? null,
                'booking_id'     => $d['booking_id']     ?? null,
                'reference_code' => $d['reference_code'] ?? null,
                'transaction_id' => $d['transaction_id'] ?? null,
                'updated_at'     => $d['updated_at']     ?? null,
                'message'        => $data['message']     ?? '',
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa status error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ---------------------------------------------------------------
    // 3. POST /api/esewa/cancel/{bookingId}
    // ---------------------------------------------------------------

    public function cancel(string $bookingId): JsonResponse
    {
        $transaction = Esewa::where('booking_id', $bookingId)->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if (!in_array($transaction->status, [Esewa::STATUS_BOOKED, Esewa::STATUS_PENDING])) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction cannot be cancelled. Current status: ' . $transaction->status,
            ], 422);
        }

        $signedFieldNames = 'booking_id,product_code';

        $fields = [
            'booking_id'   => $bookingId,
            'product_code' => $this->productCode,
        ];

        $payload = array_merge($fields, [
            'signed_field_names' => $signedFieldNames,
            'signature'          => $this->generateSignature($fields, $signedFieldNames),
        ]);

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($this->baseUrl . '/cancel', $payload);

            $data = $response->json();

            if (($data['code'] ?? '') !== 'IP-210') {
                return response()->json([
                    'success' => false,
                    'message' => $data['error_message'] ?? 'Cancel failed',
                ], 422);
            }

            $transaction->update(['status' => Esewa::STATUS_CANCELED]);

            return response()->json([
                'success'        => true,
                'message'        => $data['message']              ?? 'Cancelled',
                'status'         => $data['data']['status']        ?? null,
                'booking_id'     => $data['data']['booking_id']    ?? null,
                'correlation_id' => $data['data']['correlation_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa cancel error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ---------------------------------------------------------------
    // 4. POST /api/esewa/callback  (called by eSewa server)
    // ---------------------------------------------------------------

    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('eSewa callback received', $payload);

        if (!$this->verifySignature($payload)) {
            Log::warning('eSewa callback: invalid signature', $payload);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        $transaction = Esewa::where('correlation_id', $payload['correlation_id'] ?? null)->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        $status     = $payload['status'] ?? null;
        $updateData = [
            'status'         => $status,
            'esewa_response' => $payload,
        ];

        if ($status === Esewa::STATUS_SUCCESS) {
            $updateData['reference_code'] = $payload['reference_code'] ?? null;
            $updateData['paid_at']        = now();
        }

        $transaction->update($updateData);

        Log::info('eSewa callback processed', ['booking_id' => $transaction->booking_id, 'status' => $status]);

        return response()->json(['success' => true, 'message' => 'Callback processed']);
    }

    // ---------------------------------------------------------------
    // 5. GET /api/esewa/transactions
    // ---------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        $transactions = Esewa::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    // ---------------------------------------------------------------
    // 6. GET /api/esewa/transactions/{id}
    // ---------------------------------------------------------------

    public function show(Esewa $esewa): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $esewa]);
    }
}
