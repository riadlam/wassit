<header aria-label="Top" class="absolute top-0 z-40 px-4 w-full border-b border-transparent sm:fixed sm:px-6 lg:px-8 before:absolute before:inset-0 lg:before:backdrop-blur-xl before:p-px before:transition-all before:duration-200" style="border-color: rgba(45, 44, 49, 0.3);" 
    x-data="{ 
        mobileMenuOpen: false,
        toggleMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
            // Prevent body scroll when menu is open
            if (this.mobileMenuOpen) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        },
        closeMenu() {
            this.mobileMenuOpen = false;
            document.body.style.overflow = '';
        }
    }"
    @keydown.escape.window="closeMenu()"
>
    <div class="before:absolute before:inset-0 lg:before:backdrop-blur-xl" style="background-color: rgba(14, 16, 21, 0.75);"></div>
    <nav class="relative z-10">
        <div class="flex items-center justify-between h-16 mx-auto max-w-[1550px] px-4 sm:px-6 lg:px-8">
            <!-- Logo and Hamburger (Mobile) -->
            <div class="flex items-center gap-4">
                <!-- Hamburger Menu Button (Mobile Only) -->
                <button 
                    @click="toggleMenu()"
                    class="md:hidden text-gray-300 hover:text-white transition-colors p-2"
                    aria-label="Toggle menu"
                >
                    <i class="fa-solid fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                    <i class="fa-solid fa-times text-xl" x-show="mobileMenuOpen" x-cloak></i>
                </button>
                
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold">
                        <span class="text-red-600">Wassit</span>
                        <span class="text-xs text-gray-400 ml-2 hidden sm:inline">by Diaszone</span>
                    </a>
                </div>
                
                <!-- Navigation Links (Desktop Only) -->
                <div class="hidden md:flex items-center space-x-6 ml-5">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors font-medium">{{ __('messages.home') }}</a>
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors font-medium">{{ __('messages.browse_accounts') }}</a>
                    <a href="{{ (Auth::check() && Auth::user()->role === 'seller') ? route('account.listed-accounts') : route('partner.apply') }}" class="text-gray-300 hover:text-white transition-colors font-medium">{{ __('messages.sell_account') }}</a>
                </div>
            </div>
            
            <!-- Language Switcher and Auth Buttons (Desktop) -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Language Switcher -->
                <div class="relative" x-data="{ open: false }">
                    <button 
                        @click="open = !open" 
                        class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors font-medium px-3 py-2 rounded-lg hover:bg-gray-800/50"
                    >
                        <i class="fa-solid fa-globe"></i>
                        <span>{{ config('app.available_locales')[app()->getLocale()] ?? 'English' }}</span>
                        <i class="fa-solid fa-chevron-down text-xs" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div 
                        x-show="open"
                        x-cloak
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg z-50"
                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                    >
                        <div class="py-1">
                            <a 
                                href="{{ route('locale.switch', 'en') }}" 
                                class="flex items-center px-4 py-2 text-sm transition-colors {{ app()->getLocale() === 'en' ? 'text-white bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}"
                            >
                                <span class="mr-2">🇬🇧</span>
                                <span>English</span>
                                @if(app()->getLocale() === 'en')
                                    <i class="fa-solid fa-check ml-auto text-red-600"></i>
                                @endif
                            </a>
                            <a 
                                href="{{ route('locale.switch', 'ar') }}" 
                                class="flex items-center px-4 py-2 text-sm transition-colors {{ app()->getLocale() === 'ar' ? 'text-white bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}"
                            >
                                <span class="mr-2">🇸🇦</span>
                                <span>العربية</span>
                                @if(app()->getLocale() === 'ar')
                                    <i class="fa-solid fa-check ml-auto text-red-600"></i>
                                @endif
                            </a>
                            <a 
                                href="{{ route('locale.switch', 'fr') }}" 
                                class="flex items-center px-4 py-2 text-sm transition-colors {{ app()->getLocale() === 'fr' ? 'text-white bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}"
                            >
                                <span class="mr-2">🇫🇷</span>
                                <span>Français</span>
                                @if(app()->getLocale() === 'fr')
                                    <i class="fa-solid fa-check ml-auto text-red-600"></i>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                
                @auth
                    <a href="{{ route('account.index') }}" class="text-gray-300 hover:text-white transition-colors font-medium">{{ __('messages.dashboard') }}</a>
                    @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                            {{ __('messages.logout') }}
                        </button>
                    </form>
                    @endif
                @else
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-login-modal', {bubbles: true, cancelable: true}));" class="text-gray-300 hover:text-white transition-colors font-medium">{{ __('messages.login') }}</button>
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-signup-modal', {bubbles: true, cancelable: true}));" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                        {{ __('messages.sign_up') }}
                    </button>
                @endauth
            </div>
            
            <!-- Login Button (Mobile Only) -->
            <div class="md:hidden">
                @auth
                    <a href="{{ route('account.index') }}" class="text-gray-300 hover:text-white transition-colors font-medium text-sm">{{ __('messages.dashboard') }}</a>
                @else
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-login-modal', {bubbles: true, cancelable: true}));" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-medium text-sm">
                        {{ __('messages.login') }}
                    </button>
                @endauth
            </div>
        </div>
    </nav>
    
    <!-- Mobile Side Menu -->
    <div 
        x-show="mobileMenuOpen"
        x-cloak
        @click.away="closeMenu()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-64 md:hidden overflow-y-auto"
        style="background-color: rgba(14, 16, 21, 0.98); border-right: 1px solid rgba(45, 44, 49, 0.3); backdrop-filter: blur(10px);"
    >
        <div class="flex flex-col h-full">
            <!-- Menu Header -->
            <div class="flex items-center justify-between p-4 border-b" style="border-color: rgba(45, 44, 49, 0.3);">
                <a href="{{ route('home') }}" class="text-xl font-bold">
                    <span class="text-red-600">Wassit</span>
                </a>
                <button 
                    @click="closeMenu()"
                    class="text-gray-300 hover:text-white transition-colors p-2"
                    aria-label="Close menu"
                >
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-4">
                <a 
                    href="{{ route('home') }}" 
                    @click="closeMenu()"
                    class="block text-gray-300 hover:text-white transition-colors font-medium py-2"
                >
                    {{ __('messages.home') }}
                </a>
                <a 
                    href="{{ route('home') }}" 
                    @click="closeMenu()"
                    class="block text-gray-300 hover:text-white transition-colors font-medium py-2"
                >
                    {{ __('messages.browse_accounts') }}
                </a>
                <a 
                    href="{{ (Auth::check() && Auth::user()->role === 'seller') ? route('account.listed-accounts') : route('partner.apply') }}" 
                    @click="closeMenu()"
                    class="block text-gray-300 hover:text-white transition-colors font-medium py-2"
                >
                    {{ __('messages.sell_account') }}
                </a>
            </nav>
            
            <!-- Language Switcher -->
            <div class="px-4 py-4 border-t" style="border-color: rgba(45, 44, 49, 0.3);">
                <div class="mb-2 text-sm font-medium text-gray-400">{{ __('messages.language') }}</div>
                <div class="space-y-2">
                    <a 
                        href="{{ route('locale.switch', 'en') }}" 
                        class="flex items-center px-3 py-2 rounded-lg transition-colors {{ app()->getLocale() === 'en' ? 'text-white bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}"
                        @click="closeMenu()"
                    >
                        <span class="mr-2">🇬🇧</span>
                        <span>English</span>
                        @if(app()->getLocale() === 'en')
                            <i class="fa-solid fa-check ml-auto text-red-600"></i>
                        @endif
                    </a>
                    <a 
                        href="{{ route('locale.switch', 'ar') }}" 
                        class="flex items-center px-3 py-2 rounded-lg transition-colors {{ app()->getLocale() === 'ar' ? 'text-white bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}"
                        @click="closeMenu()"
                    >
                        <span class="mr-2">🇸🇦</span>
                        <span>العربية</span>
                        @if(app()->getLocale() === 'ar')
                            <i class="fa-solid fa-check ml-auto text-red-600"></i>
                        @endif
                    </a>
                    <a 
                        href="{{ route('locale.switch', 'fr') }}" 
                        class="flex items-center px-3 py-2 rounded-lg transition-colors {{ app()->getLocale() === 'fr' ? 'text-white bg-red-600/20' : 'text-gray-300 hover:text-white hover:bg-gray-800/50' }}"
                        @click="closeMenu()"
                    >
                        <span class="mr-2">🇫🇷</span>
                        <span>Français</span>
                        @if(app()->getLocale() === 'fr')
                            <i class="fa-solid fa-check ml-auto text-red-600"></i>
                        @endif
                    </a>
                </div>
            </div>
            
            <!-- Auth Section (Mobile) -->
            @auth
                <div class="px-4 py-4 border-t" style="border-color: rgba(45, 44, 49, 0.3);">
                    @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                            {{ __('messages.logout') }}
                        </button>
                    </form>
                    @endif
                </div>
            @endauth
        </div>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div 
        x-show="mobileMenuOpen"
        x-cloak
        @click="closeMenu()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"
    ></div>
</header>

