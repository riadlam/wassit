<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AccountForSale;
use App\Models\AccountAttribute;
use App\Models\AccountImage;
use App\Support\SkinsHelper;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }
    
    public function orders()
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        if ($seller) {
            // Seller view: show orders where they are the seller
            $orders = \App\Models\Order::where('seller_id', $seller->id)
                ->with(['buyer', 'account.game'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            $isSeller = true;
        } else {
            // Buyer view: show orders where they are the buyer
            $orders = \App\Models\Order::where('buyer_id', $user->id)
                ->with(['seller.user', 'account.game'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
            $isSeller = false;
        }
        
        return view('dashboard.orders', compact('orders', 'isSeller'));
    }
    
    public function chat()
    {
        // Optionally pre-select a conversation using a value stored in session
        $activeConversationId = session()->pull('active_chat_conversation_id', null);
        return view('dashboard.chat', compact('activeConversationId'));
    }
    
    public function wallet()
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        $walletBalance = 0;
        $transactions = collect();
        
        if ($seller) {
            // Seller wallet: show balance and completed/delivered orders (earnings)
            $walletBalance = $seller->wallet ?? 0;
            
            // Get completed orders where seller received payment (delivery confirmed)
            $transactions = \App\Models\Order::where('seller_id', $seller->id)
                ->where('status', 'completed')
                ->where('delivery_status', 'delivered')
                ->with(['buyer', 'account'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($order) {
                    $baseAmount = (float) $order->amount_dzd;
                    $processingFee = round($baseAmount * 0.039, 2);
                    $payout = $baseAmount - $processingFee;
                    
                    return [
                        'id' => $order->id,
                        'order_id' => $order->id,
                        'transaction_id' => $order->chargily_payment_id ?? 'N/A',
                        'payment_method' => 'Chargily',
                        'status' => 'Completed',
                        'amount' => $payout,
                        'type' => 'earning',
                        'updated_at' => $order->updated_at,
                        'buyer_name' => $order->buyer->name ?? 'Unknown',
                        'account_title' => $order->account->title ?? 'Account #' . $order->account_id,
                    ];
                });
        } else {
            // Buyer view: wallet stays 0, but show purchase transactions with full amount paid
            $transactions = \App\Models\Order::where('buyer_id', $user->id)
                ->where('status', 'completed')
                ->with(['seller.user', 'account'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($order) {
                    $baseAmount = (float) $order->amount_dzd;
                    $processingFee = round($baseAmount * 0.039, 2);
                    $totalPaid = $baseAmount + $processingFee;
                    
                    return [
                        'id' => $order->id,
                        'order_id' => $order->id,
                        'transaction_id' => $order->chargily_payment_id ?? 'N/A',
                        'payment_method' => 'Chargily',
                        'status' => $order->delivery_status === 'delivered' ? 'Delivered' : 'Pending Delivery',
                        'amount' => $totalPaid,
                        'type' => 'purchase',
                        'updated_at' => $order->updated_at,
                        'seller_name' => $order->seller && $order->seller->user ? $order->seller->user->name : 'Unknown',
                        'account_title' => $order->account->title ?? 'Account #' . $order->account_id,
                    ];
                });
        }
        
        return view('dashboard.wallet', compact('walletBalance', 'transactions'));
    }
    
    public function library()
    {
        return view('dashboard.library');
    }
    
    public function listedAccounts()
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        if (!$seller) {
            return redirect()->route('account.index')
                ->with('error', 'Seller profile not found. Please contact support.');
        }
        
        // Get all accounts for this seller with relationships
        $accounts = AccountForSale::where('seller_id', $seller->id)
            ->with(['game', 'attributes', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('dashboard.listed-accounts', compact('accounts'));
    }
    
    public function createAccount()
    {
        $games = \App\Models\Game::all();
        $mlbbGame = \App\Models\Game::where('slug', 'mlbb')->first();
        $mlbbId = $mlbbGame ? $mlbbGame->id : null;
        return view('dashboard.create-account', compact('games', 'mlbbId'));
    }
    
    public function storeAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'game_id' => 'required|integer|exists:games,id',
            // Allow raw input; we'll normalize to cents safely (accepts 1000, 1,000, 1000.00, 1.000,50)
            'price_dzd' => 'required|string',
            'status' => 'required|in:available,disabled,pending',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max per file
        ], [
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, and WEBP images are allowed.',
            'images.*.max' => 'Each image must not exceed 10MB.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        try {
            // Normalize price to cents (integer) with no separators or decimals carried over
            $priceCents = $this->normalizePriceToCents($request->input('price_dzd'));
            if ($priceCents === null || $priceCents < 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid price format.',
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'Invalid price format.')
                    ->withInput();
            }
            // Use database transaction to ensure data integrity
            return DB::transaction(function () use ($request, $priceCents) {
                $user = Auth::user();
                $seller = $user->seller;
                
                if (!$seller) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Seller profile not found. Please contact support.'
                        ], 404);
                    }
                    return redirect()->back()
                        ->with('error', 'Seller profile not found. Please contact support.')
                        ->withInput();
                }

                // Create the account with all relationships (SQL injection protection via Eloquent parameterized queries)
                $account = AccountForSale::create([
                    'seller_id' => (int)$seller->id, // Ensure integer
                    'game_id' => (int)$request->game_id, // Ensure integer
                    'title' => strip_tags($request->title), // Sanitize HTML
                    'description' => strip_tags($request->description), // Sanitize HTML
                    'price_dzd' => $priceCents, // Store as-is (seller enters final value)
                    'status' => in_array($request->status, ['available', 'disabled', 'pending']) ? $request->status : 'available',
                ]);

                // Create attributes if provided (using relationship)
                // Get attributes as array (request->attributes is a ParameterBag object, not an array)
                $attributes = $request->input('attributes', []);
                
                if (!empty($attributes) && is_array($attributes)) {
                    $attributesToCreate = [];
                    
                    foreach ($attributes as $key => $value) {
                        // Allow both string and numeric values (form data may convert strings to numbers)
                        if (!empty($value) && is_string($key) && is_scalar($value)) {
                            // Convert value to string (handles int, float, string)
                            $valueString = (string)$value;
                            
                            // Validate and sanitize: strip HTML tags and limit length
                            $sanitizedKey = substr(strip_tags(trim($key)), 0, 255);
                            $sanitizedValue = substr(strip_tags(trim($valueString)), 0, 255);

                            // Normalize highlighted_skins to numeric IDs (comma-separated)
                            if ($sanitizedKey === 'highlighted_skins') {
                                $normalized = SkinsHelper::normalizeHighlightedSkins($sanitizedValue);
                                if ($normalized !== '') {
                                    $sanitizedValue = $normalized;
                                }
                            }
                            
                            // Only add if both key and value are valid (not empty after sanitization)
                            if (!empty($sanitizedKey) && $sanitizedValue !== '') {
                                $attributesToCreate[] = [
                                    'account_id' => $account->id,
                                    'attribute_key' => $sanitizedKey,
                                    'attribute_value' => $sanitizedValue,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                    
                    // Bulk insert attributes for better performance
                    if (!empty($attributesToCreate)) {
                        AccountAttribute::insert($attributesToCreate);
                    }
                }

                // Handle image uploads (required, max 10 images) - using relationship
                if (!$request->hasFile('images') || count($request->file('images')) === 0) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'At least one image is required.'
                        ], 422);
                    }
                    return redirect()->back()
                        ->with('error', 'At least one image is required.')
                        ->withInput();
                }
                
                if ($request->hasFile('images')) {
                    $images = $request->file('images');
                    
                    // Enforce max 10 images
                    if (count($images) > 10) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Maximum 10 images allowed.'
                            ], 422);
                        }
                        return redirect()->back()
                            ->with('error', 'Maximum 10 images allowed.')
                            ->withInput();
                    }
                    
                    $imagesToCreate = [];
                    
                    foreach ($images as $image) {
                        // Validate file is actually an image
                        if ($image->isValid() && $image->getMimeType() && strpos($image->getMimeType(), 'image/') === 0) {
                            // Store the image directly in public/storage/account_images
                            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                            $image->move(public_path('storage/account_images'), $filename);
                            $path = 'account_images/' . $filename;->getClientOriginalExtension();
                            $image->move(public_path('storage/account_images'), $filename);
                            $path = 'account_images/' . $filename;
                            
                            // Prepare for bulk insert
                            $imagesToCreate[] = [
                                'account_id' => $account->id,
                                'url' => $path,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    
                    // Bulk insert images for better performance
                    if (!empty($imagesToCreate)) {
                        AccountImage::insert($imagesToCreate);
                    }
                }
                
                // Reload account with all relationships
                $account->load(['game', 'seller', 'attributes', 'images']);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Account created successfully',
                        'account' => $account
                    ]);
                }

                return redirect()->route('account.listed-accounts')
                    ->with('success', 'Account created successfully! Your account listing is now live.');
            }, 5); // 5 attempts for deadlock handling
        } catch (\Exception $e) {
            // Get a user-friendly error message
            $errorMessage = 'Failed to create account. Please try again.';
            if (str_contains($e->getMessage(), 'Column not found')) {
                $errorMessage = 'Database configuration error. Please contact support.';
            } elseif (str_contains($e->getMessage(), 'seller')) {
                $errorMessage = 'Seller profile not found. Please contact support to set up your seller account.';
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }
    
    public function editAccount($id)
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        if (!$seller) {
            return redirect()->route('account.listed-accounts')
                ->with('error', 'Seller profile not found.');
        }
        
        // Fetch the account with all relationships
        $account = AccountForSale::with(['game', 'attributes', 'images'])
            ->where('id', $id)
            ->where('seller_id', $seller->id) // Ensure seller owns this account
            ->firstOrFail();
        
        // Build attributes map for blade convenience
        $attributesMap = [];
        foreach ($account->attributes as $attr) {
            $attributesMap[$attr->attribute_key] = $attr->attribute_value;
        }
        return view('dashboard.edit-account', compact('account', 'attributesMap'));
    }
    
    public function updateAccount(Request $request, $id)
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        if (!$seller) {
            \Log::error('Seller not found for user', ['user_id' => $user->id]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller profile not found.'
                ], 404);
            }
            return redirect()->route('account.listed-accounts')
                ->with('error', 'Seller profile not found.');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // Allow raw input; we'll normalize to cents safely
            'price_dzd' => 'required|string',
            'status' => 'required|in:available,disabled,pending,sold,cancelled',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max per file
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:account_images,id',
        ], [
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, and WEBP images are allowed.',
            'images.*.max' => 'Each image must not exceed 10MB.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below.');
        }

        try {
            // Normalize price to cents (integer) with no separators or decimals carried over
            $priceCents = $this->normalizePriceToCents($request->input('price_dzd'));
            if ($priceCents === null || $priceCents < 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid price format.',
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'Invalid price format.')
                    ->withInput();
            }

            return DB::transaction(function () use ($request, $id, $seller, $priceCents) {
                // Fetch the account
                $account = AccountForSale::where('id', $id)
                    ->where('seller_id', $seller->id)
                    ->firstOrFail();

                // Update the account
                $account->update([
                    'title' => strip_tags($request->title),
                    'description' => strip_tags($request->description),
                    'price_dzd' => $priceCents, // Store as-is (seller enters final value)
                    'status' => $request->status,
                ]);

                // Handle attributes update
                $attributes = $request->input('attributes', []);
                
                // Delete existing attributes
                $account->attributes()->delete();
                
                // Create new attributes
                if (!empty($attributes) && is_array($attributes)) {
                    $attributesToCreate = [];
                    
                    foreach ($attributes as $key => $value) {
                        if (!empty($value) && is_string($key) && is_scalar($value)) {
                            $valueString = (string)$value;
                            $sanitizedKey = substr(strip_tags(trim($key)), 0, 255);
                            $sanitizedValue = substr(strip_tags(trim($valueString)), 0, 255);

                            // Normalize highlighted_skins to numeric IDs (comma-separated)
                            if ($sanitizedKey === 'highlighted_skins') {
                                $normalized = SkinsHelper::normalizeHighlightedSkins($sanitizedValue);
                                if ($normalized !== '') {
                                    $sanitizedValue = $normalized;
                                }
                            }
                            
                            if (!empty($sanitizedKey) && $sanitizedValue !== '') {
                                $attributesToCreate[] = [
                                    'account_id' => $account->id,
                                    'attribute_key' => $sanitizedKey,
                                    'attribute_value' => $sanitizedValue,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }
                    
                    if (!empty($attributesToCreate)) {
                        AccountAttribute::insert($attributesToCreate);
                    }
                }

                // Handle image deletions
                // Delete all images NOT in keep_images array
                // If keep_images is empty or not provided, delete all existing images
                $currentImages = $account->images()->pluck('id')->toArray();
                $keepImages = $request->has('keep_images') && is_array($request->keep_images) 
                    ? array_map('intval', $request->keep_images) 
                    : [];
                
                // Find images to delete (images not in keep_images)
                $imagesToDelete = array_diff($currentImages, $keepImages);
                
                foreach ($imagesToDelete as $imageId) {
                    $image = AccountImage::where('id', $imageId)
                        ->where('account_id', $account->id)
                        ->first();
                    
                    if ($image) {
                        // Delete file from storage
                        if (Storage::disk('public')->exists($image->url)) {
                            Storage::disk('public')->delete($image->url);
                        }
                        // Delete database record
                        $image->delete();
                    }
                }
                
                // Validate that at least one image remains (either kept existing or new uploads)
                $remainingExistingCount = count($keepImages);
                $newImagesCount = $request->hasFile('images') ? count($request->file('images')) : 0;
                $totalImagesAfterUpdate = $remainingExistingCount + $newImagesCount;
                
                if ($totalImagesAfterUpdate < 1) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'At least one image is required. You cannot delete all images.'
                        ], 422);
                    }
                    return redirect()->back()
                        ->with('error', 'At least one image is required. You cannot delete all images.')
                        ->withInput();
                }

                // Handle new image uploads
                if ($request->hasFile('images')) {
                    $currentImageCount = $account->images()->count();
                    $images = $request->file('images');
                    
                    // Check total image count (existing + new)
                    if ($currentImageCount + count($images) > 10) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Maximum 10 images allowed. You currently have ' . $currentImageCount . ' images.'
                            ], 422);
                        }
                        return redirect()->back()
                            ->with('error', 'Maximum 10 images allowed. You currently have ' . $currentImageCount . ' images.')
                            ->withInput();
                    }
                    
                    $imagesToCreate = [];
                    
                    foreach ($images as $image) {
                        if ($image->isValid() && $image->getMimeType() && strpos($image->getMimeType(), 'image/') === 0) {
                            $path = $image->store('account_images', 'public');
                            
                            $imagesToCreate[] = [
                                'account_id' => $account->id,
                                'url' => $path,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    
                    if (!empty($imagesToCreate)) {
                        AccountImage::insert($imagesToCreate);
                    }
                }
                
                // Reload account with all relationships
                $account->load(['game', 'seller', 'attributes', 'images']);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Account updated successfully',
                        'account' => $account
                    ]);
                }

                return redirect()->route('account.listed-accounts')
                    ->with('success', 'Account updated successfully!');
            }, 5);
        } catch (\Exception $e) {
            $errorMessage = 'Failed to update account. Please try again.';
            if (str_contains($e->getMessage(), 'Column not found')) {
                $errorMessage = 'Database configuration error. Please contact support.';
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }
    
    public function updateAccountStatus(Request $request, $id)
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller profile not found.'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,disabled,pending,sold,cancelled',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $account = AccountForSale::where('id', $id)
                ->where('seller_id', $seller->id)
                ->firstOrFail();
            
            $account->status = $request->status;
            $account->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Account status updated successfully.',
                'status' => $account->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function deleteAccount($id)
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        if (!$seller) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller profile not found.'
                ], 404);
            }
            return redirect()->route('account.listed-accounts')
                ->with('error', 'Seller profile not found.');
        }
        
        try {
            return \DB::transaction(function () use ($id, $seller) {
                $account = AccountForSale::with(['images'])
                    ->where('id', $id)
                    ->where('seller_id', $seller->id)
                    ->firstOrFail();

                // Delete all associated image files (ignore missing files)
                foreach ($account->images as $image) {
                    try {
                        $filePath = public_path('storage/' . $image->url);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    } catch (\Throwable $t) {
                        // Ignore storage errors to not block DB deletion
                    }
                }

                // Explicitly delete related records to avoid constraint issues
                $account->attributes()->delete();
                $account->images()->delete();

                // Finally delete the account
                $account->delete();

                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Account deleted successfully.'
                    ]);
                }

                return redirect()->route('account.listed-accounts')
                    ->with('success', 'Account deleted successfully.');
            }, 3);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete account: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('account.listed-accounts')
                ->with('error', 'Failed to delete account.');
        }
    }
    
    public function settings()
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        // Get wallet balance (sellers only)
        $walletBalance = $seller ? ($seller->wallet ?? 0) : 0;
        
        // Get user sessions
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) use ($user) {
                $agent = $this->parseUserAgent($session->user_agent);
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'platform' => $agent['platform'],
                    'browser' => $agent['browser'],
                    'device_icon' => $agent['icon'],
                    'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'is_current' => $session->id === session()->getId(),
                ];
            });
        
        return view('dashboard.settings', compact('walletBalance', 'user', 'seller', 'sessions'));
    }

    private function parseUserAgent($userAgent)
    {
        $platform = 'Unknown';
        $browser = 'Unknown';
        $icon = 'fa-solid fa-desktop';

        // Detect platform
        if (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
            $icon = 'fa-brands fa-windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            $platform = 'macOS';
            $icon = 'fa-brands fa-apple';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
            $icon = 'fa-brands fa-linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $platform = 'Android';
            $icon = 'fa-brands fa-android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $platform = 'iOS';
            $icon = 'fa-brands fa-apple';
        }

        // Detect browser
        if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'icon' => $icon,
        ];
    }

    public function logoutAllDevices()
    {
        $user = Auth::user();
        $currentSessionId = session()->getId();
        
        // Delete all sessions except current
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();
        
        return back()->with('success', 'Successfully logged out from all other devices.');
    }

    public function logoutSession($sessionId)
    {
        $user = Auth::user();
        
        // Prevent logging out current session
        if ($sessionId === session()->getId()) {
            return back()->with('error', 'Cannot logout from current session.');
        }
        
        // Delete the specific session
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->delete();
        
        return back()->with('success', 'Session logged out successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $seller = $user->seller;

        $request->validate([
            'name' => 'required|string|max:255',
            'pfp' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Update name for all users
        $user->update([
            'name' => $request->name,
        ]);

        // Handle profile picture upload (sellers only)
        if ($seller && $request->hasFile('pfp')) {
            $file = $request->file('pfp');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            
            $seller->update([
                'pfp' => '/storage/' . $path,
            ]);
        }

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Normalize a seller-entered DZD price to integer (stored as-is, no cents conversion).
     * Removes separators like commas, spaces, and decimals.
     * Seller enters 20 -> stores 20, seller enters 1,000 -> stores 1000.
     * Returns null if it cannot parse to a valid non-negative number.
     */
    protected function normalizePriceToCents($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        $raw = trim((string)$value);
        // Remove all separators and spaces
        $raw = str_replace([',', ' ', '.', "\u{00A0}"], '', $raw);

        // Keep only digits
        $digitsOnly = preg_replace('/[^0-9]/', '', $raw);
        
        if ($digitsOnly === '' || !ctype_digit($digitsOnly)) {
            return null;
        }

        $result = (int) $digitsOnly;
        return $result >= 0 ? $result : null;
    }
}

