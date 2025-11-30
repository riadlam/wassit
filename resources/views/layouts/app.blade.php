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
                    if (window.Alpine && window.Alpine.store) {
                        window.Alpine.store('authModal').open = true;
                        window.Alpine.store('authModal').mode = 'login';
                    } else {
                        // Fallback: dispatch event for Alpine to listen to
                        window.dispatchEvent(new CustomEvent('open-login-modal', {bubbles: true, cancelable: true}));
                    }
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

