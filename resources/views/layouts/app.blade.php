<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Wasit - Gaming Accounts Marketplace by Diaszone' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if(app()->getLocale() === 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    
    <!-- Alpine.js for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Initialize Alpine store for auth modal
        document.addEventListener('alpine:init', () => {
            Alpine.store('authModal', {
                open: false,
                mode: 'login' // 'login' or 'signup'
            });
        });
    </script>
    
    <style>
        @if(app()->getLocale() === 'ar')
            body {
                font-family: 'Cairo', sans-serif;
            }
        @endif
        /* Alpine.js cloak - hide elements until Alpine is initialized */
        [x-cloak] { 
            display: none !important; 
        }
        /* Ensure mobile bottom nav always stays on top */
        @media (max-width: 767px) {
            .mobile-bottom-nav {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 999999 !important;
            }
            footer {
                position: relative !important;
                z-index: 1 !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased" style="background-color: #1b1a1e;">
    <!-- Header -->
    @include('components.header')
    
    <!-- Main Content -->
    <main style="background-color: #1b1a1e;">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('components.footer')
    
    <!-- Sticky Mobile Buy Now Button -->
    <button 
        id="sticky-buy-now-btn"
        type="button" 
        class="hidden md:hidden fixed bottom-0 left-0 right-0 z-50 w-full account-buy-btn-sticky inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-lg focus:outline-red-600 py-3 px-4 text-sm rounded-none"
        style="border-top: 1px solid #2d2c31;"
    >
        <span class="buy-btn-text">Buy Now</span>
        <i class="buy-btn-loading ml-2 hidden" style="display: none;">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </i>
        <i class="ml-1 fa-solid fa-chevron-right buy-btn-icon"></i>
    </button>
    
    <!-- Auth Modal (Global) -->
    @include('components.auth-modal')
    
    @stack('scripts')
    
    <!-- Auto-trigger login modal if redirected from unauthenticated access -->
    @if(session('show_login'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for Alpine.js to be ready
            if (window.Alpine) {
                window.dispatchEvent(new CustomEvent('open-login-modal', {bubbles: true, cancelable: true}));
            } else {
                document.addEventListener('alpine:init', function() {
                    window.dispatchEvent(new CustomEvent('open-login-modal', {bubbles: true, cancelable: true}));
                });
            }
        });
    </script>
    @endif
    
    <!-- Account Card Buy Button Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile sticky Buy Now button logic - for main account only
            const stickyBuyBtn = document.getElementById('sticky-buy-now-btn');
            
            // Look for the main account buy button (the one with id #buy-account-btn)
            const getMainBuyBtn = function() {
                const fastCheckoutBtn = document.querySelector('#buy-account-btn');
                return fastCheckoutBtn;
            };
            
            const checkVisibility = function() {
                const mainBuyBtn = getMainBuyBtn();
                
                // If no main buy button exists on this page, hide sticky and stop checking
                if (!mainBuyBtn) {
                    if (stickyBuyBtn) {
                        stickyBuyBtn.classList.add('hidden');
                    }
                    return;
                }
                
                if (!stickyBuyBtn) return;
                
                const rect = mainBuyBtn.getBoundingClientRect();
                const isInViewport = rect.top < window.innerHeight && rect.bottom > 0;
                
                if (isInViewport) {
                    // Button is visible, hide sticky
                    stickyBuyBtn.classList.add('hidden');
                    console.log('Main button visible, hiding sticky');
                } else {
                    // Button is not visible, show sticky
                    stickyBuyBtn.classList.remove('hidden');
                    console.log('Main button not visible, showing sticky');
                }
            };
            
            // Check visibility on load and scroll
            setTimeout(checkVisibility, 100);
            window.addEventListener('scroll', checkVisibility);
            window.addEventListener('resize', checkVisibility);
            
            // Handle sticky button clicks - trigger the main account button
            if (stickyBuyBtn) {
                stickyBuyBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const mainBuyBtn = getMainBuyBtn();
                    if (mainBuyBtn) {
                        mainBuyBtn.click();
                        console.log('Sticky button clicked, triggered main buy button');
                    } else {
                        console.error('Main buy button not found');
                    }
                });
            }
            
            // Handle buy button clicks for account cards from any location (slider, related accounts, etc.)
            document.addEventListener('click', async function(e) {
                const buyBtn = e.target.closest('.account-buy-btn');
                if (!buyBtn) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                // Check if user is authenticated
                const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
                
                if (!isAuthenticated) {
                    // Show login modal for unauthenticated users
                    console.log('User not authenticated, showing login modal');
                    
                    // Try multiple methods to trigger modal
                    // Method 1: Alpine store
                    if (window.Alpine) {
                        try {
                            window.Alpine.store('authModal').open = true;
                            window.Alpine.store('authModal').mode = 'login';
                            console.log('Modal triggered via Alpine store');
                        } catch (err) {
                            console.error('Failed to trigger via Alpine store:', err);
                        }
                    }
                    
                    // Method 2: Window event
                    window.dispatchEvent(new CustomEvent('open-login-modal', {bubbles: true, cancelable: true}));
                    console.log('Modal event dispatched');
                    
                    return;
                }
                
                const accountId = buyBtn.dataset.accountId;
                const btnText = buyBtn.querySelector('.buy-btn-text');
                const btnLoading = buyBtn.querySelector('.buy-btn-loading');
                const btnIcon = buyBtn.querySelector('.buy-btn-icon');
                
                // Show loading state
                buyBtn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
                if (btnIcon) btnIcon.classList.add('hidden');
                
                try {
                    const response = await fetch(`{{ route('orders.create', ':id') }}`.replace(':id', accountId), {
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
                        if (btnIcon) btnIcon.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Error creating order:', error);
                    alert('An error occurred. Please try again.');
                    // Reset button state
                    buyBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                    if (btnIcon) btnIcon.classList.remove('hidden');
                }
            });
            
            // Prevent default link navigation when clicking the buy button
            document.addEventListener('click', function(e) {
                if (e.target.closest('.account-buy-btn')) {
                    e.preventDefault();
                }
            }, true);
        });
    </script>
</body>
</html>

