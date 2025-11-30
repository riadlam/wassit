<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\ChargilyService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $chargilyService;

    public function __construct(ChargilyService $chargilyService)
    {
        $this->chargilyService = $chargilyService;
    }

    /**
     * Handle Chargily webhook for payment status updates
     */
    public function chargilyWebhook(Request $request)
    {
        Log::info('WebhookController::chargilyWebhook - Webhook received', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
        ]);

        try {
            // Get the raw body and signature
            $payload = $request->getContent();
            $signature = $request->header('X-Signature-SHA256');

            if (!$signature) {
                Log::warning('WebhookController::chargilyWebhook - Missing signature header');
                return response()->json(['error' => 'Missing signature'], 400);
            }

            // Validate webhook signature
            if (!$this->chargilyService->validateWebhookSignature($payload, $signature)) {
                Log::warning('WebhookController::chargilyWebhook - Invalid signature');
                return response()->json(['error' => 'Invalid signature'], 403);
            }

            // Parse JSON payload
            $data = json_decode($payload, true);
            
            Log::info('WebhookController::chargilyWebhook - Valid webhook', [
                'event' => $data['event'] ?? null,
                'checkout_id' => $data['data']['checkout_id'] ?? null,
                'status' => $data['data']['status'] ?? null,
            ]);

            // Handle different webhook events
            $event = $data['event'] ?? null;

            switch ($event) {
                case 'checkout.confirmed':
                    return $this->handleCheckoutConfirmed($data['data']);
                case 'checkout.failed':
                    return $this->handleCheckoutFailed($data['data']);
                case 'checkout.expired':
                    return $this->handleCheckoutExpired($data['data']);
                default:
                    Log::info('WebhookController::chargilyWebhook - Unknown event', ['event' => $event]);
                    return response()->json(['status' => 'received']);
            }
        } catch (\Exception $e) {
            Log::error('WebhookController::chargilyWebhook - Exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle successful payment checkout confirmation
     */
    protected function handleCheckoutConfirmed($checkoutData)
    {
        Log::info('WebhookController::handleCheckoutConfirmed - Processing confirmed checkout', [
            'checkout_id' => $checkoutData['checkout_id'] ?? null,
            'status' => $checkoutData['status'] ?? null,
        ]);

        try {
            // Find order by checkout ID
            $order = Order::where('chargily_checkout_id', $checkoutData['checkout_id'])
                ->first();

            if (!$order) {
                Log::warning('WebhookController::handleCheckoutConfirmed - Order not found', [
                    'checkout_id' => $checkoutData['checkout_id'],
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Update order status to completed
            if ($order->status === 'pending') {
                $order->update([
                    'status' => 'completed',
                    'chargily_payment_id' => $checkoutData['id'] ?? null,
                    'paid_at' => now(),
                ]);

                Log::info('WebhookController::handleCheckoutConfirmed - Order completed', [
                    'order_id' => $order->id,
                    'buyer_id' => $order->buyer_id,
                ]);
            }

            return response()->json(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('WebhookController::handleCheckoutConfirmed - Exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to process'], 500);
        }
    }

    /**
     * Handle failed payment checkout
     */
    protected function handleCheckoutFailed($checkoutData)
    {
        Log::warning('WebhookController::handleCheckoutFailed - Checkout failed', [
            'checkout_id' => $checkoutData['checkout_id'] ?? null,
            'reason' => $checkoutData['reason'] ?? null,
        ]);

        try {
            $order = Order::where('chargily_checkout_id', $checkoutData['checkout_id'])
                ->first();

            if ($order) {
                // Log the failure but keep order in pending status for retry
                $order->update([
                    'metadata' => json_encode([
                        'last_failure_reason' => $checkoutData['reason'] ?? null,
                        'last_failure_at' => now(),
                    ]),
                ]);
            }

            return response()->json(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('WebhookController::handleCheckoutFailed - Exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to process'], 500);
        }
    }

    /**
     * Handle expired checkout
     */
    protected function handleCheckoutExpired($checkoutData)
    {
        Log::warning('WebhookController::handleCheckoutExpired - Checkout expired', [
            'checkout_id' => $checkoutData['checkout_id'] ?? null,
        ]);

        try {
            $order = Order::where('chargily_checkout_id', $checkoutData['checkout_id'])
                ->first();

            if ($order && $order->status === 'pending') {
                // Keep order in pending, allow user to retry
                $order->update([
                    'chargily_checkout_id' => null,
                ]);
            }

            return response()->json(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('WebhookController::handleCheckoutExpired - Exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to process'], 500);
        }
    }
}
