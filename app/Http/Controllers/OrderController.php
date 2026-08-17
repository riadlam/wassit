<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\AccountForSale;
use App\Models\SuperDiscountOffer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\CheckoutController;

class OrderController extends Controller
{
    public function create(Request $request, $account_id)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to create an order.'
            ], 401);
        }

        try {
            $payload = DB::transaction(function () use ($user, $account_id) {
                $account = AccountForSale::query()
                    ->whereKey($account_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($account->status !== 'available') {
                    return [
                        'error' => true,
                        'status' => 400,
                        'message' => 'This account is no longer available.',
                    ];
                }

                $offer = SuperDiscountOffer::query()
                    ->where('account_id', $account->id)
                    ->activeNow()
                    ->lockForUpdate()
                    ->first();

                $amount = $offer
                    ? $offer->discountedPrice((int) $account->price_dzd)
                    : (int) $account->price_dzd;

                $existingOrder = Order::query()
                    ->where('buyer_id', $user->id)
                    ->where('account_id', $account_id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->first();

                if ($existingOrder) {
                    if ($amount < (int) $existingOrder->amount_dzd) {
                        $existingOrder->update(['amount_dzd' => $amount]);
                    }

                    return [
                        'error' => false,
                        'order' => $existingOrder->fresh(),
                    ];
                }

                $order = Order::create([
                    'buyer_id' => $user->id,
                    'seller_id' => $account->seller_id,
                    'account_id' => $account_id,
                    'amount_dzd' => $amount,
                    'status' => 'pending',
                ]);

                return [
                    'error' => false,
                    'order' => $order,
                ];
            }, 3);

            if (! empty($payload['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $payload['message'],
                ], $payload['status']);
            }

            $encryptedOrderId = CheckoutController::encryptOrderId($payload['order']->id);

            return response()->json([
                'success' => true,
                'redirect' => route('checkout.show', $encryptedOrderId)
            ]);
        } catch (\Throwable $e) {
            Log::error('OrderController::create - Failed to create order', [
                'user_id' => $user->id,
                'account_id' => $account_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.'
            ], 500);
        }
    }

    public function getBuyerOrders(Request $request)
    {
        // TODO: Return all orders for authenticated buyer
    }

    public function getSellerOrders(Request $request)
    {
        // TODO: Return all orders for authenticated seller
    }

    public function confirm(Request $request, $id)
    {
        // TODO: Confirm order completion
    }

    public function cancel(Request $request, $id)
    {
        // TODO: Cancel order
    }
}
