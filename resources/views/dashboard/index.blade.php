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
                <h1 class="text-3xl font-bold text-white mb-6">{{ __('messages.my_account') }}</h1>
                
                <!-- Menu Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Orders -->
                    <a href="{{ route('account.orders') }}" class="rounded-xl p-6 transition-all hover:scale-105" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg" style="background-color: rgba(59, 130, 246, 0.1);">
                                <i class="fa-solid fa-cart-shopping text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ __('messages.orders') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('messages.view_your_orders') }}</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Chat -->
                    <a href="{{ route('account.chat') }}" class="rounded-xl p-6 transition-all hover:scale-105" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg" style="background-color: rgba(59, 130, 246, 0.1);">
                                <i class="fa-solid fa-message text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ __('messages.chat') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('messages.messages_conversations') }}</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Wallet -->
                    <a href="{{ route('account.wallet') }}" class="rounded-xl p-6 transition-all hover:scale-105" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg" style="background-color: rgba(59, 130, 246, 0.1);">
                                <i class="fa-solid fa-wallet text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ __('messages.wallet') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('messages.balance_transactions') }}</p>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Settings -->
                    <a href="{{ route('account.settings') }}" class="rounded-xl p-6 transition-all hover:scale-105" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg" style="background-color: rgba(59, 130, 246, 0.1);">
                                <i class="fa-solid fa-gear text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ __('messages.settings') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('messages.account_preferences') }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

