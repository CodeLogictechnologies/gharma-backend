<?php

namespace App\Http\Controllers;

use App\Services\EsewaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EsewaPaymentController extends Controller
{
    public function __construct(private EsewaService $esewa) {}

    /**
     * POST /api/payments/esewa/initiate
     * Called by React Native app to start a payment
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1',
            'customer_id' => 'nullable|string',
            'remarks'     => 'nullable|string|max:255',
        ]);

        // Generate a unique transaction UUID
        $transactionUuid = 'txn-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));

        $result = $this->esewa->bookPayment([
            'amount'           => $validated['amount'],
            'transaction_uuid' => $transactionUuid,
            'callback_url'     => route('esewa.callback'),
            'redirect_url'     => config('services.esewa.redirect_url'),
            'properties'       => [
                'customer_id' => $validated['customer_id'] ?? 'GUEST',
                'remarks'     => $validated['remarks'] ?? 'Payment',
            ],
        ]);
        // dd($result['code']);
        // if (($result['code'] ?? '') !== 'IP-200') {
        if (($result['code'] ?? '') !== 'IP-201') {
            Log::error('eSewa booking failed', ['result' => $result]);
            return response()->json([
                'success' => false,
                'message' => $result['error_message'] ?? 'Payment initiation failed',
            ], 400);
        }

        // Persist the booking to your DB here if needed:
        // Payment::create([...])

        return response()->json([
            'success'         => true,
            'booking_id'      => $result['data']['booking_id'],
            // 'deeplink'        => $result['data']['deeplink'],
            'correlation_id'  => $result['data']['correlation_id'],
            'transaction_uuid' => $transactionUuid,
        ]);
    }

    /**
     * POST /api/payments/esewa/status
     * Called by React Native to poll or verify status
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id'     => 'required|string',
            'correlation_id' => 'required|string',
        ]);

        $result = $this->esewa->checkStatus(
            $validated['booking_id'],
            $validated['correlation_id']
        );
        // eSewa returns 'code' as integer 0 on failure, or string 'IP-200' on success
        // Also error key is 'errorMessage' not 'error_message'
        $code = $result['code'] ?? '';
        if ($code !== 'IP-200') {
            return response()->json([
                'success' => false,
                'message' => $result['errorMessage']    // ← camelCase
                    ?? $result['error_message']   // ← fallback
                    ?? 'Status check failed',
            ], 400);
        }

        return response()->json([
            'success'        => true,
            'status'         => $result['data']['status'],
            'booking_id'     => $result['data']['booking_id'],
            'correlation_id' => $result['data']['correlation_id'],
            'transaction_id' => $result['data']['transaction_id'] ?? null,
            'reference_code' => $result['data']['reference_code'] ?? null,
            'updated_at'     => $result['data']['updated_at'] ?? null,
        ]);
    }

    /**
     * POST /api/payments/esewa/cancel
     * Called by React Native when user cancels before opening eSewa
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|string',
        ]);

        $result = $this->esewa->cancelPayment($validated['booking_id']);

        if (($result['code'] ?? '') !== 'IP-210') {
            return response()->json([
                'success' => false,
                'message' => $result['error_message'] ?? 'Cancellation failed',
            ], 400);
        }

        return response()->json([
            'success'    => true,
            'status'     => $result['data']['status'],
            'booking_id' => $result['data']['booking_id'],
            'message'    => $result['message'],
        ]);
    }

    /**
     * POST /api/payments/esewa/callback  (named route: esewa.callback)
     * eSewa server calls this after payment completes
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('eSewa callback received', $payload);

        // 1. Verify the signature from eSewa
        if (!$this->esewa->verifySignature($payload)) {
            Log::warning('eSewa callback: invalid signature', $payload);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $status        = $payload['status'] ?? 'UNKNOWN';
        $correlationId = $payload['correlation_id'] ?? null;
        $referenceCode = $payload['reference_code'] ?? null;
        $amount        = $payload['amount'] ?? null;

        // 2. Update your DB order/payment record here
        // Payment::where('correlation_id', $correlationId)->update([
        //     'status'         => $status,
        //     'reference_code' => $referenceCode,
        //     'paid_at'        => $status === 'SUCCESS' ? now() : null,
        // ]);

        Log::info("eSewa payment {$status}", [
            'correlation_id' => $correlationId,
            'reference_code' => $referenceCode,
            'amount'         => $amount,
        ]);

        return response()->json(['message' => 'Callback received'], 200);
    }
}