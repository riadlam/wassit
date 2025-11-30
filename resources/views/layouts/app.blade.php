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
</body>
</html>

