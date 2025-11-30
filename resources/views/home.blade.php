@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="absolute inset-0 z-0 pointer-events-none min-h-screen">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('{{ Storage::url('home_page/degaultbanner.webp') }}'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(15, 17, 27, 0.85) 0%, rgba(15, 17, 27, 0.92) 50%, rgba(15, 17, 27, 0.98) 100%);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10">
        <!-- Hero Section -->
        <div class="px-4 py-20 mx-auto max-w-7xl sm:px-6 xl:px-8 overflow-x-clip sm:pb-32 lg:pt-40 lg:pb-20">
            <div class="lg:flex">
                <!-- Hero Text -->
                <div class="flex flex-col justify-center items-center mx-auto max-w-5xl text-center lg:flex-shrink-0 lg:pt-8">
                    <h1 class="max-w-[46.5rem] mt-6 text-3xl font-bold tracking-wide leading-[1.1] text-center text-white sm:text-6xl font-display">
                        {{ __('messages.hero_title_part1') }} <span class="text-transparent bg-clip-text bg-gradient-to-br from-[#A3DFFF] via-[#6CB9FF] to-[#0185FF] max-w-max">{{ __('messages.hero_title_highlight') }}</span>
                        <span class="block">{{ __('messages.hero_title_part2') }}</span>
                    </h1>
                    <div class="items-center mt-4">
                        <h2 class="inline text-sm font-medium text-center text-gray-400 sm:text-lg lg:text-xl">{{ __('messages.hero_subtitle_1') }}</h2>
                        <span class="text-gray-400"> ⸱ </span>
                        <h2 class="inline text-sm font-medium text-center text-gray-400 sm:text-lg lg:text-xl">{{ __('messages.hero_subtitle_2') }}</h2>
                        <span class="text-gray-400"> ⸱ </span>
                        <h2 class="inline text-sm font-medium text-center text-gray-400 sm:text-lg lg:text-xl">{{ __('messages.hero_subtitle_3') }}</h2>
                    </div>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="flex relative z-10 flex-col mx-auto w-full pointer-events-auto lg:w-fit mt-10 mb-8">
                <div class="mx-auto w-full lg:w-fit">
                    <div class="rounded-[12px] w-full px-2 lg:w-[760px] h-[64px] ring-1 flex items-center justify-center relative mx-auto hover:bg-gradient-to-b backdrop-blur-[12px] transition-all" style="background-color: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); hover:background: linear-gradient(to bottom, rgba(188,195,231,0.06), rgba(188,195,231,0.024));">
                        <div class="relative box-border w-full lg:w-[744px] h-12 left-0 top-0 bg-gradient-to-b backdrop-blur-[12px] rounded-lg overflow-hidden ring-1" style="background: linear-gradient(to bottom, rgba(188,195,231,0.08), rgba(188,195,231,0.032)); border-color: rgba(255, 255, 255, 0.1);">
                            <i class="absolute left-4 top-1/2 z-10 -translate-y-1/2 text-gray-400 fa-solid fa-magnifying-glass"></i>
                            <input 
                                class="relative z-10 py-2.5 pr-4 pl-11 w-full h-full bg-transparent rounded-xl border-none ring-0 focus:outline-none focus:ring-0 placeholder:text-gray-400/80 text-white" 
                                type="search" 
                                name="search" 
                                placeholder="{{ __('messages.search_placeholder') }}"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Game Grid -->
        @include('components.game-grid', ['games' => $games])
        
        <!-- FAQ Section -->
        <div class="relative w-full" style="padding-top: 4rem; padding-bottom: 4rem;">
            <!-- Background Image and Overlay - Full Width -->
            <div class="absolute inset-0 z-0 pointer-events-none" style="left: 0; right: 0; width: 100vw; margin-left: calc(50% - 50vw);">
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('storage/game_cards_images/ml.webp') }}');"></div>
                <div class="absolute inset-0" style="background-color: rgba(15, 17, 27, 0.4);"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-20 flex flex-col w-full px-4 mx-auto max-w-7xl xl:px-8 sm:px-6">
                <div class="grid grid-cols-5">
                <!-- Left Side - Title and Description -->
                <div class="flex flex-col items-center justify-center col-span-5 text-center md:text-start md:items-start lg:col-span-2">
                    <h2 class="max-w-md text-4xl font-bold tracking-tight text-white md:max-w-xs font-display">{{ __('messages.faq_title') }}</h2>
                    <p class="max-w-md mt-4 font-medium md:max-w-xs text-gray-300">{{ __('messages.faq_subtitle') }}</p>
                </div>
                
                <!-- Right Side - FAQ Items -->
                <div class="relative flex flex-col col-span-5 mt-12 lg:col-span-3 lg:mt-0" x-data="{ openIndex: null }">
                    @php
                        $faqs = [
                            [
                                'question' => __('messages.faq_1_question'),
                                'answer' => __('messages.faq_1_answer')
                            ],
                            [
                                'question' => __('messages.faq_2_question'),
                                'answer' => __('messages.faq_2_answer')
                            ],
                            [
                                'question' => __('messages.faq_3_question'),
                                'answer' => __('messages.faq_3_answer')
                            ],
                            [
                                'question' => __('messages.faq_4_question'),
                                'answer' => __('messages.faq_4_answer')
                            ],
                            [
                                'question' => __('messages.faq_5_question'),
                                'answer' => __('messages.faq_5_answer')
                            ]
                        ];
                    @endphp
                    
                    @foreach($faqs as $index => $faq)
                        <div 
                            class="w-full rounded-2xl backdrop-blur-md flex z-20 flex-col mt-5 shadow-lg transition-colors"
                            style="background-color: rgba(20, 24, 37, 0.95); border: 1px solid #2d2c31; box-shadow: rgba(255, 255, 255, 0.06) 0px 0px 2px 0px inset;"
                        >
                            <h3 class="flex">
                                <button 
                                    type="button" 
                                    @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                                    class="flex flex-1 w-full p-6 group"
                                >
                                    @if(app()->getLocale() === 'ar')
                                        <i class="mr-auto transition-transform fa-solid fa-chevron-down text-gray-400" :class="openIndex === {{ $index }} ? 'rotate-180' : ''"></i>
                                        <p class="font-semibold text-right text-white flex-1">{{ $faq['question'] }}</p>
                                    @else
                                        <p class="font-semibold text-left text-white flex-1">{{ $faq['question'] }}</p>
                                        <i class="ml-auto transition-transform fa-solid fa-chevron-down text-gray-400" :class="openIndex === {{ $index }} ? 'rotate-180' : ''"></i>
                                    @endif
                                </button>
                            </h3>
                            <div 
                                x-show="openIndex === {{ $index }}"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 max-h-0"
                                x-transition:enter-end="opacity-100 max-h-[500px]"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 max-h-[500px]"
                                x-transition:leave-end="opacity-0 max-h-0"
                                class="text-gray-300 px-6 pb-6 cursor-auto font-medium text-sm {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} flex {{ app()->getLocale() === 'ar' ? 'ml-auto' : 'mr-auto' }} flex-col overflow-hidden"
                            >
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backgroundImage = document.getElementById('background-image');
            const gameCards = document.querySelectorAll('.game-card-group');
            const defaultBanner = "{{ Storage::url('home_page/degaultbanner.webp') }}";
            
            // Map game slugs to banner images
            const bannerMap = {
                'mlbb': "{{ Storage::url('home_page/mlbbbanner.webp') }}",
                // Add more games here as needed: 'pubg-mobile': "{{ Storage::url('home_page/pubgbanner.webp') }}",
            };
            
            // Set default background
            let currentBanner = defaultBanner;
            let hoverTimeout = null;
            
            function changeBackground(newBanner) {
                if (newBanner !== currentBanner) {
                    const bgDiv = backgroundImage.querySelector('div');
                    bgDiv.style.opacity = '0';
                    
                    setTimeout(() => {
                        bgDiv.style.backgroundImage = `url('${newBanner}')`;
                        bgDiv.style.opacity = '1';
                        currentBanner = newBanner;
                    }, 250);
                }
            }
            
            gameCards.forEach(card => {
                const gameSlug = card.getAttribute('data-game-slug');
                if (gameSlug) {
                    const bannerUrl = bannerMap[gameSlug] || defaultBanner;
                    
                    card.addEventListener('mouseenter', function() {
                        clearTimeout(hoverTimeout);
                        changeBackground(bannerUrl);
                    });
                    
                    card.addEventListener('mouseleave', function() {
                        hoverTimeout = setTimeout(() => {
                            changeBackground(defaultBanner);
                        }, 100);
                    });
                }
            });
        });
    </script>
    @endpush
@endsection

