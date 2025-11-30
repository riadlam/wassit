<style>
    .account-card-hover:hover {
        border-color: rgba(255, 255, 255, 0.2) !important;
    }
    .account-image-hover:hover {
        background-color: #0e1015 !important;
    }
    .attributes-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .attributes-scroll::-webkit-scrollbar-track {
        background: #000000;
        border-radius: 3px;
    }
    .attributes-scroll::-webkit-scrollbar-thumb {
        background: #4a4a4a;
        border-radius: 3px;
    }
    .attributes-scroll::-webkit-scrollbar-thumb:hover {
        background: #5a5a5a;
    }
    .attributes-scroll {
        scrollbar-width: thin;
        scrollbar-color: #4a4a4a #000000;
    }
    .verified-badge {
        position: relative;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .verified-badge:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5) !important;
    }
</style>

@php
    use Illuminate\Support\Facades\Storage;
    $seller = $account->seller;
    $user = $seller->user ?? null;
    
    // Calculate sold accounts from completed orders
    $soldCount = 0;
    if ($seller) {
        $soldCount = $seller->orders()->where('status', 'completed')->count();
    }
    
    // Calculate rating percentage (assuming max rating is 5)
    $ratingPercentage = 0;
    if ($seller && $seller->rating > 0) {
        $ratingPercentage = round(($seller->rating / 5) * 100);
    }
    
    $accountAttributes = $account->attributes->pluck('attribute_value', 'attribute_key')->toArray();
    $accountImages = $account->images;
    $imageCount = $accountImages->count();
    $collectionTier = $accountAttributes['collection_tier'] ?? null;
    $skinsCount = $accountAttributes['skins_count'] ?? null;
    
    // Determine profile picture URL
    $sellerPfp = asset('storage/examplepfp.webp'); // Default fallback
    if ($seller && !empty($seller->pfp)) {
        // Check if pfp is a full URL or a storage path
        if (filter_var($seller->pfp, FILTER_VALIDATE_URL)) {
            // It's a full URL, use it directly
            $sellerPfp = $seller->pfp;
        } else {
            // It's a storage path, check if file exists
            if (Storage::disk('public')->exists($seller->pfp)) {
                $sellerPfp = asset('storage/' . $seller->pfp);
            }
        }
    }
    
    // Determine seller badges
    $sellerBadges = [];
    if ($seller) {
        // Verified Seller - ID + phone verified (verified == 1)
        if ($seller->verified == 1) {
            $sellerBadges[] = [
                'type' => 'verified',
                'label' => 'Verified Seller',
                'icon' => 'fa-check',
                'color' => '#3b82f6', // Blue
                'gradient' => 'linear-gradient(135deg, #60a5fa 0%, #3b82f6 30%, #2563eb 70%, #1d4ed8 100%)',
                'shadow' => '0 2px 4px rgba(59, 130, 246, 0.4)',
                'border' => '1.5px solid rgba(96, 165, 250, 0.4)',
            ];
        }
        
        // Trusted Seller - 20+ successful sales
        if ($soldCount >= 20) {
            $sellerBadges[] = [
                'type' => 'trusted',
                'label' => 'Trusted Seller',
                'icon' => 'fa-shield-halved',
                'color' => '#3b82f6', // Blue
                'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%)',
                'shadow' => '0 2px 4px rgba(59, 130, 246, 0.4)',
            ];
        }
        
        // Fast Responder - replies in under 10 minutes (leave empty for now)
        // Will be implemented later
        
        // Power Seller - 50+ sales
        if ($soldCount >= 50) {
            $sellerBadges[] = [
                'type' => 'power',
                'label' => 'Power Seller',
                'icon' => 'fa-crown',
                'color' => '#fbbf24', // Yellow/Gold
                'gradient' => 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%)',
                'shadow' => '0 2px 4px rgba(251, 191, 36, 0.4)',
            ];
        }
    }
    
    // Determine badge type for the main badge icon (for backward compatibility)
    // Use the highest priority badge or default
    $badgeType = 'silver'; // Default
    if (!empty($sellerBadges)) {
        // Priority: Power Seller > Verified > Trusted
        $hasPower = collect($sellerBadges)->contains('type', 'power');
        $hasVerified = collect($sellerBadges)->contains('type', 'verified');
        $hasTrusted = collect($sellerBadges)->contains('type', 'trusted');
        
        if ($hasPower) {
            $badgeType = 'yellow';
        } elseif ($hasVerified) {
            $badgeType = 'green';
        } elseif ($hasTrusted) {
            $badgeType = 'blue';
        }
    }
@endphp

<a href="/mobile-legends/accounts/{{ $account->id }}" class="account-card-hover flex relative flex-col justify-between overflow-hidden rounded-xl h-full hover:shadow-xl transition-all duration-300 group" style="background-color: #0e1015; border: 1px solid #2d2c31;">
    <!-- Flash Sale Badge (Top Right) -->
    <div class="absolute z-10" style="top: 0.5rem; right: 0.5rem;">
        <div class="flex justify-center items-center py-1 w-7 h-7 text-xs font-semibold tracking-wide text-center uppercase rounded-lg" style="color: #fbbf24;">
            <i class="fa-solid fa-bolt"></i>
        </div>
    </div>

    <!-- Card Content -->
    <div class="flex flex-col flex-1 justify-between px-4 py-4 space-y-4 sm:px-5">
        <!-- Collection Tier/Skins Section -->
        <div class="pt-1.5">
            <div class="flex items-center gap-x-2">
                @if($collectionTier || $skinsCount)
                    @php
                        // Map collection tier to image filename
                        $tierImage = null;
                        if ($collectionTier) {
                            // Collection tier values match the filename exactly (with spaces)
                            $tierImagePath = 'mlbb_skins_rank/' . $collectionTier . '.webp';
                            if (Storage::disk('public')->exists($tierImagePath)) {
                                $tierImage = asset('storage/' . $tierImagePath);
                            }
                        }
                    @endphp
                    @if($tierImage)
                        <img src="{{ $tierImage }}" alt="{{ $collectionTier ?? 'Collection Tier' }}" class="object-contain" style="width: 33.6px; height: 33.6px;">
                    @endif
                    <div class="truncate">
                        <p class="font-semibold leading-6 truncate text-white" style="font-size: 0.85rem;">
                            @if($collectionTier)
                                {{ $collectionTier }}
                            @endif
                            @if($collectionTier && $skinsCount)
                                <span class="text-gray-400"> · </span>
                            @endif
                            @if($skinsCount)
                                {{ number_format((int)$skinsCount) }} {{ __('messages.skins') }}
                            @endif
                        </p>
                    </div>
                @else
                    <div class="truncate">
                        <p class="font-medium leading-6 truncate text-white" style="font-size: 0.85rem;">
                            Account Details
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Description (Fixed Height) -->
        <div class="text-sm line-clamp-2 break-all" style="min-height: 40px; color: rgba(255, 255, 255, 0.8); margin-top: 5px; margin-bottom: 10px;">
            {{ strlen($account->title) > 100 ? substr($account->title, 0, 100) . '...' : $account->title }}
        </div>

        <!-- Account Image -->
        <div style="margin-bottom: 15px;">
            <div class="relative overflow-hidden rounded-lg account-image-hover" style="height: 140px; border: 1px solid #2d2c31;">
                @if($imageCount > 0)
                    <button class="w-full h-full cursor-zoom-in">
                        <span class="sr-only">View Images</span>
                        <img src="{{ asset('storage/' . $accountImages->first()->url) }}" alt="Account Image" class="object-cover w-full h-full" loading="lazy">
                    </button>
                    @if($imageCount > 1)
                        <button type="button" class="inline-flex items-center justify-center transition-colors overflow-hidden font-medium whitespace-nowrap py-1.5 px-2 text-xs rounded-md absolute right-2 bottom-2 backdrop-blur-md" style="background-color: rgba(27, 26, 30, 0.8); color: #ffffff; border: 1px solid #2d2c31;">
                            <i class="mr-2 fas fa-images"></i> {{ $imageCount }}+
                        </button>
                    @endif
                @else
                    <div class="w-full h-full flex items-center justify-center" style="background-color: #0e1015;">
                        <span class="text-gray-500 text-sm">No Image</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Account Attributes - Individual Items with Borders -->
        <div class="attributes-scroll overflow-y-auto overflow-x-hidden rounded-md flex flex-wrap gap-1.5" style="height: 60px; margin-left: 5px; margin-right: 5px; padding: 0.5rem; background-color: rgba(27, 26, 30, 0.5); border: 1px solid rgba(255, 255, 255, 0.05);">
            @php
                $attributesList = [];
                if (isset($accountAttributes['skins_count'])) {
                    $attributesList[] = $accountAttributes['skins_count'] . ' Skins';
                }
                if (isset($accountAttributes['heroes_count'])) {
                    $attributesList[] = $accountAttributes['heroes_count'] . ' Heroes';
                }
                if (isset($accountAttributes['diamonds'])) {
                    $diamonds = number_format((float)str_replace(' ', '', $accountAttributes['diamonds']), 0, '.', ' ');
                    $attributesList[] = $diamonds . ' Diamonds';
                }
                if (isset($accountAttributes['bp'])) {
                    $bp = number_format((float)str_replace(' ', '', $accountAttributes['bp']), 0, '.', ' ');
                    $attributesList[] = $bp . ' BP';
                }
                if (isset($accountAttributes['level'])) {
                    $attributesList[] = 'Level ' . $accountAttributes['level'];
                }
                if (isset($accountAttributes['emblems_count']) || isset($accountAttributes['max_emblems'])) {
                    $attributesList[] = 'Full Emblem';
                }
                if (isset($accountAttributes['collection_tier'])) {
                    $attributesList[] = $accountAttributes['collection_tier'];
                }
            @endphp
            @foreach($attributesList as $attribute)
                <span class="inline-block px-2 py-0.5 text-xs whitespace-nowrap" style="color: rgba(255, 255, 255, 0.7); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 6px;">
                    {{ $attribute }}
                </span>
            @endforeach
        </div>

        <!-- Small Divider -->
        <div class="h-px w-full" style="background: linear-gradient(90deg, rgba(45, 44, 49, 0.1), #2d2c31, rgba(45, 44, 49, 0.1)); margin-top: 0.5rem; margin-bottom: 0.5rem;"></div>

        <!-- Price and Buy Button -->
        <div class="flex relative gap-1 justify-between items-center pt-1">
            <div class="flex gap-x-1 items-baseline truncate">
                <span class="text-3xl font-bold tracking-tight text-transparent bg-clip-text" style="background: linear-gradient(to left, #ffffff, rgba(255, 255, 255, 0.6)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    {{ number_format($account->price_dzd, 0, '.', '') }}
                </span>
                <span class="text-sm font-semibold leading-6" style="color: rgba(255, 255, 255, 0.6);">DZD</span>
            </div>
            <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-2 px-4 text-sm rounded-full shrink-0">
                <span class="truncate">Buy Now</span>
                <i class="ml-1 fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- Divider -->
    <div class="h-px w-full" style="background: linear-gradient(90deg, rgba(45, 44, 49, 0.1), #2d2c31, rgba(45, 44, 49, 0.1));"></div>

    <!-- Seller Info (Bottom Section) -->
    <button class="flex gap-x-2 justify-between items-center px-5 py-3 rounded-b-xl border-t group-hover:bg-opacity-50" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31; margin-bottom: 15px;">
        <div class="flex items-center truncate cursor-pointer">
            @if($user)
                <div class="relative block shrink-0 rounded-full border flex items-center justify-center" style="height: 36px; width: 36px; border-color: #252429; margin-bottom: 5px; margin-right: 5px;">
                    <img class="object-cover w-full h-full rounded-full" src="{{ $sellerPfp }}" alt="{{ $user->name }}" onerror="this.onerror=null; this.src='{{ asset('storage/examplepfp.webp') }}';">
                </div>
                <div class="cursor-default flex items-center truncate gap-x-1.5" data-state="closed" data-grace-area-trigger="">
                    <div class="truncate text-sm font-medium text-white">{{ strlen($user->name) > 8 ? substr(strtoupper($user->name), 0, 8) . '..' : strtoupper($user->name) }}</div>
                    @if(!empty($sellerBadges))
                        @foreach($sellerBadges as $badge)
                            <div class="flex items-center justify-center rounded-full shadow-lg shrink-0 {{ $badge['type'] === 'verified' ? 'verified-badge' : '' }}" 
                                 style="width: 15px; height: 15px; background: {{ $badge['gradient'] }}; border: {{ $badge['border'] ?? '1.5px solid rgba(255, 255, 255, 0.3)' }}; box-shadow: {{ $badge['shadow'] }}, inset 0 1px 0 rgba(255, 255, 255, 0.2);"
                                 title="{{ $badge['label'] }}">
                                <i class="fa-solid {{ $badge['icon'] }}" style="color: #ffffff; font-size: {{ $badge['type'] === 'verified' ? '0.6rem' : '0.55rem' }}; text-shadow: 0 1px 4px rgba(0, 0, 0, 0.7), 0 0 2px rgba(0, 0, 0, 0.5); {{ $badge['type'] === 'verified' ? 'filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.3));' : '' }}"></i>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>
        <div class="flex items-center shrink-0">
            <div class="flex items-center text-sm gap-x-2 text-xs" style="color: rgba(255, 255, 255, 0.6);">
                <span style="color: rgba(255, 255, 255, 0.6);">{{ number_format($soldCount) }} Sold</span>
                <div data-orientation="horizontal" role="separator" class="shrink-0 w-px" style="height: 1rem; background-color: rgba(255, 255, 255, 0.3);"></div>
                <div class="flex items-center" style="color: #10b981; margin-left: 5px;">
                    <i class="fa-solid fa-thumbs-up" style="color: #10b981; margin-right: 2px;"></i>
                    <span style="color: #10b981;">{{ $ratingPercentage }}%</span>
                </div>
            </div>
        </div>
    </button>
</a>

