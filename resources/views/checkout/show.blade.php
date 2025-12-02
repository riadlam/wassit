@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="min-h-screen" style="background-color: #0e1015;">
    <!-- Header -->
    @include('components.header')
    
    <div class="pt-16 pb-20 sm:pb-8" x-data="checkoutPageData()">
        <div class="grid relative grid-cols-1 gap-x-16 mx-auto max-w-7xl lg:grid-cols-2 lg:px-8 lg:pt-16">
            <h4 class="sr-only">Checkout</h4>
            
            <!-- Left Column: Payment Methods -->
            <section aria-labelledby="section-one-heading" class="pt-2.5 pb-6 lg:py-16 lg:mx-auto lg:w-full lg:max-w-lg lg:pb-24 lg:pt-0">
                <div class="px-4 mx-auto max-w-2xl lg:max-w-none lg:px-0">
                    <h2 class="sr-only">Select payment processor</h2>
                    
                    <!-- Logo and Protection Badge -->
                    <div class="mx-auto max-w-2xl lg:max-w-none mb-6" style="position: relative;">
                        <div class="flex justify-between items-center mb-6">
                            <a class="relative" href="{{ route('home') }}">
                                <span class="text-2xl font-bold">
                                    <span class="text-red-600">Wassit</span>
                                    <span class="text-xs text-gray-400 ml-2">by Diaszone</span>
                                </span>
                            </a>
                            
                            @auth
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center rounded-full ring-1 size-10 sm:size-11 focus:outline-none focus:ring-2 focus:ring-primary" style="border-color: rgba(45, 44, 49, 0.5);">
                                    <span class="inline-flex items-center justify-center font-normal text-foreground select-none shrink-0 bg-secondary overflow-hidden text-xs rounded-full size-full">
                                        <img role="img" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=ef4444&color=fff&size=96" class="object-cover w-full h-full aspect-1" alt="{{ Auth::user()->name }}">
                                    </span>
                                </div>
                            </div>
                            @endauth
                        </div>
                        
                        <!-- Protection Badge -->
                        <div class="flex items-center gap-2 text-sm text-gray-300 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="12" viewBox="0 0 11 12" fill="none" class="text-red-600">
                                <path d="M5.49991 0.352869C5.60017 0.352869 5.69327 0.374928 5.77921 0.419045L9.83975 2.18375C10.0832 2.28669 10.2766 2.44846 10.4198 2.66905C10.5774 2.88963 10.6562 3.14699 10.6562 3.4411C10.6705 4.1911 10.5487 5.07346 10.2909 6.08816C10.0331 7.10287 9.56762 8.08816 8.89444 9.04405C8.23559 10.0146 7.29027 10.8161 6.0585 11.4485C5.68611 11.6249 5.31371 11.6249 4.94132 11.4485C3.70955 10.8161 2.76423 10.0146 2.10538 9.04405C1.4322 8.08816 0.966707 7.10287 0.708894 6.08816C0.451082 5.07346 0.329337 4.1911 0.34366 3.4411C0.34366 3.14699 0.422436 2.88963 0.579988 2.66905C0.723217 2.44846 0.916577 2.28669 1.16007 2.18375L5.22061 0.419045C5.30655 0.374928 5.39965 0.352869 5.49991 0.352869ZM5.49991 1.83081V10.169C6.47387 9.65434 7.24014 8.99993 7.79874 8.20581C8.34301 7.41169 8.72973 6.59552 8.95889 5.75728C9.17374 4.90434 9.28116 4.13963 9.28116 3.46316L5.49991 1.83081Z" fill="currentColor"></path>
                            </svg>
                            <span>Your account purchase is protected under Wassit's Comprehensive Warranty.</span>
                        </div>
                    </div>
                    
                    <!-- Billing Details -->
                    <div class="mt-6 mb-6">
                        <h3 class="mb-2 text-lg font-medium text-white">Billing details</h3>
                        <div class="w-full">
                            <div class="flex justify-between mb-1">
                                <label class="flex items-center gap-2 text-sm font-medium leading-6 text-gray-300">Full Name</label>
                            </div>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    placeholder="e.g: John Doe" 
                                    class="flex-grow w-full block w-full border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500" 
                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    value="{{ Auth::check() ? Auth::user()->name : '' }}"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Methods -->
                    <div class="mt-6" x-data="{ selectedPayment: 'algerie-poste' }" x-init="selectedPayment = 'algerie-poste'">
                        <h3 class="text-lg font-medium text-white mb-2">Pay with</h3>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <!-- Debit/Credit Card (Coming Soon) -->
                            <div class="w-full relative opacity-75 cursor-not-allowed pointer-events-none">
                                <div class="relative flex justify-between w-full px-6 py-4 rounded-lg text-start bg-gray-800/50 border-2 border-transparent">
                                    <span class="flex items-center">
                                        <span class="flex-grow-0 mr-2 sm:mr-4 shrink-0">
                                            <i class="fa-solid fa-credit-card text-2xl text-gray-300"></i>
                                        </span>
                                        <span class="flex flex-col sm:max-w-lg max-w-[250px] text-sm truncate">
                                            <span class="text-base font-medium text-white">Debit/Credit cards</span>
                                            <span class="text-gray-400 text-sm">
                                                <span class="block truncate sm:inline">We accept all major debit and credit cards.</span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="flex items-center justify-center mt-2 text-sm sm:ml-4 sm:mt-0 sm:flex-col sm:text-right">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 text-gray-500" viewBox="0 0 512 512" fill="currentColor">
                                            <path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"></path>
                                        </svg>
                                    </span>
                                    <!-- Coming Soon Overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/70 rounded-lg">
                                        <span class="text-white font-bold text-base sm:text-lg">{{ __('messages.coming_soon') ?? 'Coming Soon' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Crypto (Coming Soon) -->
                            <div class="w-full relative opacity-75 cursor-not-allowed pointer-events-none">
                                <div class="relative flex justify-between w-full px-6 py-4 rounded-lg text-start bg-gray-800/50 border-2 border-transparent">
                                    <span class="flex items-center">
                                        <span class="flex-grow-0 mr-2 sm:mr-4 shrink-0">
                                            <i class="fa-brands fa-bitcoin text-2xl text-yellow-500"></i>
                                        </span>
                                        <span class="flex flex-col sm:max-w-lg max-w-[250px] text-sm truncate">
                                            <span class="text-base font-medium text-white">Crypto</span>
                                            <span class="text-gray-400 text-sm">
                                                <span class="block truncate sm:inline">BTC · ETH · USDT · USDC and more!</span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="flex items-center justify-center mt-2 text-sm sm:ml-4 sm:mt-0 sm:flex-col sm:text-right">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 text-gray-500" viewBox="0 0 512 512" fill="currentColor">
                                            <path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"></path>
                                        </svg>
                                    </span>
                                    <!-- Coming Soon Overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/70 rounded-lg">
                                        <span class="text-white font-bold text-base sm:text-lg">{{ __('messages.coming_soon') ?? 'Coming Soon' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Algerie Poste -->
                            <label class="w-full cursor-pointer" @click="selectedPayment = 'algerie-poste'">
                                <div class="relative flex justify-between w-full px-6 py-4 rounded-lg focus:outline-none text-start transition-colors" 
                                     :class="selectedPayment === 'algerie-poste' ? 'bg-red-600/20 shadow-sm border-2 border-red-600' : 'bg-gray-800/50 hover:bg-gray-800 border-2 border-transparent'">
                                    <span class="flex items-center">
                                        <span class="flex-grow-0 mr-2 sm:mr-4 shrink-0">
                                            <img src="{{ asset('storage/home_page/barid_jazair.png') }}" alt="Algerie Poste" class="w-10 h-10 object-contain flex-shrink-0">
                                        </span>
                                        <span class="flex flex-col sm:max-w-lg max-w-[250px] text-sm truncate">
                                            <span class="text-base font-medium text-white">Algerie Poste</span>
                                            <span class="text-gray-400 text-sm">
                                                <span class="block truncate sm:inline">Pay via Barid Jazair</span>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="flex items-center justify-center mt-2 text-sm sm:ml-4 sm:mt-0 sm:flex-col sm:text-right">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4" :class="selectedPayment === 'algerie-poste' ? 'text-white' : 'text-gray-500'" viewBox="0 0 512 512" fill="currentColor">
                                            <path v-if="selectedPayment === 'algerie-poste'" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"></path>
                                            <path v-else d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Security Badge -->
                    <div class="mt-4 mb-10 text-center">
                        <span class="text-xs text-gray-400">
                            <i class="mr-1 fa-regular fa-lock"></i> 256-bit SSL Encrypted payment. You're safe.
                        </span>
                    </div>
                </div>
            </section>
            
            <!-- Right Column: Order Summary -->
            <section aria-labelledby="section-two-heading" class="py-6 sm:py-8 md:py-12 md:px-6 lg:mx-auto lg:w-full lg:max-w-lg lg:bg-transparent lg:px-0 lg:pb-24 lg:pt-0">
                <div class="px-4 sm:px-6 mx-auto max-w-2xl lg:max-w-none lg:px-0">
                    <h2 class="sr-only">Order summary</h2>
                    
                    <!-- Order Summary Card -->
                    <div class="rounded-xl p-4 sm:p-6" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                        <!-- Header -->
                        <div class="mb-6">
                            <h3 class="text-xl sm:text-2xl font-semibold text-white mb-4">Your Order</h3>
                            
                            <!-- Seller Info -->
                            @if($order->seller && $order->seller->user)
                                <div class="flex items-center gap-3 mb-4 pb-4 border-b" style="border-color: #2d2c31;">
                                    <div class="relative shrink-0">
                                        <span class="inline-flex items-center justify-center font-normal text-white select-none shrink-0 overflow-hidden h-10 w-10 sm:h-12 sm:w-12 text-xs rounded-full ring-1" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                            <img role="img" src="{{ $sellerPfp }}" class="object-cover w-full h-full aspect-1 rounded-full" alt="{{ $order->seller->user->name ?? 'Seller' }}" onerror="this.onerror=null; this.src='{{ asset('storage/examplepfp.webp') }}';">
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs sm:text-sm font-medium text-gray-400 mb-0.5">Seller</p>
                                        <p class="text-sm sm:text-base font-semibold text-white truncate">{{ $order->seller->user->name ?? 'Unknown Seller' }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            <dl>
                                <dt class="text-xs sm:text-sm font-medium text-gray-400">Amount due</dt>
                                <dd class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-white">
                                    {{ number_format($total, 0, '.', '') }} <span class="text-sm sm:text-base font-semibold text-gray-400">DZD</span>
                                </dd>
                            </dl>
                        </div>
                        
                        <!-- Order Item -->
                        <div class="mb-6 pb-6 border-b" style="border-color: #2d2c31;">
                            <div class="flex items-start gap-3 sm:gap-4">
                                @if($accountImage)
                                    <img alt="{{ $order->account->title }}" class="flex-none object-cover object-center w-16 h-16 sm:w-20 sm:h-20 rounded-lg ring-1" style="border-color: #2d2c31;" src="{{ asset('storage/' . $accountImage->url) }}">
                                @else
                                    <div class="flex-none w-16 h-16 sm:w-20 sm:h-20 rounded-lg flex items-center justify-center ring-1" style="background-color: #1b1a1e; border-color: #2d2c31;">
                                        <i class="fa-solid fa-image text-gray-500 text-xl"></i>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0 space-y-2">
                                    <h3 class="text-sm sm:text-base font-semibold text-white line-clamp-2 leading-tight">{{ $order->account->title }}</h3>
                                    <div class="flex flex-wrap gap-2 sm:gap-3">
                                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                            @if(Storage::disk('public')->exists('game_logos/' . $order->account->game->slug . '.png'))
                                                <img src="{{ asset('storage/game_logos/' . $order->account->game->slug . '.png') }}" alt="{{ $order->account->game->name }}" class="w-4 h-4 object-contain flex-shrink-0">
                                            @else
                                                <i class="fa-solid fa-gamepad text-gray-400 flex-shrink-0"></i>
                                            @endif
                                            <span class="truncate">{{ $order->account->game->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                            <i class="fa-solid fa-bolt text-red-600 flex-shrink-0"></i>
                                            <span class="truncate">Instant delivery</span>
                                        </div>
                                    </div>
                                    <div class="pt-2">
                                        <p class="text-base sm:text-lg font-bold text-white">{{ number_format($subtotal, 0, '.', '') }} <span class="text-xs sm:text-sm font-semibold text-gray-400">DZD</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Summary Breakdown -->
                        <dl class="space-y-3 sm:space-y-4 text-sm">
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-400 text-xs sm:text-sm">Subtotal</dt>
                                <dd class="text-white font-medium text-xs sm:text-sm">{{ number_format($subtotal, 0, '.', '') }} DZD</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-gray-400 text-xs sm:text-sm flex items-center gap-1">
                                    Processor Fee
                                    <i class="fa-solid fa-circle-question text-gray-500 text-xs" title="Payment processing fee"></i>
                                </dt>
                                <dd class="text-white font-medium text-xs sm:text-sm">+{{ $processorFeePercent }}%</dd>
                            </div>
                            <div class="pt-4 border-t" style="border-color: #2d2c31;">
                                <div class="flex justify-between items-center">
                                    <dt class="text-base sm:text-lg font-semibold text-white">Total</dt>
                                    <dd class="text-base sm:text-lg font-bold text-white">{{ number_format($total, 0, '.', '') }} <span class="text-xs sm:text-sm font-semibold text-gray-400">DZD</span></dd>
                                </div>
                            </div>
                        </dl>
                        
                        <!-- Pay Now Button (Desktop) -->
                        <button type="button" class="w-full items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap text-white focus:outline-primary py-3 sm:py-3.5 px-5 text-sm sm:text-base font-semibold mt-6 rounded-lg hidden sm:flex hover:brightness-110" 
                                style="background-color: #dc2626; box-shadow: 0 4px 14px 0 rgba(220, 38, 38, 0.3);"
                                @click="handlePayment()"
                                :disabled="isProcessing"
                                x-ref="payButton">
                            <span x-show="!isProcessing">
                                Pay Now
                                <i class="ml-2 fas fa-arrow-right"></i>
                            </span>
                            <span x-show="isProcessing" class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
        
        <!-- Pay Now Button (Mobile - Fixed Bottom) -->
        <div class="fixed bottom-0 left-0 right-0 flex justify-center w-full px-4 py-3 border-t gap-x-2.5 sm:hidden z-50" style="background-color: #0e1015; border-color: #2d2c31; backdrop-filter: blur-xl; box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);">
            <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap text-white focus:outline-primary py-3 px-5 text-sm font-semibold rounded-lg flex-1 hover:brightness-110" 
                    style="background-color: #dc2626; box-shadow: 0 4px 14px 0 rgba(220, 38, 38, 0.3);"
                    @click="handlePayment()"
                    :disabled="isProcessing"
                    x-ref="payButtonMobile">
                <span x-show="!isProcessing">
                    Pay Now <span class="mx-1.5"> · </span> {{ number_format($total, 0, '.', '') }} DZD
                </span>
                <span x-show="isProcessing" class="flex items-center gap-2">
                    <i class="fas fa-spinner fa-spin"></i>
                    Processing...
                </span>
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Checkout Page Specific Styles */
    @media (max-width: 1023px) {
        /* On mobile/tablet, ensure order summary has proper spacing */
        section[aria-labelledby="section-two-heading"] {
            margin-top: 2rem;
        }
    }
    
    /* Smooth transitions for payment method selection */
    [x-data] label > div {
        transition: all 0.2s ease-in-out;
    }
    
    /* Mobile bottom button spacing */
    @media (max-width: 640px) {
        .min-h-screen {
            padding-bottom: 80px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function checkoutPageData() {
        return {
            selectedPayment: 'algerie-poste',
            isProcessing: false,
            encryptedOrderId: '{{ $encryptedOrderId }}',
            
            handlePayment() {
                this.isProcessing = true;
                
                // Only Barid Jazair (edahabia) is supported
                if (this.selectedPayment !== 'algerie-poste') {
                    alert('This payment method is not yet available');
                    this.isProcessing = false;
                    return;
                }
                
                // Initiate payment
                fetch('{{ route('payment.initiate', $encryptedOrderId) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        payment_method: 'edahabia',
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.checkout_url) {
                        // Redirect to Chargily checkout
                        window.location.href = data.checkout_url;
                    } else {
                        alert(data.message || 'Failed to initiate payment. Please try again.');
                        this.isProcessing = false;
                    }
                })
                .catch(error => {
                    console.error('Payment error:', error);
                    alert('An error occurred while processing your payment. Please try again.');
                    this.isProcessing = false;
                });
            }
        };
    }
</script>
@endpush
@endsection

