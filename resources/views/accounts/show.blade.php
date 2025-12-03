@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
$accountAttributes = $account->attributes->pluck('attribute_value', 'attribute_key')->toArray();
$rank = $accountAttributes['rank'] ?? null;
$heroesCount = $accountAttributes['heroes_count'] ?? null;

// Map rank to tier image
$tierImage = null;
if ($rank) {
    $rankLower = strtolower($rank);
    if (strpos($rankLower, 'epic') !== false) {
        $tierImage = 'https://cdn.gameboost.com/games/mobile-legends/tiers/sm/epic.webp';
    } elseif (strpos($rankLower, 'legend') !== false) {
        $tierImage = 'https://cdn.gameboost.com/games/mobile-legends/tiers/sm/legend.webp';
    } elseif (strpos($rankLower, 'mythic') !== false || strpos($rankLower, 'mythical') !== false) {
        $tierImage = 'https://cdn.gameboost.com/games/mobile-legends/tiers/sm/mythicalhonor.webp';
    } elseif (strpos($rankLower, 'grandmaster') !== false) {
        $tierImage = 'https://cdn.gameboost.com/games/mobile-legends/tiers/sm/grandmaster.webp';
    } elseif (strpos($rankLower, 'master') !== false) {
        $tierImage = 'https://cdn.gameboost.com/games/mobile-legends/tiers/sm/master.webp';
    }
}

// Determine seller badges (same logic as account card)
$seller = $account->seller;
$user = $seller->user ?? null;

// Calculate sold accounts from completed orders
$soldCount = 0;
if ($seller) {
    $soldCount = $seller->orders()->where('status', 'completed')->count();
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

// Calculate rating percentage
$ratingPercentage = 0;
if ($seller && $seller->rating > 0) {
    $ratingPercentage = round(($seller->rating / 5) * 100);
}

// Calculate review count from reviews table
$reviewCount = 0;
if ($seller) {
    $reviewCount = $seller->reviews()->count();
}

// Determine profile picture URL
$sellerPfp = asset('storage/examplepfp.webp'); // Default fallback
if ($seller && !empty($seller->pfp)) {
    if (filter_var($seller->pfp, FILTER_VALIDATE_URL)) {
        $sellerPfp = $seller->pfp;
    } else {
        if (Storage::disk('public')->exists($seller->pfp)) {
            $sellerPfp = asset('storage/' . $seller->pfp);
        }
    }
}
@endphp

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        @php
            // Use game-specific banner if available, otherwise default
            $gameBanner = null;
            if ($game->slug === 'mlbb') {
                $gameBanner = Storage::url('home_page/mlbbbanner.webp');
            }
            $defaultBanner = Storage::url('home_page/degaultbanner.webp');
            $initialBanner = $gameBanner ?: $defaultBanner;
        @endphp
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('{{ $initialBanner }}'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen pt-16 sm:pt-16 pb-8" x-data="{ 
        messagingModalOpen: false,
        message: '',
        messageCount: 0,
        guidelinesChecked: false,
        showGuidelines: false,
        sendingContact: false,
        init() {
            this.$watch('message', (value) => {
                this.messageCount = value ? value.length : 0;
            });
            this.$watch('messagingModalOpen', (value) => {
                if (value) {
                    document.body.classList.add('modal-open');
                } else {
                    document.body.classList.remove('modal-open');
                }
            });
        },
        async handleContactSellerClick() {
            // First check if a conversation already exists for this buyer/seller/account
            try {
                const params = new URLSearchParams({
                    seller_id: '{{ $account->seller_id }}',
                    account_for_sale_id: '{{ $account->id }}',
                });
                const response = await fetch('{{ route("account.chat.find") }}' + '?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                });
                const data = await response.json();
                if (response.ok && data.conversation_id) {
                    // Conversation already exists, go straight to chat (id is in session)
                    window.location.href = '{{ route("account.chat") }}';
                    return;
                }
            } catch (e) {
                console.error('Error checking existing conversation', e);
            }

            // If no existing conversation, open the messaging modal to start one
            this.messagingModalOpen = true;
        },
        async submitContactToSeller() {
            if (this.sendingContact || !this.guidelinesChecked || this.message.trim().length < 4) return;
            this.sendingContact = true;
            try {
                const formData = new FormData();
                formData.append('seller_id', '{{ $account->seller_id }}');
                formData.append('account_for_sale_id', '{{ $account->id }}');
                formData.append('initial_message', this.message.trim());
                formData.append('_token', '{{ csrf_token() }}');
                
                const response = await fetch('{{ route("account.chat.create") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: formData
                });
                
                const data = await response.json();
                if (response.ok && data.conversation_id) {
                    // Close modal state locally
                    this.messagingModalOpen = false;
                    this.message = '';
                    this.messageCount = 0;
                    this.guidelinesChecked = false;
                    this.showGuidelines = false;
                    
                    // Redirect to chat (conversation id is stored in session)
                    window.location.href = '{{ route("account.chat") }}';
                } else if (data.conversation_id) {
                    // Conversation exists but response not ok – still go to chat
                    window.location.href = '{{ route("account.chat") }}';
                } else {
                    console.error('Failed to create conversation', data);
                }
            } catch (e) {
                console.error('Error creating conversation', e);
            } finally {
                this.sendingContact = false;
            }
        }
    }">
        <!-- Small Header -->
        <div class="relative z-40 w-full px-2 sm:px-4 border-t border-b h-[50px] sm:h-[58px] lg:px-8 before:absolute before:inset-0 transition-all duration-200 before:backdrop-blur-xl before:p-px before:transition-all before:duration-200" style="background-color: rgba(14, 16, 21, 0.75); border-color: rgba(45, 44, 49, 0.5);">
            <div class="flex items-center justify-between mx-auto max-w-full lg:max-w-[1550px] overflow-clip relative h-full z-10 px-2 sm:px-4 lg:px-8">
                <!-- Left: Logo and Game Name -->
                <div class="flex items-center h-full flex-1 min-w-0 lg:w-[30%] gap-x-1.5 sm:gap-x-2 lg:gap-x-3">
                    <a href="#" class="flex gap-x-1.5 sm:gap-x-2 lg:gap-x-3 items-center truncate min-w-0 flex-1">
                        @if($game->slug === 'mlbb')
                            <img src="{{ asset('storage/home_games/mobile-legends.png') }}" alt="{{ $game->name }}" class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 object-contain flex-shrink-0">
                        @endif
                        <div class="text-sm sm:text-base lg:text-xl font-bold tracking-tight truncate text-white">{{ $game->name }}</div>
                    </a>
                </div>
                
                <!-- Right: Support and Join FB Group -->
                <div class="ml-2 sm:ml-auto flex gap-x-1 sm:gap-x-2 h-7 sm:h-8 lg:w-[30%] justify-end shrink-0">
                    <!-- Support Button -->
                    <a href="https://wa.me/213556988175" target="_blank" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap text-[10px] sm:text-xs rounded-md py-1 sm:py-1.5 px-1.5 sm:px-2 md:px-3" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                        <i class="fa-solid fa-headset text-[10px] sm:text-xs"></i> 
                        <span class="ml-1 sm:ml-2">{{ __('messages.support_24_7') }}</span>
                    </a>
                    <!-- Facebook Group Button -->
                    <a href="https://web.facebook.com/share/g/1DPpV2pJ5G/" target="_blank" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap text-[10px] sm:text-xs rounded-md py-1 sm:py-1.5 px-1.5 sm:px-2 md:px-3 hover:ring-2" style="background-color: #1877f2; color: #ffffff; border: 1px solid #1877f2;">
                        <i class="fa-brands fa-facebook text-[10px] sm:text-xs"></i> 
                        <span class="ml-1 sm:ml-2">{{ __('messages.join_fb_group') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6 space-y-4">
            <!-- Title Section with Collection Tier Icon and Badges -->
            <div class="rounded-xl p-6" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                @php
                    $collectionTier = $accountAttributes['collection_tier'] ?? null;
                    // Localized label for collection tier
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
                    
                    // Map collection tier to image filename (same as account card)
                    $collectionTierImage = null;
                    if ($collectionTier) {
                        // Collection tier values match the filename exactly (with spaces)
                        $tierImagePath = 'mlbb_skins_rank/' . $collectionTier . '.webp';
                        if (Storage::disk('public')->exists($tierImagePath)) {
                            $collectionTierImage = asset('storage/' . $tierImagePath);
                        }
                    }
                @endphp
                
                <!-- Mobile Layout: Attributes on top row, then badge and title below -->
                <div class="flex flex-col md:hidden gap-3">
                    <!-- Attributes Row (Mobile) -->
                    <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 py-1.5 pr-2.5 pl-2 text-xs font-medium rounded-full ring-1" style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ffffff;">
                            <i class="mr-1 fa-solid fa-shield-halved"></i> {{ __('messages.free_warranty_and_support') }}
                        </div>
                        <div class="flex-shrink-0 py-1.5 pr-2.5 pl-2 text-xs font-medium rounded-full ring-1" style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ffffff;">
                            <i class="mr-1 fa-solid fa-bolt"></i> {{ __('messages.instant_delivery') }}
                        </div>
                    </div>
                    
                    <!-- Badge and Title Row (Mobile) -->
                    <div class="flex items-start gap-3">
                        @if($collectionTierImage)
                            <div class="flex justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31;">
                                <img src="{{ $collectionTierImage }}" alt="{{ $collectionTier ?? 'Collection Tier' }}" class="w-8 h-8 object-contain">
                            </div>
                        @endif
                        <div class="flex flex-col flex-1 justify-center">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight text-white mb-2">
                                <div class="cursor-default line-clamp-2">{{ $account->title }}</div>
                            </h1>
                            @if($account->description)
                                <p class="relative text-sm text-gray-400 line-clamp-2">{{ $account->description }}</p>
                            @endif
                            <!-- Badges (Mobile) -->
                            @if(!empty($sellerBadges))
                                <div class="flex flex-wrap items-center gap-3 mt-2">
                                    @foreach($sellerBadges as $badge)
                                        <div class="flex items-center gap-2 shrink-0">
                                            <div class="flex items-center justify-center rounded-full shadow-lg {{ $badge['type'] === 'verified' ? 'verified-badge' : '' }}" 
                                                 style="width: 15px; height: 15px; background: {{ $badge['gradient'] }}; border: {{ $badge['border'] ?? '1.5px solid rgba(255, 255, 255, 0.3)' }}; box-shadow: {{ $badge['shadow'] }}, inset 0 1px 0 rgba(255, 255, 255, 0.2);"
                                                 title="{{ $badge['label'] }}">
                                                <i class="fa-solid {{ $badge['icon'] }}" style="color: #ffffff; font-size: {{ $badge['type'] === 'verified' ? '0.6rem' : '0.55rem' }}; text-shadow: 0 1px 4px rgba(0, 0, 0, 0.7), 0 0 2px rgba(0, 0, 0, 0.5); {{ $badge['type'] === 'verified' ? 'filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.3));' : '' }}"></i>
                                            </div>
                                            <span class="text-xs font-semibold" style="color: {{ $badge['color'] }}; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);">{{ $badge['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Desktop Layout: Original layout -->
                <div class="hidden md:block">
                    <div class="flex flex-wrap gap-4 w-full lg:shrink-0 mb-4">
                        @if($collectionTierImage)
                            <div class="flex justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31;">
                                <img src="{{ $collectionTierImage }}" alt="{{ $collectionTier ?? 'Collection Tier' }}" class="w-8 h-8 object-contain">
                            </div>
                        @endif
                        <div class="flex flex-col flex-1 justify-center">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl text-white mb-2">
                                <div class="cursor-default line-clamp-2">{{ $account->title }}</div>
                            </h1>
                            @if($account->description)
                                <p class="relative text-sm text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2">{{ $account->description }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Badges Section (Desktop) -->
                    <div class="flex flex-wrap items-center gap-3">
                        @if(!empty($sellerBadges))
                            @foreach($sellerBadges as $badge)
                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="flex items-center justify-center rounded-full shadow-lg {{ $badge['type'] === 'verified' ? 'verified-badge' : '' }}" 
                                         style="width: 15px; height: 15px; background: {{ $badge['gradient'] }}; border: {{ $badge['border'] ?? '1.5px solid rgba(255, 255, 255, 0.3)' }}; box-shadow: {{ $badge['shadow'] }}, inset 0 1px 0 rgba(255, 255, 255, 0.2);"
                                         title="{{ $badge['label'] }}">
                                        <i class="fa-solid {{ $badge['icon'] }}" style="color: #ffffff; font-size: {{ $badge['type'] === 'verified' ? '0.6rem' : '0.55rem' }}; text-shadow: 0 1px 4px rgba(0, 0, 0, 0.7), 0 0 2px rgba(0, 0, 0, 0.5); {{ $badge['type'] === 'verified' ? 'filter: drop-shadow(0 0 1px rgba(255, 255, 255, 0.3));' : '' }}"></i>
                                    </div>
                                    <span class="text-xs font-semibold" style="color: {{ $badge['color'] }}; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);">{{ $badge['label'] }}</span>
                                </div>
                            @endforeach
                        @endif
                        <div class="flex-shrink-0 py-1.5 pr-2.5 pl-2 text-xs font-medium rounded-full ring-1" style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ffffff;">
                            <i class="mr-1 fa-solid fa-shield-halved"></i> {{ __('messages.free_warranty_and_support') }}
                        </div>
                        <div class="flex-shrink-0 py-1.5 pr-2.5 pl-2 text-xs font-medium rounded-full ring-1" style="background-color: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #ffffff;">
                            <i class="mr-1 fa-solid fa-bolt"></i> {{ __('messages.instant_delivery') }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Images Gallery and Order Information -->
            <div class="flex flex-col lg:flex-row gap-4 w-full">
                <!-- Left Column: Gallery and Account Data (70% width on desktop, full width on mobile) -->
                <div class="flex flex-col gap-4 w-full lg:w-[calc(70%-0.5rem)] lg:flex-shrink-0">
                    <!-- Gallery Section -->
                    <div class="rounded-xl p-6 relative overflow-hidden" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <div class="flex items-center justify-between mb-0">
                            <div class="flex items-center gap-2">
                                @php
                                    // Get account images
                                    $images = $account->images;
                                    $imageCount = $images->count();
                                @endphp
                                <h3 class="text-lg font-semibold text-white">{{ __('messages.gallery') }}</h3>
                                <span class="inline-block px-2 py-0.5 rounded text-xs whitespace-nowrap" style="color: rgba(255, 255, 255, 0.7); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 6px; background-color: rgba(27, 26, 30, 0.5);">{{ $imageCount }}</span>
                            </div>
                            <!-- Navigation arrows on top right -->
                            <div class="flex items-center gap-2">
                                <div class="swiper-button-prev-custom swiper-button-prev"></div>
                                <div class="swiper-button-next-custom swiper-button-next"></div>
                            </div>
                        </div>
                            <div class="swiper account-gallery-swiper" style="--swiper-navigation-color: #ffffff; --swiper-pagination-color: #ef4444;">
                            <div class="swiper-wrapper">
                                @foreach($images as $index => $image)
                                    <div class="swiper-slide">
                                        <a href="{{ asset('storage/' . $image->url) }}" class="glightbox" data-gallery="account-gallery">
                                            <div class="relative overflow-clip rounded-lg ring-1 hover:ring-2 cursor-zoom-in transition-all duration-300 h-[293px]" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.5);">
                                                <button class="w-full h-full" type="button">
                                                    <span class="sr-only">View Images</span>
                                                    <img src="{{ asset('storage/' . $image->url) }}" alt="Account Image" class="object-cover w-full h-full transition-transform duration-300 hover:scale-125" loading="lazy">
                                                </button>
                                                @if($index === 0 && $imageCount > 1)
                                                    <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md absolute right-2 bottom-2 backdrop-blur-md" style="background-color: rgba(27, 26, 30, 0.8); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                                                        <i class="mr-2 fa-solid fa-images"></i> {{ $imageCount }}+
                                                    </button>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Data Section -->
                    <div class="rounded-xl overflow-hidden" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <!-- Header -->
                        <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-6 border-b sm:rounded-t-xl" style="background-color: rgba(27, 26, 30, 0.2); border-color: #2d2c31;">
                            <h3 class="font-semibold leading-none text-white">{{ __('messages.account_data') }}</h3>
                        </div>
                        
                        <!-- Content -->
                        <div class="px-0 sm:px-6 pt-0">
                            <div class="grid grid-cols-2 lg:grid-cols-3">
                                <!-- Description -->
                                <div class="px-4 py-6 sm:px-0 sm:col-span-full col-span-full">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.description') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">
                                        <span class="whitespace-pre-wrap">{{ $account->description ?? __('messages.no_description') }}</span>
                                    </dd>
                                </div>
                                
                                <!-- Platforms -->
                                @if(isset($accountAttributes['platform']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.platforms') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $accountAttributes['platform'] }}</dd>
                                </div>
                                @endif
                                
                                <!-- Account Level -->
                                @if(isset($accountAttributes['level']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.account_level') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $accountAttributes['level'] }}</dd>
                                </div>
                                @endif
                                
                                <!-- Current Tier / Rank -->
                                @if(isset($accountAttributes['rank']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.current_tier') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $accountAttributes['rank'] }}</dd>
                                </div>
                                @endif
                                
                                <!-- Heroes -->
                                @if(isset($accountAttributes['heroes_count']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.heroes') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $accountAttributes['heroes_count'] }}</dd>
                                </div>
                                @endif
                                
                                <!-- Skins -->
                                @if(isset($accountAttributes['skins_count']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.skins') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $accountAttributes['skins_count'] }}</dd>
                                </div>
                                @endif
                                
                                <!-- Collection Tier -->
                                @if(isset($accountAttributes['collection_tier']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.collection_tier') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $collectionTierLabel }}</dd>
                                </div>
                                @endif
                                
                                <!-- Battle Points -->
                                @if(isset($accountAttributes['bp']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.battle_points') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ number_format($accountAttributes['bp'], 0, '.', '') }}</dd>
                                </div>
                                @endif
                                
                                <!-- Diamonds -->
                                @if(isset($accountAttributes['diamonds']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.diamonds') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ number_format($accountAttributes['diamonds'], 0, '.', '') }}</dd>
                                </div>
                                @endif
                                
                                <!-- Win Rate -->
                                @if(isset($accountAttributes['win_rate']))
                                <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <dt class="text-sm font-medium capitalize text-white mb-1">{{ __('messages.win_rate') }}</dt>
                                    <dd class="text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">{{ $accountAttributes['win_rate'] }}%</dd>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Order Information and Trustpilot (30% width on desktop, full width on mobile) -->
                <div class="flex flex-col gap-4 w-full lg:w-[calc(30%-0.5rem)] lg:flex-shrink-0 fast-checkout-sticky">
                    <!-- Order Information Section -->
                    <div class="rounded-xl p-6" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <h3 class="text-lg font-semibold text-white mb-4">{{ __('messages.fast_checkout') }}</h3>
                        
                        <p class="text-sm text-gray-400 mb-6">{{ __('messages.youll_get_account_logins_instantly') }}</p>
                        
                        <!-- Features List -->
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                <i class="fa-solid fa-check text-green-500"></i>
                                <span>{{ __('messages.email_and_password_can_be_changed') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                <i class="fa-solid fa-check text-green-500"></i>
                                <span>{{ __('messages.instant_delivery_after_payment') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-300">
                                <i class="fa-solid fa-check text-green-500"></i>
                                <span>{{ __('messages.free_warranty_and_support') }}</span>
                            </div>
                        </div>
                        
                        <!-- Price Section -->
                        <div class="mb-6 pb-6 border-b" style="border-color: #2d2c31;">
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-3xl font-bold text-white">
                                    {{ number_format($account->price_dzd, 0, '.', '') }}
                                </span>
                                <span class="text-sm font-semibold text-gray-400">DZD</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <i class="fa-solid fa-coins text-yellow-500"></i>
                                <span>{{ __('messages.cashback_coins') }}</span>
                            </div>
                        </div>
                        
                        <!-- Buy Button -->
                        @guest
                            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-login-modal'))" class="w-full mb-4 inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 px-4 text-sm rounded-md">
                                <span>{{ __('messages.buy_account') }}</span>
                            </button>
                        @else
                            <button type="button" id="buy-account-btn" class="w-full mb-4 inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 px-4 text-sm rounded-md">
                                <span id="buy-btn-text">{{ __('messages.buy_account') }}</span>
                                <span id="buy-btn-loading" class="hidden ml-2">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </span>
                            </button>
                        @endguest
                        
                        <!-- Chat with Seller -->
                        @guest
                            <!-- If not logged in, open login modal -->
                            <button 
                                type="button" 
                                @click="window.dispatchEvent(new CustomEvent('open-login-modal'))" 
                                class="w-full inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap text-white shadow-sm py-3 px-4 text-sm rounded-md" 
                                style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;"
                            >
                                <i class="fa-solid fa-comments mr-2"></i>
                                <span>{{ __('messages.chat_with_seller') }}</span>
                            </button>
                        @else
                            <!-- If logged in, either open existing conversation or start new one -->
                            <button 
                                type="button" 
                                @click="handleContactSellerClick" 
                                class="w-full inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap text-white shadow-sm py-3 px-4 text-sm rounded-md" 
                                style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;"
                            >
                                <i class="fa-solid fa-comments mr-2"></i>
                                <span>{{ __('messages.chat_with_seller') }}</span>
                            </button>
                        @endguest
                    </div>
                    
                    <!-- Trustpilot Review Card -->
                    <div class="relative rounded-lg flex flex-col items-center justify-center mt-4 overflow-hidden" style="background-color: rgba(1, 183, 123, 0.05); border: 1px solid rgba(1, 183, 123, 0.1); padding: 20px 1rem; margin-top: 1rem; backdrop-filter: blur(16px);">
                        <!-- Gradient Overlay -->
                        <div class="absolute top-0 w-full h-full z-0 rounded-lg" style="background: linear-gradient(to bottom, rgba(41, 221, 162, 0.05), transparent);"></div>
                        
                        <!-- Content -->
                        <div class="relative z-10 flex flex-col items-center gap-2 w-full">
                            <!-- First Row: Excellent, Stars, and Review Count -->
                            <div class="flex items-center gap-2 flex-wrap justify-center">
                                <span class="text-sm font-medium text-white">{{ __('messages.excellent') }}</span>
                                <!-- Trustpilot Stars with glow effect -->
                                <svg width="80" height="16" viewBox="0 0 80 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-4">
                                    <path d="M8 0L9.76 6.08L16 6.08L11.2 9.92L12.96 16L8 12.16L3.04 16L4.8 9.92L0 6.08L6.24 6.08L8 0Z" fill="#00B67A"/>
                                    <path d="M24 0L25.76 6.08L32 6.08L27.2 9.92L28.96 16L24 12.16L19.04 16L20.8 9.92L16 6.08L22.24 6.08L24 0Z" fill="#00B67A"/>
                                    <path d="M40 0L41.76 6.08L48 6.08L43.2 9.92L44.96 16L40 12.16L35.04 16L36.8 9.92L32 6.08L38.24 6.08L40 0Z" fill="#00B67A"/>
                                    <path d="M56 0L57.76 6.08L64 6.08L59.2 9.92L60.96 16L56 12.16L51.04 16L52.8 9.92L48 6.08L54.24 6.08L56 0Z" fill="#00B67A"/>
                                    <path d="M72 0L73.76 6.08L80 6.08L75.2 9.92L76.96 16L72 12.16L67.04 16L68.8 9.92L64 6.08L70.24 6.08L72 0Z" fill="#00B67A"/>
                                </svg>
                                <span class="text-xs text-gray-300">
                                    <strong class="text-white">15 472</strong>
                                    <span class="pl-1">{{ __('messages.reviews_on') }}</span>
                                </span>
                            </div>
                            
                            <!-- Second Row: Trustpilot Logo -->
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 126 31" width="80" height="19" class="h-5">
                                    <path d="M33.075 11.07h12.743v2.364h-5.01v13.29h-2.756v-13.29h-4.988V11.07h.01zm12.199 4.32h2.355v2.187h.044c.078-.31.223-.608.434-.895a4.117 4.117 0 0 1 1.766-1.381 3.074 3.074 0 0 1 1.122-.22c.29 0 .5.01.611.021.112.011.223.034.345.045v2.408a8.063 8.063 0 0 0-.545-.077 4.64 4.64 0 0 0-.544-.033c-.422 0-.822.088-1.2.254-.378.165-.7.42-.978.74-.277.331-.5.729-.666 1.215-.167.486-.245 1.039-.245 1.668v5.392h-2.51V15.39h.01zm18.22 11.335h-2.466v-1.58h-.045c-.31.574-.766 1.027-1.377 1.37-.611.342-1.234.519-1.867.519-1.5 0-2.588-.365-3.255-1.105-.667-.74-1-1.856-1-3.347V15.39h2.51v6.949c0 .994.19 1.701.579 2.11.377.409.922.618 1.61.618.534 0 .967-.077 1.323-.243.355-.165.644-.375.855-.651.222-.266.378-.597.478-.973.1-.375.144-.784.144-1.226v-6.573h2.511v11.324zm4.278-3.635c.077.729.355 1.237.833 1.536.489.287 1.066.441 1.744.441.233 0 .5-.022.8-.055.3-.033.589-.11.844-.21.267-.1.478-.254.656-.453.167-.199.244-.453.233-.773a1.065 1.065 0 0 0-.355-.784c-.222-.21-.5-.365-.845-.498a8.513 8.513 0 0 0-1.177-.32c-.445-.088-.89-.188-1.345-.287-.466-.1-.922-.232-1.355-.376a4.147 4.147 0 0 1-1.167-.596 2.628 2.628 0 0 1-.822-.95c-.21-.387-.31-.862-.31-1.437 0-.618.155-1.127.455-1.546.3-.42.689-.752 1.144-1.006a5.35 5.35 0 0 1 1.544-.541 9.454 9.454 0 0 1 1.622-.155c.59 0 1.156.067 1.69.188.532.122 1.021.32 1.455.608.433.276.788.64 1.077 1.082.29.442.467.984.545 1.613h-2.622c-.123-.596-.39-1.005-.823-1.204-.433-.21-.933-.31-1.488-.31a4.7 4.7 0 0 0-.634.045 3.483 3.483 0 0 0-.688.166 1.494 1.494 0 0 0-.545.353.852.852 0 0 0-.222.608c0 .31.111.552.322.74.211.188.49.343.833.475a8.44 8.44 0 0 0 1.178.32c.445.089.9.188 1.367.288.455.1.9.232 1.344.375.444.144.833.343 1.178.597.344.254.622.563.833.939.211.376.322.85.322 1.403 0 .674-.155 1.237-.466 1.712a3.7 3.7 0 0 1-1.2 1.138 5.66 5.66 0 0 1-1.645.641c-.6.133-1.2.199-1.788.199a7.629 7.629 0 0 1-2-.243c-.611-.166-1.145-.409-1.589-.73-.444-.33-.8-.74-1.055-1.225-.256-.487-.39-1.072-.411-1.746h2.533v-.022zm8.288-7.7h1.9v-3.403h2.51v3.403h2.267v1.867H80.47v6.054c0 .265.012.486.034.685.022.188.078.353.155.486.078.132.2.232.367.298.167.066.378.1.667.1.177 0 .355 0 .533-.011.178-.011.355-.034.533-.078v1.934c-.278.033-.555.055-.81.088a6.532 6.532 0 0 1-.812.044c-.667 0-1.2-.066-1.6-.188-.4-.121-.722-.309-.944-.552-.233-.243-.378-.541-.467-.906a6.775 6.775 0 0 1-.144-1.248v-6.684h-1.9v-1.89h-.022zm8.455 0h2.377v1.535h.045c.355-.663.844-1.127 1.477-1.414a4.892 4.892 0 0 1 2.056-.43c.9 0 1.677.154 2.344.474a4.58 4.58 0 0 1 1.666 1.293c.445.552.767 1.193.99 1.922a8.06 8.06 0 0 1 .332 2.342c0 .763-.1 1.503-.3 2.21a5.9 5.9 0 0 1-.9 1.9c-.4.552-.91.983-1.533 1.315-.622.331-1.344.497-2.188.497a6.17 6.17 0 0 1-1.1-.1 4.862 4.862 0 0 1-1.056-.32 3.815 3.815 0 0 1-.933-.563 3.507 3.507 0 0 1-.722-.796h-.045v5.657h-2.51V15.39zm8.777 5.678c0-.508-.067-1.005-.2-1.491a4.012 4.012 0 0 0-.6-1.282 3.05 3.05 0 0 0-.99-.895 2.784 2.784 0 0 0-1.366-.342c-1.055 0-1.855.364-2.388 1.094-.534.729-.8 1.7-.8 2.916 0 .575.066 1.105.21 1.591.145.486.345.906.634 1.26.278.353.611.63 1 .828.389.21.844.31 1.355.31.578 0 1.056-.122 1.456-.354.4-.232.722-.541.977-.906.256-.376.445-.795.556-1.27.1-.476.156-.962.156-1.459zm4.432-9.998h2.511v2.364h-2.51V11.07zm0 4.32h2.511v11.335h-2.51V15.39zm4.756-4.32h2.51v15.655h-2.51V11.07zm10.21 15.964c-.911 0-1.722-.155-2.433-.453a5.373 5.373 0 0 1-1.811-1.237 5.381 5.381 0 0 1-1.122-1.89 7.255 7.255 0 0 1-.39-2.408c0-.862.134-1.657.39-2.386a5.381 5.381 0 0 1 1.122-1.89c.489-.53 1.1-.938 1.81-1.237.712-.298 1.523-.453 2.434-.453.91 0 1.722.155 2.433.453a5.373 5.373 0 0 1 1.81 1.238c.49.53.867 1.16 1.123 1.889a7.17 7.17 0 0 1 .389 2.386c0 .873-.134 1.68-.39 2.408a5.381 5.381 0 0 1-1.121 1.89c-.489.53-1.1.939-1.811 1.237-.711.298-1.522.453-2.433.453zm0-1.978c.555 0 1.044-.121 1.455-.353a3.09 3.09 0 0 0 1.011-.917c.267-.376.456-.807.589-1.282.122-.475.189-.96.189-1.458a5.89 5.89 0 0 0-.189-1.447 3.77 3.77 0 0 0-.589-1.282 3.059 3.059 0 0 0-1.01-.906c-.412-.232-.9-.353-1.456-.353-.556 0-1.045.121-1.456.353a3.184 3.184 0 0 0-1.01.906 3.993 3.993 0 0 0-.59 1.282 5.882 5.882 0 0 0-.188 1.447c0 .497.066.983.188 1.458.123.475.323.906.59 1.282.266.376.6.685 1.01.917.411.243.9.353 1.456.353zm6.488-9.666h1.9v-3.403h2.51v3.403h2.267v1.867h-2.266v6.054c0 .265.01.486.033.685.022.188.078.353.156.486a.71.71 0 0 0 .366.298c.167.066.378.1.667.1.178 0 .355 0 .533-.011.178-.011.356-.034.533-.078v1.934c-.277.033-.555.055-.81.088a6.532 6.532 0 0 1-.812.044c-.666 0-1.2-.066-1.6-.188-.4-.121-.722-.309-.944-.552-.233-.243-.378-.541-.466-.906a6.775 6.775 0 0 1-.145-1.248v-6.684h-1.9v-1.89h-.022z" fill="currentColor" class="text-white"></path>
                                    <path fill="#00B67A" d="M30.142 11.07h-11.51L15.076.177 11.51 11.07 0 11.059l9.321 6.74L5.755 28.68l9.321-6.728 9.31 6.728-3.555-10.882 9.31-6.728z"></path>
                                    <path fill="#005128" d="m21.631 20.262-.8-2.464-5.755 4.154z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seller Info Card -->
                    <div class="rounded-xl mt-4 overflow-hidden" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <!-- Seller Header -->
                        <a href="#" class="group block">
                            <div class="flex flex-row gap-3 px-4 sm:px-6 py-4 items-center justify-between group-hover:bg-muted transition-colors" style="background-color: rgba(27, 26, 30, 0.5); border-bottom: 1px solid #2d2c31;">
                                <div class="flex items-center flex-1 min-w-0">
                                    <div class="relative shrink-0">
                                        <span class="inline-flex items-center justify-center font-normal text-white select-none shrink-0 overflow-hidden h-10 w-10 text-xs rounded-full" style="background-color: rgba(27, 26, 30, 0.5);">
                                            <img role="img" src="{{ $sellerPfp }}" class="object-cover w-full h-full aspect-1 rounded-full" alt="{{ $user->name ?? 'Seller' }}" onerror="this.onerror=null; this.src='{{ asset('storage/examplepfp.webp') }}';">
                                        </span>
                                        <div class="absolute block p-px rounded-full bg-background size-3.5 -right-px bottom-1" style="background-color: #0e1015;">
                                            <img src="https://cdn.gameboost.com/static/status/offline.png" class="size-full rounded-full" alt="Offline">
                                        </div>
                                    </div>
                                    <div class="ml-2.5 truncate min-w-0">
                                        <div class="text-sm font-medium truncate text-white">
                                            <div class="group-hover:underline inline-flex items-center gap-1.5">
                                                {{ $user->name ?? 'Seller' }}
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
                                        </div>
                                        <div class="text-xs truncate text-gray-400">{{ __('messages.elite_seller') }}</div>
                                    </div>
                                </div>
                                <span class="inline-flex font-medium ring-1 ring-inset px-2 py-1 text-xs rounded-full items-center shrink-0" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31; color: rgba(255, 255, 255, 0.7);">
                                    <i class="mr-1 fa-solid fa-clock"></i>
                                    <span class="flex-1 truncate shrink-0">{{ __('messages.an_hour_ago') }}</span>
                                </span>
                            </div>
                        </a>
                        
                        <!-- Seller Stats -->
                        <div class="text-sm divide-y" style="border-color: #2d2c31;">
                            <!-- Seller Rating -->
                            <div class="flex items-baseline justify-between py-4 px-4 sm:px-6 gap-x-4">
                                <dt class="font-medium capitalize shrink-0 text-gray-400">
                                    <i class="w-4 mr-1 text-center fa-solid fa-star"></i>
                                    {{ __('messages.seller_rating') }}
                                </dt>
                                <dd class="text-white shrink-0">
                                    <div class="flex items-center text-sm gap-x-2">
                                        <span class="text-gray-400">{{ number_format($reviewCount, 0, '.', '') }} {{ __('messages.reviews') }}</span>
                                        <div class="shrink-0 h-4 w-px" style="background-color: #2d2c31;"></div>
                                        <div>
                                            <i class="mr-0.5 fa-solid fa-thumbs-up text-green-500"></i>
                                            <span class="text-green-500">{{ $ratingPercentage }}%</span>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                            
                            <!-- Total Sales -->
                            <div class="flex items-baseline justify-between py-4 px-4 sm:px-6 gap-x-4">
                                <dt class="font-medium capitalize shrink-0 text-gray-400">
                                    <i class="w-4 mr-1 text-center fa-solid fa-cart-shopping"></i>
                                    {{ __('messages.total_sales') }}
                                </dt>
                                <dd class="text-white shrink-0">{{ number_format($soldCount) }} {{ __('messages.sold') }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- You may also like Section -->
            <div class="mt-6">
                <div class="flex justify-between items-center lg:mx-0 mb-6">
                    <h2 class="text-xl font-semibold tracking-tight text-white sm:text-2xl">{{ __('messages.you_may_also_like') }}</h2>
                    <div class="flex gap-2 items-center">
                        <a href="{{ route('games.show', $game->slug) }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-full ring-1" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                            View More
                        </a>
                        <button type="button" class="related-accounts-prev inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium whitespace-nowrap hover:bg-secondary-hover text-secondary-foreground ring-1 ring-secondary-ring shadow-sm focus:outline-secondary text-sm touch-manipulation h-8 w-8 rounded-full p-0 relative top-0 left-0 translate-0 active:translate-y-px" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                            <i class="text-current fa-solid fa-angle-left"></i>
                            <span class="sr-only">Previous Slide</span>
                        </button>
                        <button type="button" class="related-accounts-next inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium whitespace-nowrap hover:bg-secondary-hover text-secondary-foreground ring-1 ring-secondary-ring shadow-sm focus:outline-secondary text-sm touch-manipulation h-8 w-8 rounded-full p-0 relative top-0 left-0 translate-0 active:translate-y-px" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                            <i class="text-current fa-solid fa-angle-right"></i>
                            <span class="sr-only">Next Slide</span>
                        </button>
                    </div>
                </div>
                <div class="swiper related-accounts-swiper">
                    <div class="swiper-wrapper">
                        @php
                            // Get accounts from the same game, excluding the current account
                            $relatedAccounts = \App\Models\AccountForSale::with(['game', 'seller.user', 'attributes', 'images'])
                                ->where('game_id', $account->game_id)
                                ->where('id', '!=', $account->id)
                                ->where('status', 'available')
                                ->inRandomOrder()
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($relatedAccounts as $relatedAccount)
                            <div class="swiper-slide">
                                @include('components.account-card', ['account' => $relatedAccount])
                            </div>
                        @empty
                            <div class="swiper-slide">
                                <div class="rounded-xl p-12 text-center" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                                    <p class="text-gray-400 text-lg">No related accounts available.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- From the same seller Section -->
            <div style="margin-top: 20px;">
                <div class="flex justify-between items-center lg:mx-0 mb-6">
                    <h2 class="text-xl font-semibold tracking-tight text-white sm:text-2xl">{{ __('messages.from_the_same_seller') }}</h2>
                    <div class="flex gap-2 items-center">
                        <a href="{{ route('games.show', $game->slug) }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-full ring-1" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                            View More
                        </a>
                        <button type="button" class="same-seller-prev inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium whitespace-nowrap hover:bg-secondary-hover text-secondary-foreground ring-1 ring-secondary-ring shadow-sm focus:outline-secondary text-sm touch-manipulation h-8 w-8 rounded-full p-0 relative top-0 left-0 translate-0 active:translate-y-px" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                            <i class="text-current fa-solid fa-angle-left"></i>
                            <span class="sr-only">Previous Slide</span>
                        </button>
                        <button type="button" class="same-seller-next inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium whitespace-nowrap hover:bg-secondary-hover text-secondary-foreground ring-1 ring-secondary-ring shadow-sm focus:outline-secondary text-sm touch-manipulation h-8 w-8 rounded-full p-0 relative top-0 left-0 translate-0 active:translate-y-px" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                            <i class="text-current fa-solid fa-angle-right"></i>
                            <span class="sr-only">Next Slide</span>
                        </button>
                    </div>
                </div>
                <div class="swiper same-seller-swiper">
                    <div class="swiper-wrapper">
                        @php
                            // Get accounts from the same seller, excluding the current account
                            $sameSellerAccounts = \App\Models\AccountForSale::with(['game', 'seller.user', 'attributes', 'images'])
                                ->where('seller_id', $account->seller_id)
                                ->where('id', '!=', $account->id)
                                ->where('status', 'available')
                                ->inRandomOrder()
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($sameSellerAccounts as $sameSellerAccount)
                            <div class="swiper-slide">
                                @include('components.account-card', ['account' => $sameSellerAccount])
                            </div>
                        @empty
                            <div class="swiper-slide">
                                <div class="rounded-xl p-12 text-center" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                                    <p class="text-gray-400 text-lg">No other accounts from this seller.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- FAQ Section -->
            <div class="flex flex-col mt-6" style="margin-top: 20px;">
                <h2 class="text-xl font-bold tracking-tight text-white sm:text-2xl mb-5" style="margin-top: 50px;">{{ __('messages.faqs_about_mlbb_accounts') }}</h2>
                <div class="flex relative flex-col pt-5">
                    @php
                        $faqs = [
                            [
                                'question' => __('messages.faq_mlbb_1_question'),
                                'answer' => __('messages.faq_mlbb_1_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_2_question'),
                                'answer' => __('messages.faq_mlbb_2_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_3_question'),
                                'answer' => __('messages.faq_mlbb_3_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_4_question'),
                                'answer' => __('messages.faq_mlbb_4_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_5_question'),
                                'answer' => __('messages.faq_mlbb_5_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_6_question'),
                                'answer' => __('messages.faq_mlbb_6_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_7_question'),
                                'answer' => __('messages.faq_mlbb_7_answer')
                            ],
                            [
                                'question' => __('messages.faq_mlbb_8_question'),
                                'answer' => __('messages.faq_mlbb_8_answer')
                            ]
                        ];
                    @endphp
                    @foreach($faqs as $index => $faq)
                        <div class="flex flex-col w-full mb-5 transition-colors border shadow-md rounded-2xl backdrop-blur-md" style="background-color: #0e1015; border-color: #2d2c31;" x-data="{ open: {{ $index === 2 ? 'true' : 'false' }} }">
                            <button 
                                type="button" 
                                @click="open = !open"
                                class="p-6 w-full text-left"
                            >
                                <div class="flex items-center w-full">
                                    <h3 class="font-semibold text-left text-white">{{ $faq['question'] }}</h3>
                                    <i class="ml-auto transition-transform fa-solid fa-chevron-down text-gray-400" :class="{ 'transform rotate-180': open }"></i>
                                </div>
                            </button>
                            <div 
                                x-show="open"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 max-h-0"
                                x-transition:enter-end="opacity-100 max-h-96"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 max-h-96"
                                x-transition:leave-end="opacity-0 max-h-0"
                                class="overflow-hidden"
                            >
                                <div class="flex flex-col px-6 pb-6 mr-auto text-sm font-medium text-left cursor-auto" style="color: rgba(255, 255, 255, 0.9);">
                                    <div>
                                        <p>{{ $faq['answer'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Messaging Modal -->
            <div 
                x-show="messagingModalOpen"
                x-cloak
        @keydown.escape.window="messagingModalOpen = false; message = ''; messageCount = 0; guidelinesChecked = false; showGuidelines = false"
        class="fixed inset-0 z-[100] overflow-y-auto"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="messagingModalOpen = false; message = ''; messageCount = 0; guidelinesChecked = false; showGuidelines = false"></div>
        
        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-2 sm:p-4">
            <div 
                class="relative w-full max-w-2xl rounded-xl overflow-hidden"
                style="background-color: #0e1015; border: 1px solid #2d2c31;"
                @click.away="messagingModalOpen = false; message = ''; messageCount = 0; guidelinesChecked = false; showGuidelines = false"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <!-- Header -->
                <div class="flex justify-between items-start sm:items-center px-4 sm:px-6 pt-4 sm:pt-6 pb-0 sm:pt-5">
                    <div class="flex gap-2 sm:gap-3 items-center flex-1 min-w-0">
                        <div class="shrink-0">
                            <div class="relative">
                                <span class="inline-flex items-center justify-center font-normal text-white select-none shrink-0 overflow-hidden h-10 w-10 sm:h-12 sm:w-12 text-xs rounded-full" style="background-color: #1b1a1e;">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($account->seller->user->name ?? 'Seller') }}&background=ef4444&color=fff" class="object-cover w-full h-full rounded-full" alt="{{ $account->seller->user->name ?? 'Seller' }}">
                                </span>
                                <div class="absolute block p-px rounded-full bg-0e1015 size-3 sm:size-4 -right-px bottom-0.5 sm:bottom-1" style="background-color: #0e1015;">
                                    <img src="https://cdn.gameboost.com/static/status/offline.png" class="size-full" alt="Offline">
                                </div>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base sm:text-lg font-medium leading-5 sm:leading-6 text-white truncate">{{ __('messages.message') }} {{ $account->seller->user->name ?? 'Game Market Store' }}</h2>
                            <p class="text-xs sm:text-sm truncate" style="color: rgba(255, 255, 255, 0.9);">{{ __('messages.offline') }} · {{ __('messages.member_since') }} {{ $account->seller?->created_at?->format('M jS, Y') ?? 'Jul 11th, 2024' }}</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="messagingModalOpen = false; message = ''; messageCount = 0; guidelinesChecked = false; showGuidelines = false"
                        class="inline-flex items-center justify-center text-sm transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap w-8 h-8 sm:w-10 sm:h-10 md:h-9 md:w-9 rounded-md shrink-0 ml-2"
                        style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);"
                    >
                        <span class="sr-only">Close</span>
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
                
                <!-- Form Content -->
                <div class="p-4 sm:p-6 overflow-y-auto max-h-[calc(100svh-120px)] sm:max-h-[calc(100svh-150px)] relative messaging-modal-scroll">
                    <div class="sticky -top-7 -mt-10 w-full h-10 bg-gradient-to-b" style="background: linear-gradient(to bottom, #0e1015, transparent);"></div>
                    <form>
                        <div class="flex flex-col px-0 sm:px-2 space-y-6 sm:space-y-8">
                            <!-- Message Textarea -->
                            <div>
                                <div class="w-full">
                                    <div class="flex justify-between mb-1">
                                        <label class="flex items-center gap-2 text-xs sm:text-sm font-medium leading-6" style="color: rgba(255, 255, 255, 0.9);">{{ __('messages.message') }}</label>
                                    </div>
                                    <div class="relative">
                                        <textarea 
                                            x-model="message"
                                            placeholder="{{ str_replace(':seller', $account->seller->user->name ?? 'Game Market Store', __('messages.ask_seller_about_product')) }}" 
                                            class="py-3 !text-sm sm:!text-base rounded-b-none block w-full rounded-md border-0 shadow-sm disabled:opacity-50 disabled:pointer-events-none text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            rows="3" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31; height: 76px; resize: none; padding-left: 12px;"
                                        ></textarea>
                                    </div>
                                </div>
                                <div class="flex gap-x-2 justify-center items-center px-2 py-1.5 text-xs sm:text-sm rounded-b-lg" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31; border-top: none; color: rgba(255, 255, 255, 0.7);">
                                    <i class="fa-solid fa-clock"></i>
                                    <span>{{ __('messages.last_seen') }} 4 {{ __('messages.hours_ago') }}</span>
                                </div>
                            </div>
                            
                            <!-- Guidelines Checkbox -->
                            <div class="p-3 sm:p-4 border rounded-lg hover:bg-muted/50" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.3);">
                                <label for="guidelines">
                                    <div class="flex items-start space-x-2 sm:space-x-3">
                                        <input 
                                            type="checkbox" 
                                            id="guidelines"
                                            x-model="guidelinesChecked"
                                            class="h-4 w-4 shrink-0 rounded-sm border focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 mt-0.5"
                                            style="background-color: #1b1a1e; border-color: #2d2c31; accent-color: #ef4444;"
                                        >
                                        <div class="grid gap-1 sm:gap-1.5 leading-none flex-1">
                                            <p class="text-xs sm:text-sm font-medium leading-tight" style="color: rgba(255, 255, 255, 0.9);">{{ __('messages.i_understand_guidelines') }}</p>
                                            <p class="text-xs sm:text-sm leading-tight" style="color: rgba(255, 255, 255, 0.6);">{{ __('messages.i_will_keep_communication') }}</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Show Guidelines Button -->
                            <div class="flex">
                                <button 
                                    type="button" 
                                    @click="showGuidelines = !showGuidelines"
                                    class="inline-flex items-center justify-center focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 rounded-md gap-2 self-start text-xs sm:text-sm transition-all duration-1000"
                                    style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid #2d2c31;"
                                >
                                    <i class="fa-solid text-xs" :class="showGuidelines ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                    <span>{{ __('messages.show_messaging_guidelines') }}</span>
                                </button>
                            </div>
                            
                            <!-- Guidelines Content (Hidden by default) -->
                            <div x-show="showGuidelines" x-transition class="grid gap-3 sm:gap-4 sm:-mx-4 grid-cols-1 md:grid-cols-2">
                                <!-- Good Examples Column -->
                                <div class="flex flex-col pt-3 space-y-3 border rounded-lg overflow-clip" style="border-color: #10b981; background-color: rgba(16, 185, 129, 0.1);">
                                    <div class="flex items-center gap-2 px-3 sm:px-4">
                                        <i class="fa-solid fa-circle-check text-sm sm:text-base" style="color: #10b981;"></i>
                                        <h4 class="text-sm sm:text-base font-semibold" style="color: #10b981;">{{ __('messages.good_examples') }}</h4>
                                    </div>
                                    <div class="flex flex-col divide-y" style="border-color: rgba(16, 185, 129, 0.3);">
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_1') }}</p>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_2') }}</p>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_3') }}</p>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_4') }}</p>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_5') }}</p>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_6') }}</p>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(16, 185, 129, 0.1);">
                                            <p class="pr-2 text-gray-300">{{ __('messages.good_example_7') }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Avoid These Column -->
                                <div class="flex flex-col pt-3 space-y-3 border rounded-lg overflow-clip" style="border-color: #ef4444; background-color: rgba(239, 68, 68, 0.1);">
                                    <div class="flex items-center gap-2 px-3 sm:px-4">
                                        <i class="fa-solid fa-circle-xmark text-sm sm:text-base" style="color: #ef4444;"></i>
                                        <h4 class="text-sm sm:text-base font-semibold" style="color: #ef4444;">{{ __('messages.avoid_these') }}</h4>
                                    </div>
                                    <div class="flex flex-col divide-y" style="border-color: rgba(239, 68, 68, 0.3);">
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_1') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_2') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_3') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_4') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_5') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_6') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_7') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_8') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                        <div class="relative px-2 sm:px-2 pt-2 pb-3 sm:pb-4 text-xs sm:text-sm group" style="background-color: rgba(239, 68, 68, 0.1);">
                                            <p class="line-through decoration-1 text-gray-400">{{ __('messages.avoid_example_9') }}</p>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity opacity-0 group-hover:opacity-100">
                                                <div class="px-2 py-1 text-xs font-medium rounded" style="background-color: #ef4444; color: #ffffff;">{{ __('messages.against_platform_rules') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Send Button -->
                        <div class="sticky bottom-0 mt-4 sm:mt-6 rounded-lg" style="background-color: #0e1015;">
                            <button 
                                type="button" 
                                @click="submitContactToSeller"
                                :disabled="!guidelinesChecked || message.length < 4 || sendingContact"
                                class="items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap shadow-sm focus:outline-red-600 py-3 px-4 sm:px-5 w-full text-sm sm:text-base rounded-lg flex"
                                :class="guidelinesChecked && message.length >= 4 && !sendingContact ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-gray-600 text-gray-400'"
                            >
                                <span x-show="!sendingContact" class="flex items-center justify-center gap-2">
                                    <span>{{ __('messages.send_message') }}</span>
                                    <i class="fa-solid fa-paper-plane"></i>
                                </span>
                                <span x-show="sendingContact" class="flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <span>{{ __('messages.sending') ?? 'Sending...' }}</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- GLightbox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <style>
        .verified-badge {
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .verified-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.5) !important;
        }
        .account-gallery-swiper {
            width: 100%;
            height: 100%;
            padding-bottom: 5px;
            position: relative;
            overflow: visible;
        }
        .account-gallery-swiper .swiper-wrapper {
            position: relative;
        }
        .account-gallery-swiper .swiper-slide {
            text-align: center;
            font-size: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .swiper-button-prev-custom,
        .swiper-button-next-custom {
            position: static !important;
            margin: 0 !important;
            color: #ffffff;
            background-color: rgba(27, 26, 30, 0.8);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #2d2c31;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .swiper-button-prev-custom:hover,
        .swiper-button-next-custom:hover {
            background-color: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.5);
        }
        .swiper-button-prev-custom:after,
        .swiper-button-next-custom:after {
            font-size: 16px;
            font-weight: bold;
        }
        .account-gallery-swiper .swiper-button-next,
        .account-gallery-swiper .swiper-button-prev {
            display: none;
        }
        .account-gallery-swiper .swiper-pagination {
            position: absolute !important;
            bottom: 10px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: auto !important;
            z-index: 10;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .account-gallery-swiper .swiper-pagination-bullet {
            background: #ef4444;
            opacity: 0.5;
        }
        .account-gallery-swiper .swiper-pagination-bullet-active {
            opacity: 1;
        }
        
        /* Sticky Fast Checkout */
        .fast-checkout-sticky {
            position: sticky;
            top: 100px; /* Account for top menu/header bar */
            align-self: flex-start;
        }
        
        /* Disable sticky on mobile */
        @media (max-width: 768px) {
            .fast-checkout-sticky {
                position: static;
            }
        }
        
        /* Related Accounts Swiper */
        .related-accounts-swiper {
            width: 100%;
            padding: 10px 0;
        }
        .related-accounts-swiper .swiper-slide {
            height: auto;
        }
        .related-accounts-swiper .swiper-button-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Same Seller Swiper */
        .same-seller-swiper {
            width: 100%;
            padding: 10px 0;
        }
        .same-seller-swiper .swiper-slide {
            height: auto;
        }
        .same-seller-swiper .swiper-button-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Login Modal */
        [x-cloak] { display: none !important; }
        
        /* Hide navbar when messaging modal is open */
        body.modal-open header {
            display: none !important;
        }
        
        /* Custom Scrollbar for Messaging Modal */
        .messaging-modal-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .messaging-modal-scroll::-webkit-scrollbar-track {
            background: #0e1015;
            border-radius: 4px;
        }
        .messaging-modal-scroll::-webkit-scrollbar-thumb {
            background: #1b1a1e;
            border-radius: 4px;
            border: 1px solid #2d2c31;
        }
        .messaging-modal-scroll::-webkit-scrollbar-thumb:hover {
            background: #2d2c31;
        }
        
        /* Firefox scrollbar */
        .messaging-modal-scroll {
            scrollbar-width: thin;
            scrollbar-color: #1b1a1e #0e1015;
        }
        
        /* Custom Scrollbar for Login/Signup Modal */
        .login-modal-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .login-modal-scroll::-webkit-scrollbar-track {
            background: #0e1015;
            border-radius: 4px;
        }
        .login-modal-scroll::-webkit-scrollbar-thumb {
            background: #1b1a1e;
            border-radius: 4px;
            border: 1px solid #2d2c31;
        }
        .login-modal-scroll::-webkit-scrollbar-thumb:hover {
            background: #2d2c31;
        }
        
        /* Firefox scrollbar */
        .login-modal-scroll {
            scrollbar-width: thin;
            scrollbar-color: #1b1a1e #0e1015;
        }
        
        .social-login-btn {
            background-color: rgba(27, 26, 30, 0.5);
            color: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(45, 44, 49, 0.5);
            transition: all 0.2s ease;
        }
        .social-login-btn:hover {
            color: #ffffff;
        }
        .social-login-btn.discord:hover {
            background-color: #5865f2;
            border-color: #5865f2;
        }
        .social-login-btn.google:hover {
            background-color: #ea4335;
            border-color: #ea4335;
        }
        .social-login-btn.steam:hover {
            background-color: #1348a3;
            border-color: #1348a3;
        }
        .social-login-btn.facebook:hover {
            background-color: #1877f2;
            border-color: #1877f2;
        }
        .social-login-btn.twitch:hover {
            background-color: #9146ff;
            border-color: #9146ff;
        }
        .social-login-btn.gmail:hover {
            background-color: #c71610;
            border-color: #c71610;
        }
    </style>
@endpush

@push('scripts')
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- GLightbox JS -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Swiper
            const swiper = new Swiper('.account-gallery-swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                navigation: {
                    nextEl: '.swiper-button-next-custom',
                    prevEl: '.swiper-button-prev-custom',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 2.5,
                        spaceBetween: 30,
                    },
                    1024: {
                        slidesPerView: 2.5,
                        spaceBetween: 30,
                    },
                },
            });

            // Initialize Related Accounts Swiper
            const relatedAccountsSwiper = new Swiper('.related-accounts-swiper', {
                slidesPerView: 1,
                spaceBetween: 24,
                navigation: {
                    nextEl: '.related-accounts-next',
                    prevEl: '.related-accounts-prev',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 24,
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 24,
                    },
                },
            });

            // Initialize Same Seller Swiper
            const sameSellerSwiper = new Swiper('.same-seller-swiper', {
                slidesPerView: 1,
                spaceBetween: 24,
                navigation: {
                    nextEl: '.same-seller-next',
                    prevEl: '.same-seller-prev',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 24,
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 24,
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 24,
                    },
                },
            });

            // Initialize GLightbox
            const lightbox = GLightbox({
                selector: '.glightbox',
                touchNavigation: true,
                loop: true,
                autoplayVideos: true,
            });
            
            // Handle Buy Account button click
            const buyBtn = document.getElementById('buy-account-btn');
            if (buyBtn) {
                buyBtn.addEventListener('click', async function() {
                    const btnText = document.getElementById('buy-btn-text');
                    const btnLoading = document.getElementById('buy-btn-loading');
                    
                    // Show loading state
                    buyBtn.disabled = true;
                    btnText.classList.add('hidden');
                    btnLoading.classList.remove('hidden');
                    
                    try {
                        const response = await fetch(`{{ route('orders.create', $account->id) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin'
                        });
                        
                        const data = await response.json();
                        
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Failed to create order. Please try again.');
                            // Reset button state
                            buyBtn.disabled = false;
                            btnText.classList.remove('hidden');
                            btnLoading.classList.add('hidden');
                        }
                    } catch (error) {
                        console.error('Error creating order:', error);
                        alert('An error occurred. Please try again.');
                        // Reset button state
                        buyBtn.disabled = false;
                        btnText.classList.remove('hidden');
                        btnLoading.classList.add('hidden');
                    }
                });
            }
        });
    </script>
@endpush

