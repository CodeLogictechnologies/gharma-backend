<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EsewaService
{
    private string $baseUrl;
    private string $productCode;
    private string $accessKey;

    public function __construct()
    {
        $this->baseUrl    = 'https://rc-checkout.esewa.com.np/api/client/intent/payment';
        $this->productCode = 'INTENT';
        $this->accessKey  = 'LB0REg8HUSw3MTYrI1s6JTE8Kyc6JyAqJiA3MQ==';
    }

    /**
     * Generate HMAC-SHA256 signature in Base64
     */
    public function generateSignature(string $message): string
    {
        $hash = hash_hmac('sha256', $message, $this->accessKey, true);
        return base64_encode($hash);
    }

    /**
     * Build the signature message from field values
     */
    public function buildSignatureMessage(array $fields, array $data): string
    {
        $parts = [];
        foreach ($fields as $field) {
            $parts[] = "{$field}={$data[$field]}";
        }
        return implode(',', $parts);
    }

    /**
     * Verify an incoming signature (e.g. from callback)
     */
    public function verifySignature(array $payload): bool
    {
        $signedFields = explode(',', $payload['signed_field_names']);
        $message      = $this->buildSignatureMessage($signedFields, $payload);
        $expected     = $this->generateSignature($message);
        return hash_equals($expected, $payload['signature']);
    }

    /**
     * Initialize / Book a payment
     */
    public function bookPayment(array $params): array
    {
        $signedFields = ['product_code', 'amount', 'transaction_uuid'];
        $data = [
            'product_code'       => $this->productCode,
            'amount'             => $params['amount'],
            'transaction_uuid'   => $params['transaction_uuid'],
        ];
        $message   = $this->buildSignatureMessage($signedFields, $data);
        $signature = $this->generateSignature($message);
        $payload = array_merge($data, [
            'signed_field_names' => implode(',', $signedFields),
            'signature'          => $signature,
            'callback_url'       => $params['callback_url'] ?? config('services.esewa.callback_url'),
            'redirect_url'       => $params['redirect_url'] ?? config('services.esewa.redirect_url'),
            'properties'         => $params['properties'] ?? [],
        ]);
        // dd($payload);
        $response = Http::post("{$this->baseUrl}/book", $payload);
        // dd($response->json());
        return $response->json();
    }

    /**
     * Check payment status
     */
    public function checkStatus(string $bookingId, string $correlationId): array
    {
        $signedFields = ['booking_id', 'product_code', 'correlation_id'];
        $data = [
            'booking_id'     => $bookingId,
            'product_code'   => $this->productCode,
            'correlation_id' => $correlationId,
        ];

        $message   = $this->buildSignatureMessage($signedFields, $data);
        $signature = $this->generateSignature($message);

        $payload = array_merge($data, [
            'signed_field_names' => implode(',', $signedFields),
            'signature'          => $signature,
        ]);
        // ✅ Full correct URL
        $response = Http::post("{$this->baseUrl}/status", $payload);
        // dd($response->json());
        return $response->json();
    }

    /**
     * Cancel a payment
     */
    public function cancelPayment(string $bookingId): array
    {
        $signedFields = ['booking_id', 'product_code'];
        $data = [
            'booking_id'   => $bookingId,
            'product_code' => $this->productCode,
        ];

        $message   = $this->buildSignatureMessage($signedFields, $data);
        $signature = $this->generateSignature($message);

        $payload = array_merge($data, [
            'signed_field_names' => implode(',', $signedFields),
            'signature'          => $signature,
        ]);

        $response = Http::post("{$this->baseUrl}/cancel", $payload);

        return $response->json();
    }
}