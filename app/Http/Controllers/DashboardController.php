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
    
    public function chat(Request $request)
    {
        // Optionally pre-select a conversation using a value stored in session
        $activeConversationId = session()->pull('active_chat_conversation_id', null);

        // ...or via ?conversation=ID, but only if the caller belongs to that thread.
        $requested = $request->query('conversation');

        if (is_string($requested) && ctype_digit($requested)) {
            $user = Auth::user();
            $sellerId = $user->seller?->id;

            $belongsToUser = \App\Models\Conversation::query()
                ->whereKey((int) $requested)
                ->where(function ($query) use ($user, $sellerId) {
                    $query->where('buyer_id', $user->id);

                    if ($sellerId) {
                        $query->orWhere('seller_id', $sellerId);
                    }
                })
                ->exists();

            if ($belongsToUser) {
                $activeConversationId = (int) $requested;
            }
        }

        return view('dashboard.chat', compact('activeConversationId'));
    }
    
    public function wallet()
    {
        $user = Auth::user();
        $seller = $user->seller;
        
        $walletBalance = 0;
        $availableToWithdraw = 0;
        $withdrawals = collect();
        $transactions = collect();
        
        if ($seller) {
            // Seller wallet: show balance and completed/delivered orders (earnings)
            $walletBalance = $seller->wallet ?? 0;
            $withdrawals = $seller->withdrawals()
                ->latest()
                ->limit(10)
                ->get();
            $pendingWithdrawalAmount = (float) $seller->withdrawals()
                ->where('status', 'pending')
                ->sum('amount');
            $availableToWithdraw = max(0, (float) $walletBalance - $pendingWithdrawalAmount);
            
            // Get completed orders where seller received payment (delivery confirmed)
            $transactions = \App\Models\Order::where('seller_id', $seller->id)
                ->where('status', 'completed')
                ->where('delivery_status', 'delivered')
                ->with(['buyer', 'account', 'sofizpayCibTransaction'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($order) {
                    $baseAmount = (float) $order->amount_dzd;
                    $processingFee = round($baseAmount * 0.039, 2);
                    $payout = $baseAmount - $processingFee;
                    
                    return [
                        'id' => $order->id,
                        'order_id' => $order->id,
                        'transaction_id' => optional($order->sofizpayCibTransaction)->transaction_id
                            ?? optional($order->sofizpayCibTransaction)->cib_order_number
                            ?? $order->chargily_payment_id
                            ?? 'N/A',
                        'payment_method' => $order->sofizpay_cib_transaction_id ? 'SofizPay' : 'Chargily',
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
                ->with(['seller.user', 'account', 'sofizpayCibTransaction'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($order) {
                    $baseAmount = (float) $order->amount_dzd;
                    $processingFee = round($baseAmount * 0.039, 2);
                    $totalPaid = $baseAmount + $processingFee;
                    
                    return [
                        'id' => $order->id,
                        'order_id' => $order->id,
                        'transaction_id' => optional($order->sofizpayCibTransaction)->transaction_id
                            ?? optional($order->sofizpayCibTransaction)->cib_order_number
                            ?? $order->chargily_payment_id
                            ?? 'N/A',
                        'payment_method' => $order->sofizpay_cib_transaction_id ? 'SofizPay' : 'Chargily',
                        'status' => $order->delivery_status === 'delivered' ? 'Delivered' : 'Pending Delivery',
                        'amount' => $totalPaid,
                        'type' => 'purchase',
                        'updated_at' => $order->updated_at,
                        'seller_name' => $order->seller && $order->seller->user ? $order->seller->user->name : 'Unknown',
                        'account_title' => $order->account->title ?? 'Account #' . $order->account_id,
                    ];
                });
        }
        
        return view('dashboard.wallet', compact(
            'walletBalance',
            'availableToWithdraw',
            'withdrawals',
            'transactions',
            'seller'
        ));
    }
    
    public function library()
    {
        return view('dashboard.library');
    }
    
    public function listedAccounts()
    {
        $user = Auth::user();
        $seller = $user->ensureSellerProfile();
        
        // Get all accounts for this seller with relationships
        $accounts = AccountForSale::where('seller_id', $seller->id)
            ->with(['game', 'attributes', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('dashboard.listed-accounts', compact('accounts'));
    }
    
    public function createAccount()
    {
        $games = \App\Models\Game::query()->active()->get();
        $mlbbGame = \App\Models\Game::query()->active()->where('slug', 'mlbb')->first();
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
            'attributes.highlighted_recalls' => 'nullable|string|max:5000',
            'attributes.highlighted_emotes' => 'nullable|string|max:5000',
            'attributes.*' => 'nullable|string|max:255',
            // Gallery photos (max 10). listing_cover is the generated poster, stored separately as primary.
            'images' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max per file
            'listing_cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ], [
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, and WEBP images are allowed.',
            'images.*.max' => 'Each image must not exceed 10MB.',
            'listing_cover.image' => 'The listing poster must be an image.',
            'listing_cover.mimes' => 'The listing poster must be JPEG, PNG, JPG, or WEBP.',
            'listing_cover.max' => 'The listing poster must not exceed 10MB.',
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
                $seller = $user->ensureSellerProfile();
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
                $highlightedSkinsLookup = strip_tags(trim((string) $request->input('highlighted_skins_lookup', '')));
                
                if (!empty($attributes) && is_array($attributes)) {
                    $attributesToCreate = [];
                    
                    foreach ($attributes as $key => $value) {
                        if (! is_string($key) || ! is_scalar($value)) {
                            continue;
                        }

                        $valueString = (string) $value;
                        $sanitizedKey = substr(strip_tags(trim($key)), 0, 255);
                        if ($sanitizedKey === '') {
                            continue;
                        }

                        $maxLength = in_array($sanitizedKey, ['highlighted_recalls', 'highlighted_emotes'], true) ? 5000 : 255;
                        $sanitizedValue = substr(strip_tags(trim($valueString)), 0, $maxLength);

                        if ($sanitizedKey === 'highlighted_skins') {
                            $sanitizedValue = SkinsHelper::mergeHighlightedSkinIds($sanitizedValue, $highlightedSkinsLookup);
                        } elseif ($sanitizedValue === '') {
                            continue;
                        }

                        if ($sanitizedValue !== '') {
                            $attributesToCreate[] = [
                                'account_id' => $account->id,
                                'attribute_key' => $sanitizedKey,
                                'attribute_value' => $sanitizedValue,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    
                    // Bulk insert attributes for better performance
                    if (!empty($attributesToCreate)) {
                        AccountAttribute::insert($attributesToCreate);
                    }
                }

                // Handle image uploads: optional generated poster as cover, then gallery photos (max 10)
                $galleryFiles = $request->file('images', []);
                if (!is_array($galleryFiles)) {
                    $galleryFiles = $galleryFiles ? [$galleryFiles] : [];
                }
                $hasCover = $request->hasFile('listing_cover');
                $hasGallery = $request->hasFile('images') && count($galleryFiles) > 0;

                if (!$hasCover && !$hasGallery) {
                    \Log::warning('createAccount: no images detected in request', [
                        'has_files' => $request->hasFile('images'),
                        'has_cover' => $hasCover,
                        'files_count' => count($galleryFiles),
                        'keys' => array_keys($request->all()),
                    ]);
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

                if (count($galleryFiles) > 10) {
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

                if ($hasCover) {
                    $coverRow = $this->prepareAccountImageRow($request->file('listing_cover'), $account->id, true);
                    if ($coverRow) {
                        $imagesToCreate[] = $coverRow;
                    }
                }

                foreach ($galleryFiles as $image) {
                    $row = $this->prepareAccountImageRow($image, $account->id, false);
                    if ($row) {
                        $imagesToCreate[] = $row;
                    }
                }

                if (!empty($imagesToCreate)) {
                    AccountImage::insert($imagesToCreate);
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
        $seller = $user->ensureSellerProfile();

        $account = AccountForSale::with(['game', 'attributes', 'images'])
            ->where('id', $id)
            ->where('seller_id', $seller->id)
            ->firstOrFail();

        $attributesMap = [];
        foreach ($account->attributes as $attr) {
            $attributesMap[$attr->attribute_key] = $attr->attribute_value;
        }

        $games = \App\Models\Game::query()->active()->get();
        $mlbbGame = \App\Models\Game::query()->active()->where('slug', 'mlbb')->first();
        $mlbbId = $mlbbGame ? $mlbbGame->id : null;

        return view('dashboard.create-account', compact('account', 'attributesMap', 'games', 'mlbbId'));
    }
    
    public function updateAccount(Request $request, $id)
    {
        $user = Auth::user();
        $seller = $user->ensureSellerProfile();
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // Allow raw input; we'll normalize to cents safely
            'price_dzd' => 'required|string',
            'status' => 'required|in:available,disabled,pending,sold,cancelled',
            'attributes' => 'nullable|array',
            'attributes.highlighted_recalls' => 'nullable|string|max:5000',
            'attributes.highlighted_emotes' => 'nullable|string|max:5000',
            'attributes.*' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB max per file
            'listing_cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:account_images,id',
        ], [
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, and WEBP images are allowed.',
            'images.*.max' => 'Each image must not exceed 10MB.',
            'listing_cover.image' => 'The listing poster must be an image.',
            'listing_cover.mimes' => 'The listing poster must be JPEG, PNG, JPG, or WEBP.',
            'listing_cover.max' => 'The listing poster must not exceed 10MB.',
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
                $highlightedSkinsLookup = strip_tags(trim((string) $request->input('highlighted_skins_lookup', '')));
                
                // Delete existing attributes
                $account->attributes()->delete();
                
                // Create new attributes
                if (!empty($attributes) && is_array($attributes)) {
                    $attributesToCreate = [];
                    
                    foreach ($attributes as $key => $value) {
                        if (! is_string($key) || ! is_scalar($value)) {
                            continue;
                        }

                        $valueString = (string) $value;
                        $sanitizedKey = substr(strip_tags(trim($key)), 0, 255);
                        if ($sanitizedKey === '') {
                            continue;
                        }

                        $maxLength = in_array($sanitizedKey, ['highlighted_recalls', 'highlighted_emotes'], true) ? 5000 : 255;
                        $sanitizedValue = substr(strip_tags(trim($valueString)), 0, $maxLength);

                        if ($sanitizedKey === 'highlighted_skins') {
                            $sanitizedValue = SkinsHelper::mergeHighlightedSkinIds($sanitizedValue, $highlightedSkinsLookup);
                        } elseif ($sanitizedValue === '') {
                            continue;
                        }

                        if ($sanitizedValue !== '') {
                            $attributesToCreate[] = [
                                'account_id' => $account->id,
                                'attribute_key' => $sanitizedKey,
                                'attribute_value' => $sanitizedValue,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    
                    if (!empty($attributesToCreate)) {
                        AccountAttribute::insert($attributesToCreate);
                    }
                }

                // Handle image deletions (never remove the generated listing poster)
                $currentImages = $account->images()->where('is_cover', false)->pluck('id')->toArray();
                $keepImages = $request->has('keep_images') && is_array($request->keep_images)
                    ? array_map('intval', $request->keep_images)
                    : [];

                $imagesToDelete = array_diff($currentImages, $keepImages);

                foreach ($imagesToDelete as $imageId) {
                    $image = AccountImage::where('id', $imageId)
                        ->where('account_id', $account->id)
                        ->where('is_cover', false)
                        ->first();

                    if ($image) {
                        if (Storage::disk('public')->exists($image->url)) {
                            Storage::disk('public')->delete($image->url);
                        }
                        $image->delete();
                    }
                }

                $remainingExistingCount = $account->images()->where('is_cover', false)->count();
                $newImagesCount = $request->hasFile('images') ? count($request->file('images')) : 0;
                $coverCount = $account->images()->where('is_cover', true)->count();
                $totalImagesAfterUpdate = $remainingExistingCount + $newImagesCount + $coverCount;

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

                if ($request->hasFile('images')) {
                    $currentGalleryCount = $account->images()->where('is_cover', false)->count();
                    $images = $request->file('images');

                    if ($currentGalleryCount + count($images) > 10) {
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Maximum 10 images allowed. You currently have ' . $currentGalleryCount . ' images.'
                            ], 422);
                        }
                        return redirect()->back()
                            ->with('error', 'Maximum 10 images allowed. You currently have ' . $currentGalleryCount . ' images.')
                            ->withInput();
                    }

                    $imagesToCreate = [];

                    foreach ($images as $image) {
                        $row = $this->prepareAccountImageRow($image, $account->id, false);
                        if ($row) {
                            $imagesToCreate[] = $row;
                        }
                    }

                    if (!empty($imagesToCreate)) {
                        AccountImage::insert($imagesToCreate);
                    }
                }

                if ($request->hasFile('listing_cover')) {
                    $oldCovers = $account->images()->where('is_cover', true)->get();
                    foreach ($oldCovers as $oldCover) {
                        if (Storage::disk('public')->exists($oldCover->url)) {
                            Storage::disk('public')->delete($oldCover->url);
                        }
                        $oldCover->delete();
                    }

                    $coverRow = $this->prepareAccountImageRow($request->file('listing_cover'), $account->id, true);
                    if ($coverRow) {
                        AccountImage::insert([$coverRow]);
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
        $seller = $user->ensureSellerProfile();
        
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
        $seller = $user->ensureSellerProfile();
        
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

    private function prepareAccountImageRow($image, int $accountId, bool $isCover): ?array
    {
        if (!$image || !$image->isValid() || !$image->getMimeType() || !str_starts_with((string) $image->getMimeType(), 'image/')) {
            return null;
        }

        $extension = $image->getClientOriginalExtension() ?: 'png';
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $image->move(public_path('storage/account_images'), $filename);

        return [
            'account_id' => $accountId,
            'url' => 'account_images/' . $filename,
            'is_cover' => $isCover ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

