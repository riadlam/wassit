<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Chargily\ChargilyPay\ChargilyPay;
use Chargily\ChargilyPay\Auth\Credentials;
use App\Events\MessageSent;

class PaymentController extends Controller
{
    /**
     * Build ChargilyPay SDK instance from env/config.
     */
    protected function chargilyPayInstance(): ChargilyPay
    {
        $mode = config('chargily.mode', env('CHARGILY_MODE', 'live'));
        $public = config('chargily.api_key', env('CHARGILY_EPAY_KEY'));
        $secret = config('chargily.api_secret', env('CHARGILY_EPAY_SECRET'));
        if (!class_exists(\Chargily\ChargilyPay\ChargilyPay::class)) {
            Log::error('Chargily SDK class not found. Have you run composer install on server?', [
                'expected_class' => 'Chargily\\ChargilyPay\\ChargilyPay',
                'mode' => $mode,
            ]);
            throw new \RuntimeException('Payment provider temporarily unavailable. Please retry later.');
        }
        return new ChargilyPay(new Credentials([
            'mode' => $mode,
            'public' => (string)$public,
            'secret' => (string)$secret,
        ]));
    }

    /**
     * Initiate payment with Chargily
     */
    public function initiatePayment(Request $request, $encryptedOrderId)
    {
        Log::info('PaymentController::initiatePayment - Starting payment process', [
            'user_id' => Auth::id(),
            'encrypted_order_id' => substr($encryptedOrderId, 0, 20) . '...',
        ]);

        try {
            // Decrypt order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
            
            // Load order
            $order = Order::with('account', 'buyer')->findOrFail($orderId);
            
            // Verify order ownership and status
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is no longer available for payment.',
                ], 400);
            }

            // If authenticated, verify ownership
            if (Auth::check() && (int)Auth::id() !== (int)$order->buyer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this order.',
                ], 403);
            }

            // Get buyer email (either from auth user or from request)
            $buyerEmail = Auth::check() ? Auth::user()->email : $request->input('email');
            if (!$buyerEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is required for payment.',
                ], 400);
            }

            // Create Chargily checkout via SDK
            $checkout = $this->chargilyPayInstance()->checkouts()->create([
                'metadata' => [
                    'order_id' => (string)$order->id,
                ],
                'locale' => app()->getLocale() ?? 'en',
                'amount' => (string) (int) $order->amount_dzd,
                'currency' => 'dzd',
                'description' => 'Account Purchase - Order #' . $order->id,
                'success_url' => route('payment.success', ['encryptedOrderId' => $encryptedOrderId]),
                'failure_url' => route('payment.failure', ['encryptedOrderId' => $encryptedOrderId]),
                'webhook_endpoint' => config('chargily.webhook_url') ?: route('webhook.chargily'),
            ]);

            if (!$checkout) {
                throw new \Exception('Failed to create Chargily checkout');
            }

            Log::info('PaymentController::initiatePayment - Checkout created successfully', [
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => true,
                'checkout_url' => $checkout->getUrl(),
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentController::initiatePayment - Exception', [
                'error' => $e->getMessage(),
                'order_id' => $orderId ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.',
            ], 500);
        }
    }

    /**
     * Payment success callback
     */
    public function paymentSuccess($encryptedOrderId)
    {
        Log::info('PaymentController::paymentSuccess - Payment success callback', [
            'user_id' => Auth::id(),
        ]);

        try {
            $orderId = Crypt::decryptString($encryptedOrderId);
            $order = Order::with(['buyer', 'seller'])->findOrFail($orderId);

            // Ensure there is a conversation between buyer and seller
            $conversation = \App\Models\Conversation::firstOrCreate([
                'buyer_id' => (int)$order->buyer_id,
                'seller_id' => (int)$order->seller_id,
            ]);

            // Post a system message notifying payment initiation success (final confirmation via webhook)
            $sysMsg = \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => null, // system
                'sender_type' => 'system',
                'message_type' => 'text',
                'content' => 'Payment initiated for Order #' . $order->id . '. Awaiting confirmation.',
            ]);

            // Optionally bump conversation last_message_at for ordering
            try {
                $conversation->last_message_at = now();
                $conversation->save();
            } catch (\Throwable $t) {}

            // Broadcast system message so chat updates in real-time
            $broadcastMessage = [
                'id' => $sysMsg->id,
                'type' => 'system',
                'content' => $sysMsg->content,
                'timestamp' => 'Just now',
                'read' => true,
            ];
            event(new MessageSent($conversation, $broadcastMessage));

            // Focus chat UI to the conversation
            session(['active_chat_conversation_id' => $conversation->id]);

            return redirect()->route('account.chat')
                ->with('success', 'Payment initiated. Chat opened to notify the seller.');
        } catch (\Exception $e) {
            Log::error('PaymentController::paymentSuccess - Exception', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('home')->with('error', 'Payment process error.');
        }
    }

    /**
     * Payment failure callback
     */
    public function paymentFailure($encryptedOrderId)
    {
        Log::info('PaymentController::paymentFailure - Payment failure callback', [
            'user_id' => Auth::id(),
        ]);

        try {
            $orderId = Crypt::decryptString($encryptedOrderId);
            $order = Order::findOrFail($orderId);

            // Do not open chat on failure; show a clear failure screen
            return redirect()->route('checkout.show', ['encryptedOrderId' => $encryptedOrderId])
                ->with('error', 'Payment was cancelled or failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('PaymentController::paymentFailure - Exception', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('home')->with('error', 'Payment process error.');
        }
    }
}
