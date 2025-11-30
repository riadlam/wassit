<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page for an order.
     */
    public function show($encryptedOrderId)
    {
        try {
            // Decrypt the order ID
            $orderId = Crypt::decryptString($encryptedOrderId);
            
            // Load order with relationships
            $order = Order::with(['account.game', 'account.images', 'seller.user', 'buyer'])
                ->findOrFail($orderId);
            
            // Ensure order is in pending status
            if ($order->status !== 'pending') {
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
                abort(403, 'Unauthorized access to this order.');
            }
            
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
            abort(404, 'Invalid order ID.');
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

