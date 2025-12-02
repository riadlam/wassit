@extends('layouts.app')

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('https://wassit.diaszone.com/storage/home_page/degaultbanner.webp'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen pt-16 sm:pt-16 pb-20 md:pb-8">
        <!-- Dashboard Navigation -->
        @include('components.dashboard-nav')
        
        <!-- Main Content -->
        <div class="relative z-10 px-4 sm:px-6 lg:px-8" style="padding-top: 122px;">
            <div class="mx-auto max-w-[1550px]">
                <!-- Header -->
                <div class="flex flex-wrap gap-4 justify-between items-center w-full lg:shrink-0 mb-8">
                    <div class="flex gap-x-3 items-center">
                        <div class="hidden justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm md:flex shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                            <i class="fa-lg fa-solid fa-shopping-cart" aria-hidden="true"></i>
                        </div>
                        <div class="flex flex-col justify-center lg:flex-1">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl font-display text-white">{{ __('messages.my_orders') }}</h1>
                            <p class="relative text-sm sm:block text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2">{{ __('messages.list_of_all_products_services') }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Content -->
                <div class="mt-8">
                    <div class="space-y-4">
                        <!-- Search and Filters -->
                        <div class="flex items-center justify-between">
                            <div class="flex flex-wrap items-center flex-1 gap-2">
                                <!-- Search -->
                                <div class="relative">
                                    <input 
                                        placeholder="{{ __('messages.search') }}" 
                                        type="text"
                                        class="h-8 w-[150px] lg:w-[250px] block rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none pr-9 pl-3 py-1.5 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    >
                                    <div class="absolute flex items-center justify-center rounded pointer-events-none right-1.5 top-1/2 -translate-y-1/2 w-5 h-5" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;">
                                        <span class="text-xs font-medium" style="color: #9ca3af;">/</span>
                                    </div>
                                </div>
                                
                                <!-- Type Filter -->
                                <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                    <i class="mr-2 fa-regular fa-plus"></i> {{ __('messages.type') }}
                                </button>
                                
                                <!-- Status Filter -->
                                <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                    <i class="mr-2 fa-regular fa-plus"></i> {{ __('messages.status') }}
                                </button>
                                
                                <!-- Purchased At Filter -->
                                <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                    <i class="mr-2 fa-regular fa-plus"></i> {{ __('messages.purchased_at') }}
                                </button>
                                
                                <!-- View Filter (Hidden on mobile) -->
                                <button class="items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs hidden h-8 ml-auto lg:flex" style="border-color: #2d2c31; color: #9ca3af;">
                                    <i class="mr-2 fa-regular fa-sliders-simple"></i> {{ __('messages.view') }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Table -->
                        <div class="-mx-4 sm:-mx-6 lg:rounded-md lg:-mx-0 border overflow-hidden" style="background-color: #0e1015; border-color: #2d2c31;">
                            <div class="w-full overflow-auto">
                                <table class="w-full caption-bottom text-sm">
                                    <thead class="overflow-clip">
                                        <tr class="border-b transition-colors" style="border-color: #2d2c31; color: #9ca3af;">
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">{{ __('messages.order_id') }}</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">{{ __('messages.account') }}</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">{{ $isSeller ? __('messages.buyer') : __('messages.seller') }}</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">{{ __('messages.status') }}</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">{{ __('messages.delivery') }}</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="flex items-center space-x-2 justify-end">
                                                    <span class="uppercase tracking-wide text-xs">{{ __('messages.amount') }}</span>
                                                </div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="flex items-center space-x-2 justify-end">
                                                    <span class="uppercase tracking-wide text-xs">{{ __('messages.date') }}</span>
                                                </div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-end">{{ __('messages.actions') }}</div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody style="background-color: rgba(27, 26, 30, 0.3);">
                                        @forelse($orders as $order)
                                        <tr class="border-b transition-colors hover:bg-gray-800/50" style="border-color: #2d2c31;">
                                            <td class="px-2.5 py-3 align-middle first:pl-4 last:pr-4">
                                                <span class="text-sm text-gray-400 font-mono">#{{ $order->id }}</span>
                                            </td>
                                            <td class="px-2.5 py-3 align-middle first:pl-4 last:pr-4">
                                                <div class="flex items-center gap-2">
                                                    @if($order->account && $order->account->game)
                                                    <div class="flex-shrink-0 w-8 h-8 rounded" style="background: url('{{ asset('storage/' . $order->account->game->image) }}') center/cover;"></div>
                                                    @endif
                                                    <div class="text-sm text-white">
                                                        <p class="font-medium">{{ $order->account->title ?? 'Account #' . $order->account_id }}</p>
                                                        <p class="text-xs text-gray-500">{{ $order->account->game->name ?? 'Unknown Game' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-2.5 py-3 align-middle first:pl-4 last:pr-4">
                                                <span class="text-sm text-gray-300">
                                                    @if($isSeller)
                                                        {{ $order->buyer->name ?? 'Unknown' }}
                                                    @else
                                                        {{ $order->seller && $order->seller->user ? $order->seller->user->name : 'Unknown' }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-2.5 py-3 align-middle first:pl-4 last:pr-4">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                                    style="background-color: {{ $order->status === 'completed' ? 'rgba(34, 197, 94, 0.15)' : ($order->status === 'pending' ? 'rgba(234, 179, 8, 0.15)' : 'rgba(239, 68, 68, 0.15)') }};
                                                           color: {{ $order->status === 'completed' ? '#86efac' : ($order->status === 'pending' ? '#fde047' : '#fca5a5') }};
                                                           border: 1px solid {{ $order->status === 'completed' ? 'rgba(34, 197, 94, 0.3)' : ($order->status === 'pending' ? 'rgba(234, 179, 8, 0.3)' : 'rgba(239, 68, 68, 0.3)') }};">
                                                    <i class="fa-solid {{ $order->status === 'completed' ? 'fa-circle-check' : ($order->status === 'pending' ? 'fa-clock' : 'fa-circle-xmark') }} mr-1"></i>
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-2.5 py-3 align-middle first:pl-4 last:pr-4">
                                                @if($order->delivery_status)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                                    style="background-color: {{ $order->delivery_status === 'delivered' ? 'rgba(34, 197, 94, 0.15)' : 'rgba(234, 179, 8, 0.15)' }};
                                                           color: {{ $order->delivery_status === 'delivered' ? '#86efac' : '#fde047' }};
                                                           border: 1px solid {{ $order->delivery_status === 'delivered' ? 'rgba(34, 197, 94, 0.3)' : 'rgba(234, 179, 8, 0.3)' }};">
                                                    {{ ucfirst($order->delivery_status) }}
                                                </span>
                                                @else
                                                <span class="text-xs text-gray-500">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-2.5 py-3 align-middle text-right first:pl-4 last:pr-4">
                                                <span class="text-sm text-white font-semibold">{{ number_format($order->amount_dzd, 2, ',', ' ') }} DZD</span>
                                            </td>
                                            <td class="px-2.5 py-3 align-middle text-right first:pl-4 last:pr-4">
                                                <span class="text-sm text-gray-400">{{ $order->created_at->format('d M Y') }}</span>
                                                <span class="block text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</span>
                                            </td>
                                            <td class="px-2.5 py-3 align-middle text-right first:pl-4 last:pr-4">
                                                <a href="{{ route('account.chat') }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium rounded-md transition-colors bg-blue-600 hover:bg-blue-700 text-white">
                                                    <i class="fa-solid fa-message mr-1.5"></i> Chat
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr class="border-b transition-colors hover:bg-gray-800/50" style="border-color: #2d2c31;">
                                            <td class="px-2.5 py-2.5 align-middle h-32 text-center first:pl-4 last:pr-4" colspan="8" style="color: #9ca3af;">
                                                {{ __('messages.no_orders_yet') }}
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="flex items-center justify-center px-2 sm:justify-end">
                            <div class="flex items-center justify-between flex-1 gap-x-6 sm:flex-auto lg:gap-x-8">
                                <!-- Rows per page -->
                                <div class="items-center hidden space-x-2 sm:flex">
                                    <p class="text-sm font-medium" style="color: #9ca3af;">{{ __('messages.rows_per_page') }}</p>
                                    <button class="h-8 w-[70px] flex items-center justify-between rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-red-500 disabled:cursor-not-allowed disabled:opacity-50" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                                        <span style="pointer-events: none;">15</span>
                                        <i aria-hidden="true" class="w-4 h-4 opacity-50 fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                
                                <!-- Page info -->
                                <div class="items-center justify-center hidden text-sm font-medium sm:flex" style="color: #9ca3af;">{{ $orders->total() }} rows – Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</div>
                                
                                <!-- Pagination buttons -->
                                <nav class="w-full sm:w-auto">
                                    <div class="flex items-center w-full gap-1">
                                        <a href="{{ $orders->url(1) }}" class="items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white hidden sm:flex w-10 h-10 p-0 {{ $orders->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}" style="border-color: #2d2c31; color: #9ca3af;" aria-label="{{ __('messages.first_page') }}">
                                            <i class="fa-solid fa-angles-left"></i>
                                        </a>
                                        <a href="{{ $orders->previousPageUrl() }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white flex-grow sm:flex-grow-0 w-10 h-10 p-0 {{ $orders->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}" style="border-color: #2d2c31; color: #9ca3af;" aria-label="{{ __('messages.previous_page') }}">
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </a>
                                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 bg-red-600 text-white shadow hover:bg-red-700 flex-grow h-10 p-0 sm:w-10 sm:flex-grow-0" aria-current="page">{{ $orders->currentPage() }}</button>
                                        <a href="{{ $orders->nextPageUrl() }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white flex-grow sm:flex-grow-0 w-10 h-10 p-0 {{ !$orders->hasMorePages() ? 'pointer-events-none opacity-50' : '' }}" style="border-color: #2d2c31; color: #9ca3af;" aria-label="{{ __('messages.next_page') }}">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </a>
                                        <a href="{{ $orders->url($orders->lastPage()) }}" class="items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white hidden sm:flex w-10 h-10 p-0 {{ !$orders->hasMorePages() ? 'pointer-events-none opacity-50' : '' }}" style="border-color: #2d2c31; color: #9ca3af;" aria-label="{{ __('messages.last_page') }}">
                                            <i class="fa-solid fa-angles-right"></i>
                                        </a>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
