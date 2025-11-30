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

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }
    
    public function orders()
    {
        return view('dashboard.orders');
    }
    
    public function chat()
    {
        // Optionally pre-select a conversation using a value stored in session
        $activeConversationId = session()->pull('active_chat_conversation_id', null);
        return view('dashboard.chat', compact('activeConversationId'));
    }
    
    public function wallet()
    {
        return view('dashboard.wallet');
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
            'price_dzd' => 'required|numeric|min:0',
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
            // Use database transaction to ensure data integrity
            return DB::transaction(function () use ($request) {
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
                    'price_dzd' => (int)($request->price_dzd * 100), // Convert to cents
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
                            // Store the image
                            $path = $image->store('account_images', 'public');
                            
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
        
        return view('dashboard.edit-account', compact('account'));
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
            'price_dzd' => 'required|numeric|min:0',
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
            return DB::transaction(function () use ($request, $id, $seller) {
                // Fetch the account
                $account = AccountForSale::where('id', $id)
                    ->where('seller_id', $seller->id)
                    ->firstOrFail();

                // Update the account
                $account->update([
                    'title' => strip_tags($request->title),
                    'description' => strip_tags($request->description),
                    'price_dzd' => (int)($request->price_dzd * 100), // Convert to cents
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
            $account = AccountForSale::with('images')->where('id', $id)
                ->where('seller_id', $seller->id)
                ->firstOrFail();
            
            // Delete all associated images from storage
            foreach ($account->images as $image) {
                if (Storage::disk('public')->exists($image->url)) {
                    Storage::disk('public')->delete($image->url);
                }
            }
            
            // Delete the account (cascade will handle related records)
            $account->delete();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account deleted successfully.'
                ]);
            }
            
            return redirect()->route('account.listed-accounts')
                ->with('success', 'Account deleted successfully.');
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
        return view('dashboard.settings');
    }
}

