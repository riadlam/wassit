<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChargilyService
{
    protected $apiKey;
    protected $apiSecret;
    protected $baseUrl;

    public function __construct()
    {
        $mode = config('chargily.mode', 'live');
        $this->apiKey = config('chargily.api_key');
        $this->apiSecret = config('chargily.api_secret');
        $this->baseUrl = config('chargily.' . $mode . '.base_url');
    }

    /**
     * Create a checkout session with Chargily
     */
    public function createCheckout(array $params)
    {
        Log::info('ChargilyService::createCheckout - Initiating checkout', [
            'order_id' => $params['metadata']['order_id'] ?? null,
            'amount' => $params['amount'] ?? null,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/checkouts', $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('ChargilyService::createCheckout - Success', [
                    'checkout_id' => $data['id'] ?? null,
                    'checkout_url' => $data['checkout_url'] ?? null,
                ]);
                return $data;
            }

            Log::error('ChargilyService::createCheckout - Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to create Chargily checkout: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('ChargilyService::createCheckout - Exception', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a checkout by ID
     */
    public function retrieveCheckout($checkoutId)
    {
        Log::info('ChargilyService::retrieveCheckout - Fetching checkout', [
            'checkout_id' => $checkoutId,
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/checkouts/' . $checkoutId);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('ChargilyService::retrieveCheckout - Success', [
                    'checkout_id' => $data['id'] ?? null,
                    'status' => $data['status'] ?? null,
                ]);
                return $data;
            }

            Log::error('ChargilyService::retrieveCheckout - Failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('ChargilyService::retrieveCheckout - Exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature($payload, $signature)
    {
        Log::info('ChargilyService::validateWebhookSignature - Validating webhook');

        // Chargily uses HMAC-SHA256 for webhook signature
        $calculatedSignature = hash_hmac('sha256', $payload, $this->apiSecret);

        $isValid = hash_equals($calculatedSignature, $signature);

        if (!$isValid) {
            Log::warning('ChargilyService::validateWebhookSignature - Invalid signature', [
                'provided' => substr($signature, 0, 20) . '...',
                'calculated' => substr($calculatedSignature, 0, 20) . '...',
            ]);
        } else {
            Log::info('ChargilyService::validateWebhookSignature - Valid signature');
        }

        return $isValid;
    }
}
