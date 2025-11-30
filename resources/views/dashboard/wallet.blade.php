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
                <div class="mt-8">
                    <div class="grid items-start grid-cols-1 grid-rows-1 mx-auto gap-x-5 gap-y-5 lg:mx-0 lg:grid-cols-3 xl:grid-cols-7">
                        <!-- Left Column - Store Credit and Coins Cards -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:row-end-1 lg:grid-cols-1 xl:col-span-2">
                            <!-- Store Credit Card -->
                            <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                <div class="sm:px-6 px-4 py-6">
                                    <dt class="text-sm font-medium leading-6" style="color: #9ca3af;">Store Credit</dt>
                                    <dd class="flex-none w-full mt-2">
                                        <span class="pr-0.5 text-3xl font-semibold" style="color: #9ca3af;">€</span>
                                        <span class="text-3xl font-semibold leading-10 tracking-tight text-white">0,00</span>
                                        <span class="pl-1 text-sm font-medium" style="color: #9ca3af;">EUR</span>
                                    </dd>
                                </div>
                                <div class="flex items-center px-4 sm:px-6 py-3 border-t sm:rounded-b-xl" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                    <a href="#" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-transparent hover:bg-gray-800/50 text-white focus:outline-secondary py-1.5 px-2 text-xs rounded-md">
                                        <i class="mr-2 fa-duotone fa-receipt"></i> View History
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Coins Card -->
                            <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                <div class="sm:px-6 px-4 py-6">
                                    <dt class="text-sm font-medium leading-6" style="color: #9ca3af;">Coins</dt>
                                    <dd class="flex items-center w-full mt-2">
                                        <img src="https://cdn.gameboost.com/static/coins/coin-md.webp" alt="Coins" class="mr-1 h-7">
                                        <div class="flex items-baseline gap-x-1">
                                            <span class="text-3xl font-semibold leading-10 tracking-tight text-white">0</span>
                                            <span class="text-sm font-medium" style="color: #9ca3af;"> ≈$0.00</span>
                                        </div>
                                    </dd>
                                </div>
                                <div class="flex items-center px-4 sm:px-6 py-3 border-t sm:rounded-b-xl" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                    <a href="#" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-transparent hover:bg-gray-800/50 text-white focus:outline-secondary py-1.5 px-2 text-xs rounded-md">
                                        <i class="mr-2 fa-duotone fa-receipt"></i> View History
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Transactions Section -->
                        <div class="space-y-4 lg:space-y-6 lg:col-span-2 lg:row-span-2 lg:row-end-2 xl:col-span-5" x-data="{ activeTab: 'transactions' }">
                            <!-- Tabs -->
                            <div class="flex overflow-x-auto -mb-8 pb-8" style="scrollbar-width: none; -ms-overflow-style: none;">
                                <div class="flex gap-1 min-w-full">
                                    <a href="#" 
                                       @click.prevent="activeTab = 'transactions'"
                                       class="flex items-center flex-shrink-0 h-10 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                       :class="activeTab === 'transactions' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
                                       style="background-color: rgba(220, 38, 38, 0.1);"
                                       :style="activeTab === 'transactions' ? 'background-color: #dc2626;' : ''">
                                        <i class="mr-1.5 fa-solid fa-credit-card text-sm" :class="activeTab === 'transactions' ? 'opacity-100' : 'opacity-75'"></i> <span>Transactions</span>
                                    </a>
                                    <a href="#" 
                                       @click.prevent="activeTab = 'store-credit'"
                                       class="flex items-center flex-shrink-0 h-10 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                       :class="activeTab === 'store-credit' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
                                       style="background-color: rgba(220, 38, 38, 0.1);"
                                       :style="activeTab === 'store-credit' ? 'background-color: #dc2626;' : ''">
                                        <i class="mr-1.5 fa-solid fa-dollar-sign text-sm" :class="activeTab === 'store-credit' ? 'opacity-100' : 'opacity-75'"></i> <span>Store Credit History</span>
                                    </a>
                                    <a href="#" 
                                       @click.prevent="activeTab = 'coins'"
                                       class="flex items-center flex-shrink-0 h-10 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                       :class="activeTab === 'coins' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
                                       style="background-color: rgba(220, 38, 38, 0.1);"
                                       :style="activeTab === 'coins' ? 'background-color: #dc2626;' : ''">
                                        <i class="mr-1.5 fa-solid fa-coins text-sm" :class="activeTab === 'coins' ? 'opacity-100' : 'opacity-75'"></i> <span>Coins History</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Transactions Content -->
                            <div class="space-y-4">
                                <!-- Search and Filters -->
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex flex-wrap items-center flex-1 gap-2">
                                        <!-- Search -->
                                        <div class="relative">
                                            <input 
                                                placeholder="Search..." 
                                                type="text"
                                                class="h-8 w-[150px] lg:w-[250px] block rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none pr-9 pl-3 py-1.5 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            >
                                            <div class="absolute flex items-center justify-center rounded pointer-events-none right-1.5 top-1/2 -translate-y-1/2 w-5 h-5" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;">
                                                <span class="text-xs font-medium" style="color: #9ca3af;">/</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Status Filter -->
                                        <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                            <i class="mr-2 fa-regular fa-plus"></i> Status
                                        </button>
                                        
                                        <!-- Type Filter -->
                                        <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                            <i class="mr-2 fa-regular fa-plus"></i> Type
                                        </button>
                                        
                                        <!-- View Filter (Hidden on mobile) -->
                                        <button class="items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs hidden h-8 ml-auto lg:flex" style="border-color: #2d2c31; color: #9ca3af;">
                                            <i class="mr-2 fa-regular fa-sliders-simple"></i> View
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
                                                        <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">Payment Method</div>
                                                    </th>
                                                    <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                        <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">Order</div>
                                                    </th>
                                                    <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                        <div class="flex items-center space-x-2 justify-start">
                                                            <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-800/50 hover:text-white rounded-md px-3 -ml-3 h-8 group uppercase tracking-wide text-xs flex-shrink-0 gap-x-2">
                                                                <span class="truncate">Status</span>
                                                                <i class="opacity-0 fa-regular group-hover:opacity-100 fa-arrow-up-arrow-down"></i>
                                                            </button>
                                                        </div>
                                                    </th>
                                                    <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                        <div class="flex items-center space-x-2 justify-end">
                                                            <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-800/50 hover:text-white rounded-md px-3 -ml-3 h-8 group uppercase tracking-wide text-xs flex-shrink-0 gap-x-2 flex-row-reverse">
                                                                <span class="truncate">Transaction Id</span>
                                                                <i class="opacity-0 fa-regular group-hover:opacity-100 fa-arrow-up-arrow-down"></i>
                                                            </button>
                                                        </div>
                                                    </th>
                                                    <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                        <div class="flex items-center space-x-2 justify-end">
                                                            <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-800/50 hover:text-white rounded-md px-3 -ml-3 h-8 group uppercase tracking-wide text-xs flex-shrink-0 gap-x-2 flex-row-reverse">
                                                                <span class="truncate">Order Id</span>
                                                                <i class="opacity-0 fa-regular group-hover:opacity-100 fa-arrow-up-arrow-down"></i>
                                                            </button>
                                                        </div>
                                                    </th>
                                                    <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                        <div class="flex items-center space-x-2 justify-end">
                                                            <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-800/50 hover:text-white rounded-md px-3 -ml-3 h-8 group uppercase tracking-wide text-xs flex-shrink-0 gap-x-2 flex-row-reverse">
                                                                <span class="truncate">Amount</span>
                                                                <i class="opacity-0 fa-regular group-hover:opacity-100 fa-arrow-up-arrow-down"></i>
                                                            </button>
                                                        </div>
                                                    </th>
                                                    <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                        <div class="flex items-center space-x-2 justify-end">
                                                            <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-800/50 hover:text-white rounded-md px-3 -ml-3 h-8 group uppercase tracking-wide text-xs flex-shrink-0 gap-x-2 flex-row-reverse">
                                                                <span class="truncate">Last Updated</span>
                                                                <i class="opacity-0 fa-regular group-hover:opacity-100 fa-arrow-up-arrow-down"></i>
                                                            </button>
                                                        </div>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody style="background-color: rgba(27, 26, 30, 0.3);">
                                                <tr class="border-b transition-colors hover:bg-gray-800/50" style="border-color: #2d2c31;">
                                                    <td class="px-2.5 py-2.5 align-middle h-32 text-center first:pl-4 last:pr-4" colspan="8" style="color: #9ca3af;">
                                                        No results.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="flex items-center justify-center px-2 sm:justify-end">
                                    <div class="flex items-center justify-between flex-1 gap-x-6 sm:flex-auto lg:gap-x-8">
                                        <!-- Rows per page -->
                                        <div class="items-center hidden space-x-2 sm:flex">
                                            <p class="text-sm font-medium" style="color: #9ca3af;">Rows per page</p>
                                            <button class="h-8 w-[70px] flex items-center justify-between rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-red-500 disabled:cursor-not-allowed disabled:opacity-50" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                                                <span style="pointer-events: none;">15</span>
                                                <i aria-hidden="true" class="w-4 h-4 opacity-50 fa-solid fa-chevron-down"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Page info -->
                                        <div class="items-center justify-center hidden text-sm font-medium sm:flex" style="color: #9ca3af;">0 rows – Page 1 to 1</div>
                                        
                                        <!-- Pagination buttons -->
                                        <nav class="w-full sm:w-auto">
                                            <div class="flex items-center w-full gap-1">
                                                <button disabled class="items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white hidden sm:flex w-10 h-10 p-0" style="border-color: #2d2c31; color: #9ca3af;" aria-label="First Page" type="button">
                                                    <i class="fa-solid fa-angles-left"></i>
                                                </button>
                                                <button disabled class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white flex-grow sm:flex-grow-0 w-10 h-10 p-0" style="border-color: #2d2c31; color: #9ca3af;" aria-label="Previous Page" type="button">
                                                    <i class="fa-solid fa-chevron-left"></i>
                                                </button>
                                                <button class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 bg-red-600 text-white shadow hover:bg-red-700 flex-grow h-10 p-0 sm:w-10 sm:flex-grow-0" data-type="page" aria-label="Page 1" aria-current="page" data-selected="true" type="button" value="1">1</button>
                                                <button disabled class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white flex-grow sm:flex-grow-0 w-10 h-10 p-0" style="border-color: #2d2c31; color: #9ca3af;" aria-label="Next Page" type="button">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </button>
                                                <button disabled class="items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white hidden sm:flex w-10 h-10 p-0" style="border-color: #2d2c31; color: #9ca3af;" aria-label="Last Page" type="button">
                                                    <i class="fa-solid fa-angles-right"></i>
                                                </button>
                                            </div>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
