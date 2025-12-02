<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ChargilyPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Chargily\ChargilyPay\ChargilyPay;
use Chargily\ChargilyPay\Auth\Credentials;
use Chargily\ChargilyPay\Elements\CheckoutElement;
use App\Events\MessageSent;
use App\Events\PaymentStatusUpdated;

class WebhookController extends Controller
{
    /** Build SDK instance */
    protected function chargilyPayInstance(): ChargilyPay
    {
        $mode = config('chargily.mode', env('CHARGILY_MODE', 'live'));
        $public = config('chargily.api_key', env('CHARGILY_EPAY_KEY'));
        $secret = config('chargily.api_secret', env('CHARGILY_EPAY_SECRET'));
        return new ChargilyPay(new Credentials([
            'mode' => $mode,
            'public' => (string)$public,
            'secret' => (string)$secret,
        ]));
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
            $webhook = $this->chargilyPayInstance()->webhook()->get();
            if (!$webhook) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Webhook request',
                ], 403);
            }

            $checkout = $webhook->getData();
            if (!$checkout || !($checkout instanceof CheckoutElement)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Webhook payload',
                ], 400);
            }

            $status = $checkout->getStatus();
            if ($status === 'paid') {
                return $this->handleCheckoutConfirmed($checkout);
            } elseif (in_array($status, ['failed', 'canceled'])) {
                return $this->handleCheckoutFailed($checkout);
            } elseif ($status === 'expired') {
                return $this->handleCheckoutExpired($checkout);
            }

            return response()->json(['status' => 'ignored']);
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
    protected function handleCheckoutConfirmed(CheckoutElement $checkout)
    {
        Log::info('WebhookController::handleCheckoutConfirmed - Processing confirmed checkout', [
            'status' => $checkout->getStatus(),
            'metadata' => $checkout->getMetadata(),
        ]);

        try {
            $metadata = $checkout->getMetadata();
            $order = isset($metadata['order_id']) ? Order::find($metadata['order_id']) : null;

            if (!$order) {
                Log::warning('WebhookController::handleCheckoutConfirmed - Order not found', [
                    'metadata' => $metadata,
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Update order status to completed
            if ($order->status === 'pending') {
                $order->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);

                // Create/find conversation for this buyer/seller/account and notify seller with system message
                $conversation = \App\Models\Conversation::firstOrCreate([
                    'buyer_id' => (int)$order->buyer_id,
                    'seller_id' => (int)$order->seller_id,
                    'account_for_sale_id' => (int)$order->account_id,
                ]);

                $sysMsg = \App\Models\Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => null,
                    'sender_type' => 'system',
                    'message_type' => 'text',
                    'content' => 'Payment confirmed for Order #' . $order->id . '. Seller, please proceed to deliver the account.',
                ]);

                // Update conversation ordering timestamp
                try {
                    $conversation->last_message_at = now();
                    $conversation->save();
                } catch (\Throwable $t) {}

                // Broadcast system message for real-time update in chat
                $broadcastMessage = [
                    'id' => $sysMsg->id,
                    'type' => 'system',
                    'content' => $sysMsg->content,
                    'timestamp' => 'Just now',
                    'read' => true,
                ];
                event(new MessageSent($conversation, $broadcastMessage));

                // Broadcast payment status update so header badge reflects instantly
                event(new PaymentStatusUpdated($conversation, [
                    'paid' => true,
                    'orderId' => $order->id,
                ]));

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
    protected function handleCheckoutFailed(CheckoutElement $checkout)
    {
        Log::warning('WebhookController::handleCheckoutFailed - Checkout failed', [
            'status' => $checkout->getStatus(),
            'metadata' => $checkout->getMetadata(),
        ]);

        try {
            $metadata = $checkout->getMetadata();
            $order = isset($metadata['order_id']) ? Order::find($metadata['order_id']) : null;

            if ($order) {
                // Log the failure but keep order in pending status for retry
                $order->update([
                    // If you add a JSON metadata column later, you can persist failure info.
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
    protected function handleCheckoutExpired(CheckoutElement $checkout)
    {
        Log::warning('WebhookController::handleCheckoutExpired - Checkout expired', [
            'status' => $checkout->getStatus(),
            'metadata' => $checkout->getMetadata(),
        ]);

        try {
            $metadata = $checkout->getMetadata();
            $order = isset($metadata['order_id']) ? Order::find($metadata['order_id']) : null;

            // Keep order pending; no DB changes required here

            return response()->json(['status' => 'processed']);
        } catch (\Exception $e) {
            Log::error('WebhookController::handleCheckoutExpired - Exception', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to process'], 500);
        }
    }
}
