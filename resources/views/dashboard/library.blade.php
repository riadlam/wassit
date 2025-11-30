@extends('layouts.app')

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('{{ asset('storage/home_page/degaultbanner.webp') }}'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen pt-16 sm:pt-16 pb-20 md:pb-8">
        <!-- Dashboard Navigation -->
        @include('components.dashboard-nav')
        
        <!-- Main Content -->
        <div class="relative z-10 px-4 sm:px-6 lg:px-8" style="padding-top: 122px;">
            <div class="mx-auto max-w-[1550px]">
                <!-- Header -->
                <div class="flex flex-wrap gap-4 justify-between items-center w-full lg:shrink-0 mb-8">
                    <div class="flex gap-x-3 items-center">
                        <div class="hidden justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm md:flex shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                            <i class="fa-lg fa-solid fa-books" aria-hidden="true"></i>
                        </div>
                        <div class="flex flex-col justify-center lg:flex-1">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl font-display text-white">My Library</h1>
                            <p class="relative text-sm sm:block text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Library Sections -->
                <div class="mt-8">
                    <div class="space-y-12">
                        <!-- Bookmarked Offers Section -->
                        <div class="relative space-y-4" role="region" aria-roledescription="carousel" tabindex="0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-base font-medium sm:text-lg text-white">
                                        <i class="mr-2 text-gray-400 fa-solid fa-bookmark"></i> Bookmarked Offers
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-400">Once you bookmark an offer, it will appear here</p>
                                </div>
                            </div>
                            <!-- Placeholder Cards (Empty State) -->
                            <div class="grid grid-cols-1 gap-8 max-h-48 overflow-clip sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div class="w-full h-48 rounded-lg" style="background-color: #0e1015; border: 1px solid #2d2c31; opacity: 0.5;"></div>
                                <div class="w-full h-48 rounded-lg" style="background-color: #0e1015; border: 1px solid #2d2c31; opacity: 0.3;"></div>
                                <div class="w-full h-48 rounded-lg" style="background-color: #0e1015; border: 1px solid #2d2c31; opacity: 0.2;"></div>
                                <div class="w-full h-48 rounded-lg" style="background-color: #0e1015; border: 1px solid #2d2c31; opacity: 0.1;"></div>
                            </div>
                        </div>
                        
                        <!-- Recently Viewed Section -->
                        <div class="relative space-y-4" role="region" aria-roledescription="carousel" tabindex="0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-base font-medium sm:text-lg text-white">
                                        <i class="mr-2 text-gray-400 fa-solid fa-clock-rotate-left"></i> Recently Viewed (<span id="recently-viewed-count">1</span>)
                                    </h2>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <a href="#" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 rounded-full text-xs ring-1" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31; color: #9ca3af; hover:background-color: rgba(27, 26, 30, 0.7);">
                                        Show all
                                    </a>
                                    <button type="button" class="recently-viewed-prev inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium whitespace-nowrap hover:bg-gray-800/50 text-white ring-1 shadow-sm text-sm touch-manipulation h-8 w-8 rounded-full p-0 relative top-0 left-0 translate-0 active:translate-y-px" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                        <i class="text-current fa-solid fa-angle-left"></i>
                                        <span class="sr-only">Previous Slide</span>
                                    </button>
                                    <button type="button" class="recently-viewed-next inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium whitespace-nowrap hover:bg-gray-800/50 text-white ring-1 shadow-sm text-sm touch-manipulation h-8 w-8 rounded-full p-0 -right-4 relative top-0 left-0 translate-0 active:translate-y-px" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                        <i class="text-current fa-solid fa-angle-right"></i>
                                        <span class="sr-only">Next Slide</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Swiper Carousel -->
                            <div class="swiper recently-viewed-swiper">
                                <div class="swiper-wrapper">
                                    @php
                                        // Dummy data for recently viewed accounts
                                        $recentlyViewedAccounts = [
                                            (object)[
                                                'id' => 2592919,
                                                'title' => 'Best Offers Mega Colector V🔥467 Skins🔥Lunox Legend🔥Fanny Mikasaa🔥Lance Kishin🔥Valir Gold Saint Seiya🔥CHOU KOF🔥Colector Valir x Arlot🔥Acces Full🔥Cheapest and Safe Account',
                                                'price' => 140.00,
                                                'currency' => 'EUR',
                                                'game' => (object)['name' => 'Mobile Legends', 'slug' => 'mobile-legends', 'logo' => 'https://cdn.gameboost.com/games/logos/mobile-legends.png'],
                                                'status' => 'listed',
                                                'type' => 'account',
                                                'image' => 'https://cdn.gameboost.com/accounts/2592919/gallery/conversions/c26724a7-a3eb-49da-9135-50b89cef8bc9-webp.webp',
                                            ],
                                            (object)[
                                                'id' => 123456,
                                                'title' => 'Premium MLBB Account with 300+ Skins',
                                                'price' => 250.00,
                                                'currency' => 'EUR',
                                                'game' => (object)['name' => 'Mobile Legends', 'slug' => 'mobile-legends', 'logo' => 'https://cdn.gameboost.com/games/logos/mobile-legends.png'],
                                                'status' => 'listed',
                                                'type' => 'account',
                                                'image' => 'https://cdn.gameboost.com/accounts/123456/gallery/conversions/image1.webp',
                                            ],
                                            (object)[
                                                'id' => 789012,
                                                'title' => 'Legendary Account with All Heroes Unlocked',
                                                'price' => 180.00,
                                                'currency' => 'EUR',
                                                'game' => (object)['name' => 'Mobile Legends', 'slug' => 'mobile-legends', 'logo' => 'https://cdn.gameboost.com/games/logos/mobile-legends.png'],
                                                'status' => 'listed',
                                                'type' => 'account',
                                                'image' => 'https://cdn.gameboost.com/accounts/789012/gallery/conversions/image2.webp',
                                            ],
                                            (object)[
                                                'id' => 456789,
                                                'title' => 'Epic MLBB Account with Rare Collectors',
                                                'price' => 320.00,
                                                'currency' => 'EUR',
                                                'game' => (object)['name' => 'Mobile Legends', 'slug' => 'mobile-legends', 'logo' => 'https://cdn.gameboost.com/games/logos/mobile-legends.png'],
                                                'status' => 'listed',
                                                'type' => 'account',
                                                'image' => 'https://cdn.gameboost.com/accounts/456789/gallery/conversions/image3.webp',
                                            ],
                                        ];
                                    @endphp
                                    
                                    @foreach($recentlyViewedAccounts as $account)
                                        <div class="swiper-slide">
                                            <div class="flex relative flex-col h-full overflow-clip rounded-xl ring-1 transition-all group hover:ring-red-500/50" style="background-color: #0e1015; border: 1px solid #2d2c31; hover:background-color: rgba(27, 26, 30, 0.3);">
                                                <!-- Delete Button (Top Right) -->
                                                <div class="flex absolute top-2 right-2 z-10 gap-1">
                                                    <div class="rounded-md transition-opacity duration-300 sm:opacity-0 group-hover:opacity-100" style="background-color: rgba(14, 16, 21, 0.8);">
                                                        <button type="button" class="inline-flex items-center justify-center text-sm transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap w-10 h-10 sm:h-9 sm:w-9 rounded-md ring-1 bg-red-600 hover:bg-red-700 text-white focus:outline-red-600">
                                                            <i class="fa-solid fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Image -->
                                                <div class="overflow-hidden relative h-48" style="background-color: #0e1015;">
                                                    <img src="{{ $account->image }}" class="w-full h-full transition-transform duration-300 group-hover:scale-105 object-cover" loading="lazy" alt="{{ $account->title }}">
                                                    <div class="absolute bottom-3 left-3 size-8">
                                                        <img src="{{ $account->game->logo }}" class="object-contain w-full h-full" alt="{{ $account->game->name }}">
                                                    </div>
                                                </div>
                                                
                                                <!-- Content -->
                                                <div class="flex flex-col flex-1 p-4 space-y-3 border-t" style="border-color: #2d2c31;">
                                                    <div class="space-y-1">
                                                        <h3 class="text-sm font-medium transition-colors text-white line-clamp-2 group-hover:text-gray-300">{{ $account->title }}</h3>
                                                        <p class="text-xs text-gray-400 line-clamp-1">#{{ $account->id }} · {{ $account->game->name }} Account</p>
                                                    </div>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <span class="inline-flex items-center font-medium ring-1 ring-inset px-2 py-1 text-xs rounded-md" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31; color: #9ca3af;">
                                                            <i class="mr-1.5 fa-solid fa-helmet-battle"></i>
                                                            <span class="flex-1 truncate shrink-0">Account</span>
                                                        </span>
                                                        <span class="inline-flex items-center font-medium ring-1 ring-inset px-2 py-1 text-xs rounded-md bg-red-600/20 text-red-400 border-red-500/30">
                                                            <i class="mr-1.5 fa-solid fa-check"></i>
                                                            <span class="flex-1 truncate shrink-0">Listed</span>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Footer with Price and View Button -->
                                                <div class="flex justify-between items-end px-4 py-4 mt-auto border-t" style="border-color: #2d2c31;">
                                                    <div class="flex gap-x-1 items-baseline">
                                                        <span class="text-3xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-l from-white to-gray-400">€{{ number_format($account->price, 2, ',', '.') }}</span>
                                                        <span class="text-sm font-semibold leading-6 text-gray-400">{{ $account->currency }}</span>
                                                    </div>
                                                    <a href="/mobile-legends/accounts/{{ $account->id }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-full ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                                        View <i class="ml-2 fa-regular fa-arrow-up-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Recently Viewed Swiper
        const recentlyViewedSwiper = new Swiper('.recently-viewed-swiper', {
            slidesPerView: 1,
            spaceBetween: 16,
            navigation: {
                nextEl: '.recently-viewed-next',
                prevEl: '.recently-viewed-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 16,
                },
                768: {
                    slidesPerView: 2.2,
                    spaceBetween: 16,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 16,
                },
                1280: {
                    slidesPerView: 4,
                    spaceBetween: 16,
                },
                1536: {
                    slidesPerView: 4.2,
                    spaceBetween: 16,
                },
            },
        });
        
        // Update count
        const count = {{ count($recentlyViewedAccounts) }};
        document.getElementById('recently-viewed-count').textContent = count;
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush
