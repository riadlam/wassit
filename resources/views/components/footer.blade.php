<footer class="text-white border-t relative z-10 w-full mb-20 md:mb-0" style="background-color: #0e1015; border-color: #2d2c31; padding-top: 2rem; padding-bottom: 2rem;">
    <div class="container mx-auto px-4 sm:px-6 py-8 sm:py-12 max-w-[1550px]">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
            <!-- Brand Section -->
            <div class="mb-4 sm:mb-0">
                <h3 class="text-lg sm:text-xl font-bold mb-2">
                    <span class="text-red-600">Wassit</span>
                    <span class="text-xs text-gray-400 ml-2">by Diaszone</span>
                </h3>
                <p class="text-gray-400 text-xs sm:text-sm mt-2">{{ __('messages.footer_tagline') }}</p>
            </div>
            
            <!-- Quick Links -->
            <div class="mb-4 sm:mb-0">
                <h4 class="font-semibold mb-3 sm:mb-4 text-white text-sm sm:text-base">{{ __('messages.quick_links') }}</h4>
                <ul class="space-y-2 text-xs sm:text-sm">
                    <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.home') }}</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.browse_accounts') }}</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.sell_account') }}</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.how_it_works') }}</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="mb-4 sm:mb-0">
                <h4 class="font-semibold mb-3 sm:mb-4 text-white text-sm sm:text-base">{{ __('messages.support') }}</h4>
                <ul class="space-y-2 text-xs sm:text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.help_center') }}</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.contact_us') }}</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.terms_of_service') }}</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">{{ __('messages.privacy_policy') }}</a></li>
                </ul>
            </div>
            
            <!-- Social Media & Support -->
            <div class="mb-4 sm:mb-0">
                <h4 class="font-semibold mb-3 sm:mb-4 text-white text-sm sm:text-base">{{ __('messages.follow_us') }}</h4>
                <div class="flex space-x-4 mb-4">
                    <a href="#" class="text-gray-400 hover:text-red-600 transition-colors" aria-label="Facebook">
                        <i class="fa-brands fa-facebook text-base sm:text-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-red-600 transition-colors" aria-label="Twitter">
                        <i class="fa-brands fa-twitter text-base sm:text-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-red-600 transition-colors" aria-label="Instagram">
                        <i class="fa-brands fa-instagram text-base sm:text-lg"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-red-600 transition-colors" aria-label="Discord">
                        <i class="fa-brands fa-discord text-base sm:text-lg"></i>
                    </a>
                </div>
                <!-- Support Badges -->
                <div class="flex flex-col gap-2 mt-4">
                    <a href="#" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md w-full sm:w-fit" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);">
                        <i class="mr-2 fa-solid fa-headset"></i> {{ __('messages.support_24_7') }}
                    </a>
                    <a href="#" target="_blank" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md w-full sm:w-fit hover:ring-2" style="background-color: #1877f2; color: #ffffff; border: 1px solid #1877f2;">
                        <i class="mr-2 fa-brands fa-facebook"></i> {{ __('messages.join_fb_group') }}
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="border-t mt-6 sm:mt-8 pt-4 sm:pt-6 text-center text-xs sm:text-sm text-gray-400" style="border-color: #2d2c31;">
            <p>&copy; {{ date('Y') }} Wassit by Diaszone. {{ __('messages.all_rights_reserved') }}</p>
        </div>
    </div>
</footer>

