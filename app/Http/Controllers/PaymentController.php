<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\PaymentStatusUpdated;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\SofizPayCibTransaction;
use App\Services\SofizPayCibService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected SofizPayCibService $sofizPay;

    public function __construct(SofizPayCibService $sofizPay)
    {
        $this->sofizPay = $sofizPay;
    }

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

            if (!(bool) config('services.sofizpay.enabled', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SofizPay is currently disabled.',
                ], 503);
            }

            $merchantAccount = (string) config('services.sofizpay.merchant_account', '');
            if ($merchantAccount === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'SofizPay merchant account is not configured.',
                ], 500);
            }

            $baseAmount = (float) $order->amount_dzd;
            $processingFee = ceil($baseAmount * 0.039);
            $totalAmount = $baseAmount + $processingFee;
            if ($totalAmount < 75) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum payable amount is 75 DZD.',
                ], 400);
            }

            $query = [
                'account' => $merchantAccount,
                'amount' => (string) ((int) round($totalAmount)),
                'full_name' => Auth::check() ? Auth::user()->name : ($request->input('full_name') ?: 'Customer'),
                'phone' => $request->input('phone', '0000000000'),
                'email' => $buyerEmail,
                'return_url' => route('payment.sofizpay.cib.return', ['eid' => $encryptedOrderId]),
                'memo' => 'Wassit Order #' . $order->id,
                'redirect' => (string) config('services.sofizpay.redirect', 'no'),
                'keep_return_url' => (string) config('services.sofizpay.keep_return_url', 'True'),
            ];

            $createResponse = $this->sofizPay->createCibTransaction($query);
            $checkoutUrl = $createResponse['payment_url'] ?? null;
            if (!is_string($checkoutUrl) || trim($checkoutUrl) === '') {
                throw new \RuntimeException('SofizPay did not return payment_url.');
            }

            $tx = SofizPayCibTransaction::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'transaction_id' => $createResponse['transaction_id'] ?? null,
                    'cib_order_number' => $createResponse['order_number'] ?? null,
                    'cib_order_id' => $createResponse['orderId'] ?? ($createResponse['mdOrder'] ?? null),
                    'amount_expected' => (float) $totalAmount,
                    'status' => 'pending',
                    'create_response' => $createResponse,
                ]
            );

            $order->update([
                'sofizpay_cib_transaction_id' => $tx->id,
            ]);

            Log::info('PaymentController::initiatePayment - Checkout created successfully', [
                'order_id' => $order->id,
            ]);

            return response()->json([
                'success' => true,
                'checkout_url' => $checkoutUrl,
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
     * SofizPay CIB return callback.
     */
    public function sofizpayCibReturn(Request $request)
    {
        Log::info('PaymentController::sofizpayCibReturn - Return callback', [
            'user_id' => Auth::id(),
            'query' => $request->query(),
        ]);

        try {
            $eid = (string) $request->query('eid', '');
            if ($eid === '') {
                return redirect()->route('home')->with('error', 'Missing payment token.');
            }

            $orderId = Crypt::decryptString($eid);
            $order = Order::with(['buyer', 'seller'])->findOrFail($orderId);

            if ($order->status === 'completed') {
                $this->openChatForOrder($order);
                return redirect()->route('account.chat')->with('success', 'Payment already confirmed.');
            }

            $tx = $order->sofizpayCibTransaction;
            if (!$tx || !$tx->cib_order_number) {
                return redirect()->route('checkout.show', ['encryptedOrderId' => $eid])
                    ->with('error', 'Payment session not found. Please retry payment.');
            }

            $checkResponse = $this->sofizPay->checkCibTransaction($tx->cib_order_number);
            $tx->update([
                'last_check_response' => $checkResponse,
            ]);

            if (!$this->sofizPay->isPaidCheck($checkResponse)) {
                $hint = $this->sofizPay->parsePaymentFailureHint($checkResponse);
                return redirect()->route('checkout.show', ['encryptedOrderId' => $eid])
                    ->with('error', $hint);
            }

            $paidAmount = $this->sofizPay->parsePaidAmountDzd($checkResponse);
            if ($paidAmount !== null && abs($paidAmount - (float) $tx->amount_expected) > 1.0) {
                Log::error('SofizPay amount mismatch', [
                    'order_id' => $order->id,
                    'expected' => $tx->amount_expected,
                    'paid' => $paidAmount,
                ]);
                return redirect()->route('checkout.show', ['encryptedOrderId' => $eid])
                    ->with('error', 'Payment amount mismatch. Please contact support.');
            }

            $destinationAccount = $this->sofizPay->parseDestinationAccount($checkResponse);
            $merchantAccount = (string) config('services.sofizpay.merchant_account', '');
            if ($destinationAccount && $merchantAccount !== '' && strcasecmp($destinationAccount, $merchantAccount) !== 0) {
                Log::error('SofizPay destination account mismatch', [
                    'order_id' => $order->id,
                    'expected_account' => $merchantAccount,
                    'reported_account' => $destinationAccount,
                ]);
                return redirect()->route('checkout.show', ['encryptedOrderId' => $eid])
                    ->with('error', 'Payment account mismatch. Please contact support.');
            }

            DB::transaction(function () use ($order, $tx, $checkResponse): void {
                $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $lockedTx = SofizPayCibTransaction::whereKey($tx->id)->lockForUpdate()->firstOrFail();

                if ($lockedOrder->status === 'completed' || $lockedTx->status === 'paid') {
                    return;
                }

                $lockedTx->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'last_check_response' => $checkResponse,
                ]);

                $lockedOrder->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'sofizpay_cib_transaction_id' => $lockedTx->id,
                ]);

                $this->notifyPaymentConfirmed($lockedOrder);
            });

            $this->openChatForOrder($order);
            return redirect()->route('account.chat')->with('success', 'Payment confirmed successfully.');
        } catch (\Exception $e) {
            Log::error('PaymentController::sofizpayCibReturn - Exception', [
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

    protected function notifyPaymentConfirmed(Order $order): void
    {
        $conversation = Conversation::firstOrCreate([
            'buyer_id' => (int) $order->buyer_id,
            'seller_id' => (int) $order->seller_id,
            'account_for_sale_id' => (int) $order->account_id,
        ]);

        $sysMsg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'sender_type' => 'system',
            'message_type' => 'text',
            'content' => 'Payment confirmed for Order #' . $order->id . '. Seller, please proceed to deliver the account.',
        ]);

        try {
            $conversation->last_message_at = now();
            $conversation->save();
        } catch (\Throwable $t) {
        }

        event(new MessageSent($conversation, [
            'id' => $sysMsg->id,
            'type' => 'system',
            'content' => $sysMsg->content,
            'timestamp' => 'Just now',
            'read' => true,
        ]));

        event(new PaymentStatusUpdated($conversation, [
            'paid' => true,
            'orderId' => $order->id,
        ]));
    }

    protected function openChatForOrder(Order $order): void
    {
        $conversation = Conversation::firstOrCreate([
            'buyer_id' => (int) $order->buyer_id,
            'seller_id' => (int) $order->seller_id,
            'account_for_sale_id' => (int) $order->account_id,
        ]);

        session(['active_chat_conversation_id' => $conversation->id]);
    }
}
