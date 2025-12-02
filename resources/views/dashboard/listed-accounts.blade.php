@extends('layouts.app')

@section('content')
    <!-- Success Notification Toast -->
    @if(session('success'))
    <div 
        x-data="{ show: true }"
        x-show="show"
        x-cloak
        x-init="setTimeout(() => show = false, 5000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed top-20 right-4 z-[9999] max-w-sm w-full"
        style="z-index: 9999;"
    >
        <div class="rounded-lg p-4 shadow-lg" style="background-color: rgba(14, 16, 21, 0.95); border: 1px solid #22c55e; backdrop-blur-md;">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full" style="background-color: rgba(34, 197, 94, 0.1);">
                        <i class="fa-solid fa-check-circle text-xl" style="color: #22c55e;"></i>
                    </div>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium text-white">
                        {{ session('success') }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="show = false" class="inline-flex rounded-md text-gray-400 hover:text-gray-300 focus:outline-none transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                <!-- Header -->
                <div class="flex flex-wrap gap-4 justify-between items-center w-full lg:shrink-0 mb-8">
                    <div class="flex gap-x-3 items-center">
                        <div class="hidden justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm md:flex shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                            <i class="fa-lg fa-solid fa-list" aria-hidden="true"></i>
                        </div>
                        <div class="flex flex-col justify-center lg:flex-1">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl font-display text-white">Listed Accounts</h1>
                            <p class="relative text-sm sm:block text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2">Manage your listed gaming accounts</p>
                        </div>
                    </div>
                    <!-- List New Account Button -->
                    <div class="flex items-center">
                        <a href="{{ route('account.listed-accounts.create') }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-2.5 px-4 text-sm rounded-md">
                            <i class="mr-2 fa-solid fa-plus"></i>
                            List New Account
                        </a>
                    </div>
                </div>
                
                <!-- Accounts Table -->
                <div class="mt-8">
                    <div class="space-y-4">
                        <!-- Search and Filters -->
                        <div class="flex items-center justify-between">
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
                                
                                <!-- Game Filter -->
                                <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                    <i class="mr-2 fa-regular fa-plus"></i> Game
                                </button>
                                
                                <!-- Created At Filter -->
                                <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 border bg-transparent shadow-sm hover:bg-gray-800/50 hover:text-white rounded-md px-3 text-xs h-8 border-dashed" style="border-color: #2d2c31; color: #9ca3af;">
                                    <i class="mr-2 fa-regular fa-plus"></i> Created At
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
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">ID</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">Title</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">Game</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">Status</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="flex items-center space-x-2 justify-end">
                                                    <button class="inline-flex items-center justify-center font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:pointer-events-none disabled:opacity-50 hover:bg-gray-800/50 hover:text-white rounded-md px-3 -ml-3 h-8 group uppercase tracking-wide text-xs flex-shrink-0 gap-x-2 flex-row-reverse">
                                                        <span class="truncate">Price</span>
                                                        <i class="opacity-0 fa-regular group-hover:opacity-100 fa-arrow-up-arrow-down"></i>
                                                    </button>
                                                </div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-start">Created</div>
                                            </th>
                                            <th class="h-10 px-2.5 text-left align-middle font-medium first:pl-4 first:rounded-tl-md last:rounded-tr-md" style="background-color: rgba(27, 26, 30, 0.5);">
                                                <div class="uppercase tracking-wide text-xs flex items-center text-nowrap justify-end">Actions</div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($accounts as $account)
                                            <tr class="border-b transition-colors hover:bg-gray-800/30" style="border-color: #2d2c31;">
                                                <td class="p-2.5 align-middle first:pl-4">
                                                    <div class="text-white text-sm">#{{ $account->id }}</div>
                                                </td>
                                                <td class="p-2.5 align-middle">
                                                    <div class="text-white text-sm max-w-xs truncate" title="{{ $account->title }}">
                                                        {{ $account->title }}
                                                    </div>
                                                </td>
                                                <td class="p-2.5 align-middle">
                                                    <div class="text-gray-400 text-sm">{{ $account->game->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="p-2.5 align-middle">
                                                    @if($account->status === 'available')
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" style="background-color: rgba(34, 197, 94, 0.1); color: #22c55e;">
                                                            Available
                                                        </span>
                                                    @elseif($account->status === 'disabled')
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444;">
                                                            Disabled
                                                        </span>
                                                    @elseif($account->status === 'sold')
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" style="background-color: rgba(156, 163, 175, 0.1); color: #9ca3af;">
                                                            Sold
                                                        </span>
                                                    @elseif($account->status === 'pending')
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" style="background-color: rgba(234, 179, 8, 0.1); color: #eab308;">
                                                            Pending
                                                        </span>
                                                    @elseif($account->status === 'cancelled')
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" style="background-color: rgba(156, 163, 175, 0.1); color: #9ca3af;">
                                                            Cancelled
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="p-2.5 align-middle">
                                                    <div class="text-white text-sm text-right">{{ number_format($account->price_dzd / 100, 0, '.', '') }} DZD</div>
                                                </td>
                                                <td class="p-2.5 align-middle">
                                                    <div class="text-gray-400 text-sm">{{ $account->created_at->diffForHumans() }}</div>
                                                </td>
                                                <td class="p-2.5 align-middle">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <!-- Edit Button -->
                                                        <a href="{{ route('account.listed-accounts.edit', $account->id) }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md hover:bg-blue-600/20 text-blue-400 hover:text-blue-300" style="border: 1px solid rgba(59, 130, 246, 0.3);" title="Edit">
                                                            <i class="fa-solid fa-pencil"></i>
                                                        </a>
                                                        
                                                        <!-- Disable/Enable Button -->
                                                        @if($account->status === 'available')
                                                            <button 
                                                                onclick="updateAccountStatus({{ $account->id }}, 'disabled', this)"
                                                                class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md hover:bg-yellow-600/20 text-yellow-400 hover:text-yellow-300" 
                                                                style="border: 1px solid rgba(234, 179, 8, 0.3);" 
                                                                title="Disable"
                                                            >
                                                                <i class="fa-solid fa-eye-slash"></i>
                                                            </button>
                                                        @elseif($account->status === 'disabled')
                                                            <button 
                                                                onclick="updateAccountStatus({{ $account->id }}, 'available', this)"
                                                                class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md hover:bg-green-600/20 text-green-400 hover:text-green-300" 
                                                                style="border: 1px solid rgba(34, 197, 94, 0.3);" 
                                                                title="Enable"
                                                            >
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <!-- Delete Button -->
                                                        <button 
                                                            onclick="deleteAccount({{ $account->id }}, this)"
                                                            class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md hover:bg-red-600/20 text-red-400 hover:text-red-300" 
                                                            style="border: 1px solid rgba(239, 68, 68, 0.3);" 
                                                            title="Delete"
                                                        >
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="p-12 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <i class="fa-solid fa-inbox text-4xl text-gray-500 mb-4"></i>
                                                        <p class="text-gray-400 text-lg">No accounts listed yet</p>
                                                        <p class="text-gray-500 text-sm mt-2">Start by listing your first account</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                                <!-- Pagination -->
                                @if($accounts->hasPages())
                                <div class="flex items-center justify-between px-2 mt-4">
                                    <div class="text-sm text-gray-400">
                                        Showing <span class="text-white font-medium">{{ $accounts->firstItem() }}</span> to <span class="text-white font-medium">{{ $accounts->lastItem() }}</span> of <span class="text-white font-medium">{{ $accounts->total() }}</span> accounts
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($accounts->onFirstPage())
                                            <button class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-3 text-xs rounded-md text-gray-400 hover:text-white hover:bg-gray-800/50 disabled:opacity-30" style="border: 1px solid #2d2c31;" disabled>
                                                <i class="fa-solid fa-angles-left mr-1"></i>
                                                Previous
                                            </button>
                                        @else
                                            <a href="{{ $accounts->previousPageUrl() }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-3 text-xs rounded-md text-gray-400 hover:text-white hover:bg-gray-800/50" style="border: 1px solid #2d2c31;">
                                                <i class="fa-solid fa-angles-left mr-1"></i>
                                                Previous
                                            </a>
                                        @endif
                                        
                                        @if($accounts->hasMorePages())
                                            <a href="{{ $accounts->nextPageUrl() }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-3 text-xs rounded-md text-gray-400 hover:text-white hover:bg-gray-800/50" style="border: 1px solid #2d2c31;">
                                                Next
                                                <i class="fa-solid fa-angles-right ml-1"></i>
                                            </a>
                                        @else
                                            <button class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-3 text-xs rounded-md text-gray-400 hover:text-white hover:bg-gray-800/50 disabled:opacity-30" style="border: 1px solid #2d2c31;" disabled>
                                                Next
                                                <i class="fa-solid fa-angles-right ml-1"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-20 right-4 z-[10000] space-y-2" style="z-index: 10000;"></div>
    
    <!-- Confirmation Modal -->
    <div 
        id="confirm-modal" 
        x-data="{ 
            show: false, 
            title: '', 
            message: '', 
            onConfirm: null,
            type: 'danger',
            open(title, message, callback, modalType = 'danger') {
                this.title = title;
                this.message = message;
                this.onConfirm = callback;
                this.type = modalType;
                this.show = true;
            },
            close() {
                this.show = false;
                this.onConfirm = null;
            },
            confirm() {
                if (this.onConfirm) {
                    this.onConfirm();
                }
                this.close();
            }
        }"
        x-show="show"
        x-cloak
        @keydown.escape.window="close()"
        class="fixed inset-0 z-[10001] overflow-y-auto"
        style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                class="relative w-full max-w-md rounded-xl overflow-hidden shadow-xl"
                style="background-color: #0e1015; border: 1px solid #2d2c31;"
                @click.stop
            >
                <div class="p-6">
                    <div class="flex items-start mb-4">
                        <div 
                            class="flex items-center justify-center w-12 h-12 rounded-full mr-4 flex-shrink-0"
                            :style="type === 'danger' ? 'background-color: rgba(239, 68, 68, 0.1);' : 'background-color: rgba(234, 179, 8, 0.1);'"
                        >
                            <i 
                                class="text-2xl"
                                :class="type === 'danger' ? 'fa-solid fa-exclamation-triangle text-red-500' : 'fa-solid fa-question-circle text-yellow-500'"
                            ></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-1" x-text="title"></h3>
                            <p class="text-gray-400 text-sm" x-text="message"></p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            @click="close()" 
                            class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2.5 px-4 text-sm rounded-md ring-1 ring-secondary-ring text-gray-300 hover:text-white hover:bg-gray-800/50" 
                            style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="confirm()" 
                            class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2.5 px-4 text-sm rounded-md text-white shadow-sm focus:outline-red-600"
                            :style="type === 'danger' ? 'background-color: #ef4444;' : 'background-color: #eab308;'"
                            :class="type === 'danger' ? 'hover:bg-red-700' : 'hover:bg-yellow-600'"
                        >
                            <span x-text="type === 'danger' ? 'Delete' : 'Confirm'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Toast notification system
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            
            const toastId = 'toast-' + Date.now();
            
            const colors = {
                success: { bg: 'rgba(34, 197, 94, 0.1)', border: '#22c55e', icon: 'fa-check-circle', iconColor: '#22c55e' },
                error: { bg: 'rgba(239, 68, 68, 0.1)', border: '#ef4444', icon: 'fa-exclamation-circle', iconColor: '#ef4444' },
                warning: { bg: 'rgba(234, 179, 8, 0.1)', border: '#eab308', icon: 'fa-exclamation-triangle', iconColor: '#eab308' },
                info: { bg: 'rgba(59, 130, 246, 0.1)', border: '#3b82f6', icon: 'fa-info-circle', iconColor: '#3b82f6' }
            };
            
            const color = colors[type] || colors.success;
            
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = 'max-w-sm w-full';
            toast.innerHTML = `
                <div 
                    x-data="{ show: true }"
                    x-show="show"
                    x-cloak
                    x-init="setTimeout(() => { show = false; setTimeout(() => { const el = document.getElementById('${toastId}'); if (el) el.remove(); }, 300); }, 5000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2 translate-x-2"
                    x-transition:enter-end="opacity-100 translate-y-0 translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 translate-x-0"
                    x-transition:leave-end="opacity-0 translate-y-2 translate-x-2"
                    class="rounded-lg p-4 shadow-lg backdrop-blur-md"
                    style="background-color: rgba(14, 16, 21, 0.95); border: 1px solid ${color.border};"
                >
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full" style="background-color: ${color.bg};">
                                <i class="fa-solid ${color.icon} text-xl" style="color: ${color.iconColor};"></i>
                            </div>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <p class="text-sm font-medium text-white">${message}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false; setTimeout(() => { const el = document.getElementById('${toastId}'); if (el) el.remove(); }, 300)" class="inline-flex rounded-md text-gray-400 hover:text-gray-300 focus:outline-none transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Initialize Alpine.js on the new element
            if (window.Alpine) {
                Alpine.initTree(toast);
            }
        }
        
        // Confirmation modal helper
        function showConfirmModal(title, message, onConfirm, type = 'danger') {
            const modal = document.getElementById('confirm-modal');
            if (!modal || !window.Alpine) {
                // Fallback to browser confirm if Alpine not ready
                if (confirm(message)) {
                    onConfirm();
                }
                return;
            }
            
            // Get Alpine data
            const alpineData = modal._x_dataStack?.[0] || modal.__x?.$data;
            if (alpineData) {
                alpineData.open(title, message, onConfirm, type);
            } else {
                // Fallback
                if (confirm(message)) {
                    onConfirm();
                }
            }
        }
        
        // Update account status
        async function updateAccountStatus(accountId, newStatus, button) {
            const action = newStatus === 'disabled' ? 'disable' : 'enable';
            
            showConfirmModal(
                `${action.charAt(0).toUpperCase() + action.slice(1)} Account`,
                `Are you sure you want to ${action} this account?`,
                async () => {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                    
                    try {
                        const response = await fetch(`{{ url('/account/listed-accounts') }}/${accountId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast(`Account ${action}d successfully`, 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showToast(result.message || 'Failed to update account status', 'error');
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        showToast('An error occurred. Please try again.', 'error');
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    }
                },
                'warning'
            );
        }
        
        // Delete account
        async function deleteAccount(accountId, button) {
            showConfirmModal(
                'Delete Account',
                'Are you sure you want to delete this account? This action cannot be undone and will delete all associated images.',
                async () => {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                    
                    try {
                        const response = await fetch(`{{ url('/account/listed-accounts') }}/${accountId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            }
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast('Account deleted successfully', 'success');
                            
                            // Remove the row from the table
                            const row = button.closest('tr');
                            if (row) {
                                row.style.transition = 'opacity 0.3s';
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    // Reload if no accounts left
                                    const remainingRows = document.querySelectorAll('tbody tr').length;
                                    if (remainingRows === 0) {
                                        setTimeout(() => window.location.reload(), 500);
                                    }
                                }, 300);
                            } else {
                                setTimeout(() => window.location.reload(), 500);
                            }
                        } else {
                            showToast(result.message || 'Failed to delete account', 'error');
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        }
                    } catch (error) {
                        console.error('Error deleting account:', error);
                        showToast('An error occurred. Please try again.', 'error');
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    }
                },
                'danger'
            );
        }
    </script>
    @endpush
@endsection
