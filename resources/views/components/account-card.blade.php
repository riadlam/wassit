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
    .account-card-image {
        position: relative;
        width: 100%;
        aspect-ratio: 681 / 1024;
        flex-shrink: 0;
        overflow: hidden;
        border-radius: 0.5rem;
        border: 1px solid #2d2c31;
        background-color: #0e1015;
    }
    .account-card-image > img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center center;
    }
    .account-card-image-empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        font-size: 0.875rem;
    }
    @media (max-width: 639px) {
        .account-card .account-card-body {
            padding: 0.75rem 0.7rem 0.85rem !important;
            gap: 0.65rem !important;
        }
        .account-card .account-card-price {
            font-size: 1.25rem !important;
        }
        .account-card .account-buy-btn {
            padding: 0.35rem 0.65rem;
            font-size: 0.7rem;
        }
        .account-card .account-card-seller {
            padding-left: 0.7rem !important;
            padding-right: 0.7rem !important;
            margin-bottom: 8px !important;
        }
        .account-card .account-card-attrs {
            height: 48px !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
    }
</style>

@php
    use App\Support\CollectionTierHelper;
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
    $coverImage = $accountImages->firstWhere('is_cover', true) ?? $accountImages->first();
    $galleryCount = $accountImages->where('is_cover', false)->count();
    $hasCover = $accountImages->contains(fn ($image) => (bool) $image->is_cover);
    $imageCount = $accountImages->count();
    $overlayCount = $hasCover ? $galleryCount : $imageCount;
    $collectionTier = $accountAttributes['collection_tier'] ?? null;
    // Translate collection tier label per locale while preserving original for image file lookup
    $collectionTierLabel = $collectionTier;
    $tierTranslationMap = [
        'Expert Collector' => __('messages.expert_collector'),
        'Renowned Collector' => __('messages.renowned_collector'),
        'Exalted Collector' => __('messages.exalted_collector'),
        'Mega Collector' => __('messages.mega_collector'),
        'World Collector' => __('messages.world_collector'),
    ];
    if (!empty($collectionTier) && isset($tierTranslationMap[$collectionTier])) {
        $collectionTierLabel = $tierTranslationMap[$collectionTier];
    }
    $skinsCount = $accountAttributes['skins_count'] ?? null;
    $tierImage = CollectionTierHelper::imageUrl($collectionTier);
    
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
    
    // Seller ranks are assigned by an administrator.
    $sellerBadges = $seller?->rankBadges() ?? [];
    
@endphp

<a href="/mobile-legends/accounts/{{ $account->id }}" class="account-card-hover account-card flex relative flex-col justify-between overflow-hidden rounded-xl h-full hover:shadow-xl transition-all duration-300 group" style="background-color: #0e1015; border: 1px solid #2d2c31;">
    <!-- Flash Sale Badge (Top Right) -->
    <div class="absolute z-10" style="top: 0.5rem; right: 0.5rem;">
        <div class="flex justify-center items-center py-1 w-7 h-7 text-xs font-semibold tracking-wide text-center uppercase rounded-lg" style="color: #fbbf24;">
            <i class="fa-solid fa-bolt"></i>
        </div>
    </div>

    <!-- Card Content -->
    <div class="account-card-body flex flex-col flex-1 justify-between px-4 py-4 space-y-4 sm:px-5">
        <!-- Collection Tier/Skins Section -->
        <div class="pt-1.5">
            <div class="flex items-center gap-x-2">
                @if($collectionTier || $skinsCount)
                    @if($tierImage)
                        <img src="{{ $tierImage }}" alt="{{ $collectionTier ?? 'Collection Tier' }}" class="object-contain" style="width: 33.6px; height: 33.6px;">
                    @endif
                    <div class="truncate">
                        <p class="font-semibold leading-6 truncate text-white" style="font-size: 0.85rem;">
                            @if($collectionTier)
                                {{ $collectionTierLabel }}
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
        <div class="account-card-image account-image-hover">
            @if($coverImage)
                <img src="{{ asset('storage/' . $coverImage->url) }}" alt="Account Image" loading="lazy">
                @if($overlayCount > 0 && ($hasCover ? $galleryCount > 0 : $imageCount > 1))
                    <span class="inline-flex items-center justify-center overflow-hidden font-medium whitespace-nowrap py-1.5 px-2 text-xs rounded-md absolute right-2 bottom-2 backdrop-blur-md" style="background-color: rgba(27, 26, 30, 0.8); color: #ffffff; border: 1px solid #2d2c31;">
                        <i class="mr-2 fas fa-images"></i> {{ $overlayCount }}+
                    </span>
                @endif
            @else
                <div class="account-card-image-empty">No Image</div>
            @endif
        </div>

        <!-- Account Attributes - Individual Items with Borders -->
        <div class="account-card-attrs attributes-scroll overflow-y-auto overflow-x-hidden rounded-md flex flex-wrap gap-1.5" style="height: 60px; margin-left: 5px; margin-right: 5px; padding: 0.5rem; background-color: rgba(27, 26, 30, 0.5); border: 1px solid rgba(255, 255, 255, 0.05);">
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
                    $attributesList[] = $collectionTierLabel;
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
                <span class="account-card-price text-3xl font-bold tracking-tight text-transparent bg-clip-text" style="background: linear-gradient(to left, #ffffff, rgba(255, 255, 255, 0.6)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    {{ number_format($account->price_dzd, 0, '.', '') }}
                </span>
                <span class="text-sm font-semibold leading-6" style="color: rgba(255, 255, 255, 0.6);">DA</span>
            </div>
            <button type="button" class="account-buy-btn inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-2 px-4 text-sm rounded-full shrink-0" data-account-id="{{ $account->id }}">
                <span class="buy-btn-text truncate">Buy Now</span>
                <i class="buy-btn-loading ml-1 hidden" style="display: none;">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </i>
                <i class="ml-1 fa-solid fa-chevron-right buy-btn-icon"></i>
            </button>
        </div>
    </div>

    <!-- Divider -->
    <div class="h-px w-full" style="background: linear-gradient(90deg, rgba(45, 44, 49, 0.1), #2d2c31, rgba(45, 44, 49, 0.1));"></div>

    <!-- Seller Info (Bottom Section) -->
    <button class="account-card-seller flex gap-x-2 justify-between items-center px-5 py-3 rounded-b-xl border-t group-hover:bg-opacity-50" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31; margin-bottom: 15px;">
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

