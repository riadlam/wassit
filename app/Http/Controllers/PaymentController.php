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
                'amount' => number_format($totalAmount, 2, '.', ''),
                'full_name' => Auth::check() ? Auth::user()->name : ($request->input('full_name') ?: 'Customer'),
                'phone' => $this->resolveSofizPayPhone($request),
                'email' => $buyerEmail,
                'return_url' => route('payment.sofizpay.cib.return', ['eid' => $encryptedOrderId]),
                'memo' => 'Wassit Order #' . $order->id,
                'redirect' => $this->normalizeRedirectFlag(config('services.sofizpay.redirect', 'no')),
                'keep_return_url' => $this->normalizeKeepReturnUrlFlag(config('services.sofizpay.keep_return_url', 'True')),
            ];

            Log::info('PaymentController::initiatePayment - SofizPay request payload', [
                'order_id' => $order->id,
                'account' => $query['account'],
                'amount' => $query['amount'],
                'full_name' => $query['full_name'],
                'phone' => $query['phone'],
                'email_masked' => $this->maskEmail((string) $query['email']),
                'return_url' => $query['return_url'],
                'redirect' => $query['redirect'],
                'keep_return_url' => $query['keep_return_url'],
            ]);

            $createResponse = $this->createSofizPayTransactionWithFallback($query, (int) $order->id);
            $checkoutUrl = $createResponse['payment_url']
                ?? ($createResponse['cib_response']['formUrl'] ?? null);
            if (!is_string($checkoutUrl) || trim($checkoutUrl) === '') {
                throw new \RuntimeException('SofizPay did not return payment_url.');
            }

            $tx = SofizPayCibTransaction::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'transaction_id' => $createResponse['transaction_id'] ?? null,
                    // SofizPay check endpoint expects "order_number", which is returned as cib_transaction_id.
                    'cib_order_number' => $createResponse['cib_transaction_id']
                        ?? $createResponse['order_number']
                        ?? null,
                    'cib_order_id' => $createResponse['cib_response']['orderId']
                        ?? $createResponse['orderId']
                        ?? ($createResponse['mdOrder'] ?? null),
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

    protected function resolveSofizPayPhone(Request $request): string
    {
        $phone = (string) ($request->input('phone') ?? config('services.sofizpay.default_phone', ''));
        $phone = preg_replace('/\s+/', '', trim($phone));

        // Normalize common DZ local forms to international format.
        if (preg_match('/^0[0-9]{9}$/', $phone)) {
            $phone = '+213' . substr($phone, 1);
        } elseif (preg_match('/^213[0-9]{9}$/', $phone)) {
            $phone = '+' . $phone;
        }

        if ($phone === '' || !preg_match('/^\+[0-9]{8,15}$/', $phone)) {
            // Keep a deterministic fallback in international format.
            $phone = '+213550000000';
        }

        return $phone;
    }

    protected function maskEmail(string $email): string
    {
        if ($email === '' || !str_contains($email, '@')) {
            return 'invalid-email';
        }

        [$name, $domain] = explode('@', $email, 2);
        $nameMasked = strlen($name) <= 2 ? str_repeat('*', strlen($name)) : substr($name, 0, 2) . '***';

        return $nameMasked . '@' . $domain;
    }

    protected function normalizeRedirectFlag(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        $raw = strtolower(trim((string) $value));
        return in_array($raw, ['yes', '1', 'true'], true) ? 'yes' : 'no';
    }

    protected function normalizeKeepReturnUrlFlag(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'True' : 'False';
        }

        $raw = strtolower(trim((string) $value));
        return in_array($raw, ['true', '1', 'yes'], true) ? 'True' : 'False';
    }

    /**
     * SofizPay live accounts can have strict validation differences.
     * Try safe variants before failing to reduce manual debugging.
     */
    protected function createSofizPayTransactionWithFallback(array $baseQuery, int $orderId): array
    {
        $attempts = [];
        $attempts[] = $baseQuery;

        // Variant 2: force redirect=yes (some merchant profiles require redirect flow)
        if (($baseQuery['redirect'] ?? 'no') !== 'yes') {
            $variant = $baseQuery;
            $variant['redirect'] = 'yes';
            $attempts[] = $variant;
        }

        // Variant 3: keep_return_url=False (some profiles reject signed-return flow)
        if (($baseQuery['keep_return_url'] ?? 'True') !== 'False') {
            $variant = $baseQuery;
            $variant['keep_return_url'] = 'False';
            $attempts[] = $variant;
        }

        // Variant 4: phone without leading plus
        if (isset($baseQuery['phone']) && is_string($baseQuery['phone']) && str_starts_with($baseQuery['phone'], '+')) {
            $variant = $baseQuery;
            $variant['phone'] = ltrim($baseQuery['phone'], '+');
            $attempts[] = $variant;
        }

        $lastException = null;
        foreach ($attempts as $idx => $query) {
            try {
                if ($idx > 0) {
                    Log::warning('PaymentController::initiatePayment - SofizPay fallback attempt', [
                        'order_id' => $orderId,
                        'attempt' => $idx + 1,
                        'redirect' => $query['redirect'] ?? null,
                        'keep_return_url' => $query['keep_return_url'] ?? null,
                        'phone' => $query['phone'] ?? null,
                    ]);
                }

                return $this->sofizPay->createCibTransaction($query);
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning('PaymentController::initiatePayment - SofizPay attempt failed', [
                    'order_id' => $orderId,
                    'attempt' => $idx + 1,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw new \RuntimeException(
            $lastException ? $lastException->getMessage() : 'Failed to create SofizPay transaction.'
        );
    }
}
