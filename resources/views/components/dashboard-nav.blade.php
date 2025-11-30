@php
    $currentRoute = request()->route()->getName() ?? 'account.index';
    $user = auth()->user();
    $isSeller = $user && $user->role === 'seller';
@endphp

<!-- Desktop Navigation (Top Bar) -->
<div class="hidden md:block fixed z-40 w-full px-4 border-t border-b h-[58px] top-16 border-border/50 sm:px-6 lg:px-8 before:absolute before:inset-0 transition-all duration-200 before:backdrop-blur-xl before:p-px before:transition-all before:duration-200" style="background-color: rgba(14, 16, 21, 0.75); border-color: rgba(45, 44, 49, 0.5);">
    <div class="flex items-center justify-between sm:justify-normal mx-auto max-w-full lg:max-w-[1550px] overflow-clip relative h-full z-10">
        <!-- User Info (Left) -->
        <div class="flex items-center h-full xl:w-[30%] sm:gap-x-1">
            <a class="flex gap-x-3 items-center" href="{{ route('account.index') }}">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'User') }}&background=ef4444&color=fff&size=96" alt="{{ $user->name ?? 'User' }}" class="rounded ring-1 size-8" style="border-color: rgba(45, 44, 49, 0.5);">
                <div class="text-xl font-bold tracking-tight truncate text-white">{{ $user->name ?? 'User' }}</div>
            </a>
        </div>
        
        <!-- Navigation Links (Center - Desktop) -->
        <div class="items-center hidden justify-center h-full w-full lg:flex flex-1 xl:w-[40%]">
            <a class="flex relative gap-x-2 items-center h-full text-sm font-medium leading-6 group px-4 py-2 backdrop-blur-sm z-[1] transition-colors {{ $currentRoute === 'account.orders' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.orders') }}">
                <i class="text-base fa-solid fa-cart-shopping {{ $currentRoute === 'account.orders' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
                <span class="truncate">{{ __('messages.orders') }}</span>
                @if($currentRoute === 'account.orders')
                    <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full bottom-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                    <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -bottom-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
                @endif
            </a>
            <a class="flex relative gap-x-2 items-center h-full text-sm font-medium leading-6 group px-4 py-2 backdrop-blur-sm z-[1] transition-colors {{ $currentRoute === 'account.chat' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.chat') }}">
                <i class="text-base fa-solid fa-message {{ $currentRoute === 'account.chat' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
                <span class="truncate">{{ __('messages.chat') }}</span>
                @if($currentRoute === 'account.chat')
                    <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full bottom-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                    <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -bottom-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
                @endif
            </a>
            <a class="flex relative gap-x-2 items-center h-full text-sm font-medium leading-6 group px-4 py-2 backdrop-blur-sm z-[1] transition-colors {{ $currentRoute === 'account.wallet' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.wallet') }}">
                <i class="text-base fa-solid fa-wallet {{ $currentRoute === 'account.wallet' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
                <span class="truncate">{{ __('messages.wallet') }}</span>
                @if($currentRoute === 'account.wallet')
                    <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full bottom-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                    <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -bottom-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
                @endif
            </a>
            <a class="flex relative gap-x-2 items-center h-full text-sm font-medium leading-6 group px-4 py-2 backdrop-blur-sm z-[1] transition-colors {{ $currentRoute === 'account.library' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.library') }}">
                <i class="text-base fa-solid fa-books {{ $currentRoute === 'account.library' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
                <span class="truncate">{{ __('messages.library') }}</span>
                @if($currentRoute === 'account.library')
                    <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full bottom-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                    <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -bottom-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
                @endif
            </a>
            @if($isSeller)
            <a class="flex relative gap-x-2 items-center h-full text-sm font-medium leading-6 group px-4 py-2 backdrop-blur-sm z-[1] transition-colors {{ $currentRoute === 'account.listed-accounts' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.listed-accounts') }}">
                <i class="text-base fa-solid fa-list {{ $currentRoute === 'account.listed-accounts' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
                <span class="truncate">{{ __('messages.listed_accounts') }}</span>
                @if($currentRoute === 'account.listed-accounts')
                    <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full bottom-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                    <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -bottom-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
                @endif
            </a>
            @endif
            <a class="flex relative gap-x-2 items-center h-full text-sm font-medium leading-6 group px-4 py-2 backdrop-blur-sm z-[1] transition-colors {{ $currentRoute === 'account.settings' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.settings') }}">
                <i class="text-base fa-solid fa-gear {{ $currentRoute === 'account.settings' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
                <span class="truncate">{{ __('messages.settings') }}</span>
                @if($currentRoute === 'account.settings')
                    <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full bottom-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                    <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -bottom-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
                @endif
            </a>
        </div>
        
        <!-- Support Buttons (Right - Desktop) -->
        <div class="sm:ml-auto ml-2 flex gap-x-2 h-8 xl:w-[30%] xl:flex lg:hidden justify-end">
            <a class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md" style="background-color: rgba(27, 26, 30, 0.5); color: rgba(255, 255, 255, 0.9); border: 1px solid rgba(45, 44, 49, 0.5);" href="#">
                <i class="mr-2 fa-solid fa-headset"></i> {{ __('messages.support_24_7') }}
            </a>
            <a href="#" class="justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md !bg-[#5865f2] hidden sm:flex items-center hover:!ring-[#5865f2] hover:!bg-[#6773f4] !text-white" target="_blank">
                <i class="sm:mr-2 fa-brands fa-discord"></i> {{ __('messages.join_discord') }}
            </a>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation Bar -->
<div class="mobile-bottom-nav md:hidden border-t" style="background-color: rgba(14, 16, 21, 0.95); border-color: rgba(45, 44, 49, 0.5); backdrop-filter: blur-xl; box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);">
    <div class="flex items-center justify-around px-1 py-2" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">
        <a class="relative flex flex-col items-center justify-center gap-1 px-2 py-2 rounded-lg transition-colors min-w-0 flex-1 group {{ $currentRoute === 'account.orders' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.orders') }}">
            <i class="text-base fa-solid fa-cart-shopping {{ $currentRoute === 'account.orders' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
            <span class="text-[10px] font-medium leading-tight truncate w-full text-center">{{ __('messages.orders') }}</span>
            @if($currentRoute === 'account.orders')
                <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full top-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -top-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
            @endif
        </a>
        <a class="relative flex flex-col items-center justify-center gap-1 px-2 py-2 rounded-lg transition-colors min-w-0 flex-1 group {{ $currentRoute === 'account.chat' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.chat') }}">
            <i class="text-base fa-solid fa-message {{ $currentRoute === 'account.chat' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
            <span class="text-[10px] font-medium leading-tight truncate w-full text-center">{{ __('messages.chat') }}</span>
            @if($currentRoute === 'account.chat')
                <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full top-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -top-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
            @endif
        </a>
        <a class="relative flex flex-col items-center justify-center gap-1 px-2 py-2 rounded-lg transition-colors min-w-0 flex-1 group {{ $currentRoute === 'account.wallet' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.wallet') }}">
            <i class="text-base fa-solid fa-wallet {{ $currentRoute === 'account.wallet' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
            <span class="text-[10px] font-medium leading-tight truncate w-full text-center">{{ __('messages.wallet') }}</span>
            @if($currentRoute === 'account.wallet')
                <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full top-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -top-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
            @endif
        </a>
        <a class="relative flex flex-col items-center justify-center gap-1 px-2 py-2 rounded-lg transition-colors min-w-0 flex-1 group {{ $currentRoute === 'account.library' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.library') }}">
            <i class="text-base fa-solid fa-books {{ $currentRoute === 'account.library' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
            <span class="text-[10px] font-medium leading-tight truncate w-full text-center">{{ __('messages.library') }}</span>
            @if($currentRoute === 'account.library')
                <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full top-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -top-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
            @endif
        </a>
        @if($isSeller)
        <a class="relative flex flex-col items-center justify-center gap-1 px-2 py-2 rounded-lg transition-colors min-w-0 flex-1 group {{ $currentRoute === 'account.listed-accounts' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.listed-accounts') }}">
            <i class="text-base fa-solid fa-list {{ $currentRoute === 'account.listed-accounts' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
            <span class="text-[10px] font-medium leading-tight truncate w-full text-center">{{ __('messages.listed_accounts') }}</span>
            @if($currentRoute === 'account.listed-accounts')
                <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full top-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -top-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
            @endif
        </a>
        @endif
        <a class="relative flex flex-col items-center justify-center gap-1 px-2 py-2 rounded-lg transition-colors min-w-0 flex-1 group {{ $currentRoute === 'account.settings' ? 'text-white bg-gradient-to-t from-blue-500/10' : 'text-gray-400 hover:text-white hover:bg-gradient-to-t from-accent' }}" href="{{ route('account.settings') }}">
            <i class="text-base fa-solid fa-gear {{ $currentRoute === 'account.settings' ? 'text-white' : 'text-gray-400/50 group-hover:text-white' }}" aria-hidden="true"></i>
            <span class="text-[10px] font-medium leading-tight truncate w-full text-center">{{ __('messages.settings') }}</span>
            @if($currentRoute === 'account.settings')
                <div class="absolute h-[2px] -translate-x-1/2 rounded-t-full top-0 left-1/2 w-8" style="background-color: #3b82f6;"></div>
                <div class="absolute h-6 opacity-30 blur -translate-x-1/2 -top-4 left-1/2 w-16" style="background-color: #3b82f6;"></div>
            @endif
        </a>
    </div>
</div>

