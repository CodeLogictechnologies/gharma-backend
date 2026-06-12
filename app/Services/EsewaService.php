<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsewaService
{
    private string $productCode;
    private string $accessKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->productCode = config('esewa.product_code', 'INTENT');
        $this->accessKey   = config('esewa.access_key', 'LB0REg8HUSw3MTYrI1s6JTE8Kyc6JyAqJiA3MQ=='); // fallback
        $this->baseUrl     = config('esewa.base_url', 'https://rc-checkout.esewa.com.np/api/client/intent/payment');
    }
    // ---------------------------------------------------------------
    // Signature
    // ---------------------------------------------------------------

    public function generateSignature(array $fields, string $signedFieldNames): string
    {
        $parts = [];
        foreach (explode(',', $signedFieldNames) as $name) {
            $key    = trim($name);
            $parts[] = $key . '=' . ($fields[$key] ?? '');
        }
        $message = implode(',', $parts);
        return base64_encode(hash_hmac('sha256', $message, $this->accessKey, true));
    }

    public function verifySignature(array $payload): bool
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
    // 1. Book / Initialize Payment
    // ---------------------------------------------------------------

    public function bookPayment(
        float  $amount,
        string $transactionUuid,
        string $callbackUrl,
        string $redirectUrl = '',
        array  $properties  = [],
        string $signature   = ''   // add this
    ): array {
        $signedFieldNames = 'product_code,amount,transaction_uuid';

        // Use passed signature or generate one
        if (empty($signature)) {
            $fields    = [
                'product_code'     => $this->productCode,
                'amount'           => $amount,
                'transaction_uuid' => $transactionUuid,
            ];
            $signature = $this->generateSignature($fields, $signedFieldNames);
        }

        $payload = [
            'product_code'       => $this->productCode,
            'amount'             => $amount,
            'transaction_uuid'   => $transactionUuid,
            'signed_field_names' => $signedFieldNames,
            'signature'          => $signature,
            'callback_url'       => $callbackUrl,
            'redirect_url'       => $redirectUrl,
            'properties'         => $properties,
        ];
    }
    // ---------------------------------------------------------------
    // 2. Status Check
    // ---------------------------------------------------------------

    public function checkStatus(string $bookingId, string $correlationId): array
    {
        $signedFieldNames = 'booking_id,product_code,correlation_id';

        $fields = [
            'booking_id'     => $bookingId,
            'product_code'   => $this->productCode,
            'correlation_id' => $correlationId,
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

            if ($response->successful() && ($data['code'] ?? '') === 'IP-200') {
                $d = $data['data'] ?? [];
                return [
                    'success'        => true,
                    'status'         => $d['status']         ?? 'UNKNOWN',
                    'booking_id'     => $d['booking_id']     ?? null,
                    'correlation_id' => $d['correlation_id'] ?? null,
                    'transaction_id' => $d['transaction_id'] ?? null,
                    'reference_code' => $d['reference_code'] ?? null,
                    'updated_at'     => $d['updated_at']     ?? null,
                    'message'        => $data['message']     ?? '',
                ];
            }

            return [
                'success' => false,
                'code'    => $data['code']          ?? null,
                'error'   => $data['error_message'] ?? 'Status check failed',
            ];
        } catch (\Exception $e) {
            Log::error('eSewa checkStatus error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // 3. Cancel Payment
    // ---------------------------------------------------------------

    public function cancelPayment(string $bookingId): array
    {
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

            if (($data['code'] ?? '') === 'IP-210') {
                $d = $data['data'] ?? [];
                return [
                    'success'        => true,
                    'status'         => $d['status']         ?? 'CANCELED',
                    'booking_id'     => $d['booking_id']     ?? null,
                    'correlation_id' => $d['correlation_id'] ?? null,
                    'transaction_id' => $d['transaction_id'] ?? null,
                    'message'        => $data['message']     ?? 'Cancelled',
                ];
            }

            return [
                'success' => false,
                'code'    => $data['code']          ?? null,
                'error'   => $data['error_message'] ?? 'Cancel failed',
            ];
        } catch (\Exception $e) {
            Log::error('eSewa cancelPayment error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
