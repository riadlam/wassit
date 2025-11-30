<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page for an order.
     */
    public function show($encryptedOrderId)
    {
        Log::info('CheckoutController@show - Attempting to access checkout', [
            'encrypted_order_id' => substr($encryptedOrderId, 0, 20) . '...',
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);

        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
            Log::info('CheckoutController@show - Successfully decrypted order ID', [
                'order_id' => $orderId,
            ]);
            
            // Load order with relationships
            $order = Order::with(['account.game', 'account.images', 'seller.user', 'buyer'])
                ->findOrFail($orderId);
            
            Log::info('CheckoutController@show - Order loaded', [
                'order_id' => $order->id,
                'buyer_id' => $order->buyer_id,
                'seller_id' => $order->seller_id,
                'status' => $order->status,
                'account_id' => $order->account_id,
            ]);
            
            // Ensure order is in pending status
            if ($order->status !== 'pending') {
                Log::warning('CheckoutController@show - Order not in pending status', [
                    'order_id' => $order->id,
                    'status' => $order->status,
                ]);
                
                if (Auth::check()) {
                    return redirect()->route('account.orders')
                        ->with('error', 'This order is no longer available for checkout.');
                } else {
                    return redirect()->route('home')
                        ->with('error', 'This order is no longer available for checkout.');
                }
            }
            
            // If user is authenticated, verify they own the order (only if buyer_id is set)
            // Unauthenticated users can access to enter their details
            if (Auth::check() && $order->buyer_id && Auth::id() !== $order->buyer_id) {
                Log::warning('CheckoutController@show - Unauthorized access attempt', [
                    'order_id' => $order->id,
                    'order_buyer_id' => $order->buyer_id,
                    'authenticated_user_id' => Auth::id(),
                    'message' => 'Authenticated user does not own this order',
                ]);
                abort(403, 'Unauthorized access to this order.');
            }
            
            Log::info('CheckoutController@show - Authorization passed', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
            ]);
            
            // Calculate fees
            $subtotal = $order->amount_dzd / 100; // Convert from cents to DZD
            $processorFeePercent = 3.9; // 3.9% processor fee
            $processorFee = $subtotal * ($processorFeePercent / 100);
            $total = $subtotal + $processorFee;
            
            // Get account image at index 0 for display
            $accountImages = $order->account->images;
            $accountImage = $accountImages->isNotEmpty() ? $accountImages[0] : null;
            
            // Determine seller PFP with fallback to examplepfp.webp
            $sellerPfp = asset('storage/examplepfp.webp'); // Default fallback
            if ($order->seller && !empty($order->seller->pfp)) {
                // Check if pfp is a full URL or a storage path
                if (filter_var($order->seller->pfp, FILTER_VALIDATE_URL)) {
                    // It's a full URL, use it directly
                    $sellerPfp = $order->seller->pfp;
                } else {
                    // It's a storage path, check if file exists
                    $pfpPath = str_replace('storage/', '', $order->seller->pfp);
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($pfpPath)) {
                        $sellerPfp = asset('storage/' . $pfpPath);
                    }
                }
            }
            
            return view('checkout.show', [
                'order' => $order,
                'encryptedOrderId' => $encryptedOrderId,
                'subtotal' => $subtotal,
                'processorFeePercent' => $processorFeePercent,
                'processorFee' => $processorFee,
                'total' => $total,
                'accountImage' => $accountImage,
                'sellerPfp' => $sellerPfp,
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('CheckoutController@show - Decryption failed', [
                'encrypted_order_id' => substr($encryptedOrderId, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);
            abort(404, 'Invalid order ID.');
        } catch (\Exception $e) {
            Log::error('CheckoutController@show - Unexpected error', [
                'error' => $e->getMessage(),
                'exception' => class_basename($e),
                'order_id' => $orderId ?? null,
            ]);
            throw $e;
        }
    }
    
    /**
     * Encrypt an order ID for use in URLs.
     */
    public static function encryptOrderId($orderId)
    {
        return Crypt::encryptString($orderId);
    }
}

