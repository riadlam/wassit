<!-- Auth Modal Component -->
<div x-data="{
    loginModalOpen: false,
    isSignup: false,
    init() {
        // Ensure modal is hidden on init
        this.loginModalOpen = false;
        
        // Watch the store
        this.$watch('$store.authModal.open', (value) => {
            if (value) {
                if (this.$store.authModal.mode === 'login') {
                    this.openLogin();
                } else if (this.$store.authModal.mode === 'signup') {
                    this.openSignup();
                }
            } else {
                this.loginModalOpen = false;
            }
        });
        
        this.$watch('loginModalOpen', value => {
            if (value) {
                document.body.classList.add('modal-open');
                // Force show the modal
                this.$nextTick(() => {
                    const modal = this.$el;
                    if (modal) {
                        // Ensure it's visible
                        modal.style.display = 'block';
                    }
                });
            } else {
                document.body.classList.remove('modal-open');
            }
        });
        
        // Also listen to window events for compatibility
        const self = this;
        const handleLoginModal = function(event) {
            self.openLogin();
        };
        const handleSignupModal = function(event) {
            self.openSignup();
        };
        
        // Use capture phase to ensure we catch the event
        window.addEventListener('open-login-modal', handleLoginModal, true);
        window.addEventListener('open-signup-modal', handleSignupModal, true);
        
        // Clean up on component destroy
        this.$el.addEventListener('alpine:destroy', () => {
            window.removeEventListener('open-login-modal', handleLoginModal, true);
            window.removeEventListener('open-signup-modal', handleSignupModal, true);
        });
    },
        showOTP: false,
        otpCode: '',
        username: '',
        password: '',
        email: '',
        countdown: 0,
        loading: false,
        error: null,
        success: null,
        openLogin() {
            this.loginModalOpen = true;
            this.isSignup = false;
            this.resetForm();
        },
        openSignup() {
            this.loginModalOpen = true;
            this.isSignup = true;
            this.resetForm();
        },
        resetForm() {
            this.showOTP = false;
            this.otpCode = '';
            this.username = '';
            this.password = '';
            this.email = '';
            this.countdown = 0;
            this.error = null;
            this.success = null;
        },
        async handleLogin(event) {
            event.preventDefault();
            this.loading = true;
            this.error = null;
            
            const formData = new FormData(event.target);
            const data = {
                email: formData.get('email'),
                password: formData.get('password'),
                remember: formData.get('remember') === 'on'
            };
            
            try {
                const response = await fetch('{{ route('login') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.success = result.message || 'Logged in successfully';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.error = result.message || 'Invalid email or password';
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0];
                        this.error = Array.isArray(firstError) ? firstError[0] : firstError;
                    }
                }
            } catch (error) {
                this.error = 'An error occurred. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        async handleSignup(event) {
            event.preventDefault();
            
            // Validate required fields
            if (!this.email || !this.password) {
                this.error = 'Email and password are required';
                return;
            }
            
            // For now, skip OTP and directly register
            // In production, you would implement OTP verification here
            // if (!this.showOTP) {
            //     this.showOTP = true;
            //     this.startOTPCountdown();
            //     return;
            // }
            
            this.loading = true;
            this.error = null;
            
            const data = {
                email: this.email,
                password: this.password,
                username: this.username || null
            };
            
            try {
                const response = await fetch('{{ route('register') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.success = result.message || 'Account created successfully';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.error = result.message || 'Registration failed';
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0];
                        this.error = Array.isArray(firstError) ? firstError[0] : firstError;
                    }
                }
            } catch (error) {
                this.error = 'An error occurred. Please try again.';
                console.error('Registration error:', error);
            } finally {
                this.loading = false;
            }
        },
        startOTPCountdown() {
            this.countdown = 60;
            const interval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(interval);
                }
            }, 1000);
        },
        resendOTP() {
            this.startOTPCountdown();
            // In production, resend OTP to email
        }
    }"
    x-show="loginModalOpen"
    x-cloak
    @keydown.escape.window="loginModalOpen = false; resetForm()"
    class="fixed inset-0 z-[100] overflow-y-auto"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="loginModalOpen = false; resetForm()"></div>
    
    <!-- Modal Content -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            class="relative w-full max-w-4xl rounded-xl overflow-hidden"
            style="background-color: #0e1015; border: 1px solid #2d2c31;"
            @click.away="loginModalOpen = false; resetForm()"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="overflow-y-auto max-h-[calc(100svh-150px)] relative login-modal-scroll">
                <div class="sticky -top-7 -mt-10 w-full h-10 bg-gradient-to-b" style="background: linear-gradient(to bottom, #0e1015, transparent);"></div>
                <div class="flex overflow-hidden w-full rounded-xl items-stretch">
                    <!-- Left Side - Image/Video (Hidden on mobile) -->
                    <div 
                        class="hidden order-1 bg-center bg-cover bg-no-repeat rounded-r-lg md:block md:w-1/2 flex-shrink-0 self-stretch transition-all duration-300" 
                        style="background-image: url('{{ asset('storage/home_page/degaultbanner.webp') }}'); background-color: #1b1a1e;"
                    ></div>
                    
                    <!-- Right Side - Login/Signup Form -->
                    <div class="p-8 py-12 mx-auto w-full sm:p-14 order-0 md:w-1/2 sm:max-w-lg flex flex-col">
                        <!-- Error/Success Messages -->
                        <div x-show="error" x-cloak class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                            <span x-text="error"></span>
                        </div>
                        <div x-show="success" x-cloak class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                            <span x-text="success"></span>
                        </div>
                        
                        <!-- Login Form -->
                        <div x-show="!isSignup">
                            <h2 class="mb-2 text-3xl font-semibold text-white">{{ __('messages.login') }}</h2>
                            <p class="text-sm text-gray-400 mb-2">{{ __('messages.please_login_first') }}</p>
                            <p class="text-sm text-gray-400">{{ __('messages.dont_have_account') }} <button @click="isSignup = true; resetForm()" class="text-white hover:underline">{{ __('messages.create_one_here') }}</button></p>
                            
                            <div class="mt-8">
                                <div class="space-y-8">
                                    <!-- Social Login Buttons -->
                                    <div class="flex justify-center">
                                        <a href="{{ route('auth.google') }}" class="social-login-btn inline-flex items-center justify-center focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 px-8 text-sm rounded-md ring-1 google" style="background-color: #1b1a1e !important; border: 1px solid #2d2c31 !important; color: #ffffff !important;">
                                            <i class="text-base fa-brands fa-google mr-2" style="color: #ffffff !important;"></i>
                                            <span style="color: #ffffff !important;">{{ __('messages.login_with_google') }}</span>
                                        </a>
                                    </div>
                                    
                                    <!-- Divider -->
                                    <div class="flex items-center justify-between">
                                        <span class="w-1/5 border-b lg:w-1/4" style="border-color: #2d2c31;"></span>
                                        <div class="text-xs text-center capitalize text-gray-400">{{ __('messages.or_login_with_email') }}</div>
                                        <span class="w-1/5 border-b lg:w-1/4" style="border-color: #2d2c31;"></span>
                                    </div>
                                    
                                    <!-- Email/Password Form -->
                                    <form @submit.prevent="handleLogin">
                                        <div class="w-full">
                                            <div class="relative mb-4">
                                                <input 
                                                    name="email"
                                                    placeholder="{{ __('messages.email_address') }}" 
                                                    autofocus 
                                                    required
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    type="email" 
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                >
                                            </div>
                                            <div class="relative">
                                                <input 
                                                    name="password"
                                                    placeholder="{{ __('messages.password') }}" 
                                                    required
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    type="password" 
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                >
                                            </div>
                                        </div>
                                        <button 
                                            type="submit" 
                                            :disabled="loading"
                                            class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 sm:py-2.5 px-5 text-sm rounded-md w-full mt-4"
                                        >
                                            <span x-show="!loading">{{ __('messages.continue') }}</span>
                                            <span x-show="loading" x-cloak>
                                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> {{ __('messages.loading') }}...
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Signup Form -->
                        <div x-show="isSignup">
                            <h2 class="mb-2 text-3xl font-semibold text-white">{{ __('messages.sign_up') }}</h2>
                            <p class="text-sm text-gray-400 mb-2">{{ __('messages.create_account_to_chat') }}</p>
                            <p class="text-sm text-gray-400">{{ __('messages.already_have_account') }} <button @click="isSignup = false; resetForm()" class="text-white hover:underline">{{ __('messages.login_here') }}</button></p>
                            
                            <div class="mt-8">
                                <div class="space-y-8">
                                    <!-- Social Signup Buttons -->
                                    <div class="flex justify-center">
                                        <a href="{{ route('auth.google') }}" class="social-login-btn inline-flex items-center justify-center focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 px-8 text-sm rounded-md ring-1 google" style="background-color: #1b1a1e !important; border: 1px solid #2d2c31 !important; color: #ffffff !important;">
                                            <i class="text-base fa-brands fa-google mr-2" style="color: #ffffff !important;"></i>
                                            <span style="color: #ffffff !important;">{{ __('messages.sign_up_with_google') }}</span>
                                        </a>
                                    </div>
                                    
                                    <!-- Divider -->
                                    <div class="flex items-center justify-between">
                                        <span class="w-1/5 border-b lg:w-1/4" style="border-color: #2d2c31;"></span>
                                        <div class="text-xs text-center capitalize text-gray-400">{{ __('messages.or_sign_up_with_email') }}</div>
                                        <span class="w-1/5 border-b lg:w-1/4" style="border-color: #2d2c31;"></span>
                                    </div>
                                    
                                    <!-- Signup Form -->
                                    <form @submit.prevent="handleSignup">
                                        <div class="w-full">
                                            <div class="relative mb-4">
                                                <input 
                                                    x-model="username"
                                                    placeholder="{{ __('messages.username_optional') }}" 
                                                    autofocus 
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    type="text" 
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                >
                                            </div>
                                            <div class="relative mb-4">
                                                <input 
                                                    x-model="email"
                                                    placeholder="{{ __('messages.email_address') }}" 
                                                    required
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    type="email" 
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                >
                                            </div>
                                            <div class="relative mb-4">
                                                <input 
                                                    x-model="password"
                                                    placeholder="{{ __('messages.password') }}" 
                                                    required
                                                    minlength="8"
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    type="password" 
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                >
                                            </div>
                                        </div>
                                        
                                        <!-- OTP Section -->
                                        <div x-show="showOTP" x-transition class="mb-4">
                                            <div class="relative mb-4">
                                                <input 
                                                    x-model="otpCode"
                                                    placeholder="{{ __('messages.enter_otp_code') }}" 
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    type="text" 
                                                    maxlength="6"
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                >
                                            </div>
                                            <div class="flex items-center justify-between mb-4">
                                                <p class="text-xs text-gray-400">
                                                    <span x-show="countdown > 0">{{ __('messages.resend_otp_in') }} <span x-text="countdown"></span>s</span>
                                                    <span x-show="countdown <= 0">
                                                        <button @click="resendOTP()" type="button" class="text-red-600 hover:text-red-700 hover:underline">{{ __('messages.resend_otp') }}</button>
                                                    </span>
                                                </p>
                                            </div>
                                            <button 
                                                type="submit" 
                                                :disabled="loading"
                                                class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 sm:py-2.5 px-5 text-sm rounded-md w-full mb-4"
                                            >
                                                <span x-show="!loading">{{ __('messages.confirm') }}</span>
                                                <span x-show="loading" x-cloak>
                                                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> {{ __('messages.loading') }}...
                                                </span>
                                            </button>
                                        </div>
                                        
                                        <p class="text-xs text-gray-400 mb-4 text-center">
                                            {{ __('messages.by_creating_account') }} <a href="#" class="text-white hover:underline">{{ __('messages.terms_of_service') }}</a> & <a href="#" class="text-white hover:underline">{{ __('messages.privacy_policy') }}</a>
                                        </p>
                                        <button 
                                            type="submit" 
                                            x-show="!showOTP" 
                                            :disabled="loading"
                                            class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 sm:py-2.5 px-5 text-sm rounded-md w-full"
                                        >
                                            <span x-show="!loading">{{ __('messages.continue') }}</span>
                                            <span x-show="loading" x-cloak>
                                                <i class="fa-solid fa-spinner fa-spin mr-2"></i> {{ __('messages.loading') }}...
                                            </span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Close Button -->
            <button 
                type="button" 
                @click="loginModalOpen = false; resetForm()"
                class="inline-flex items-center justify-center text-sm transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden font-medium active:translate-y-px whitespace-nowrap absolute top-3 right-3 z-10 p-1 w-7 h-7 rounded-full"
                style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.8); border: 1px solid rgba(45, 44, 49, 0.5);"
            >
                <span class="sr-only">{{ __('messages.close') }}</span>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    a.social-login-btn.google,
    a.social-login-btn.google *,
    a.social-login-btn.google span,
    a.social-login-btn.google i {
        color: #ffffff !important;
    }
    a.social-login-btn.google {
        background-color: #1b1a1e !important;
        border: 1px solid #2d2c31 !important;
    }
    a.social-login-btn.google:hover,
    a.social-login-btn.google:hover *,
    a.social-login-btn.google:hover span,
    a.social-login-btn.google:hover i {
        color: #ffffff !important;
        background-color: #2d2c31 !important;
        border-color: #3d3c41 !important;
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
</style>
@endpush

