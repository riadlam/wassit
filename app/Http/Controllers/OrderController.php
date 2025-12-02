<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\AccountForSale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        
        // Find the account
        $account = AccountForSale::with('seller')->findOrFail($account_id);
        
        // Check if account is available
        if ($account->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'This account is no longer available.'
            ], 400);
        }
        
        // Check if user already has a pending order for this account
        $existingOrder = Order::where('buyer_id', $user->id)
            ->where('account_id', $account_id)
            ->where('status', 'pending')
            ->first();
        
        if ($existingOrder) {
            // Redirect to existing order checkout
            $encryptedOrderId = CheckoutController::encryptOrderId($existingOrder->id);
            return response()->json([
                'success' => true,
                'redirect' => route('checkout.show', $encryptedOrderId)
            ]);
        }
        
        // Create new order
        try {
            DB::beginTransaction();
            
            $order = Order::create([
                'buyer_id' => $user->id,
                'seller_id' => $account->seller_id,
                'account_id' => $account_id,
                'amount_dzd' => $account->price_dzd, // Raw value
                'status' => 'pending',
            ]);
            
            DB::commit();
            
            // Encrypt order ID and redirect to checkout
            $encryptedOrderId = CheckoutController::encryptOrderId($order->id);
            
            return response()->json([
                'success' => true,
                'redirect' => route('checkout.show', $encryptedOrderId)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
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
