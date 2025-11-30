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
                            <i class="fa-lg fa-solid fa-cog" aria-hidden="true"></i>
                        </div>
                        <div class="flex flex-col justify-center lg:flex-1">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl font-display text-white">My Settings</h1>
                            <p class="relative text-sm sm:block text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2">View and update your account settings.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Content -->
                <div class="mt-8" x-data="{ activeTab: 'general' }">
                    <!-- Tabs -->
                    <div class="mb-4">
                        <div class="flex overflow-x-auto -mb-8 pb-8" style="scrollbar-width: none; -ms-overflow-style: none;">
                            <div class="flex gap-1 min-w-full">
                                <a href="#" 
                                   @click.prevent="activeTab = 'general'"
                                   class="flex items-center flex-shrink-0 h-10 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                   :class="activeTab === 'general' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
                                   style="background-color: rgba(220, 38, 38, 0.1);"
                                   :style="activeTab === 'general' ? 'background-color: #dc2626;' : ''">
                                    <i class="mr-1.5 fa-solid fa-rectangle-vertical-history text-sm" :class="activeTab === 'general' ? 'opacity-100' : 'opacity-75'"></i> <span>General</span>
                                </a>
                                <a href="#" 
                                   @click.prevent="activeTab = 'notifications'"
                                   class="flex items-center flex-shrink-0 h-10 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                   :class="activeTab === 'notifications' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
                                   style="background-color: rgba(220, 38, 38, 0.1);"
                                   :style="activeTab === 'notifications' ? 'background-color: #dc2626;' : ''">
                                    <i class="mr-1.5 fa-solid fa-bell text-sm" :class="activeTab === 'notifications' ? 'opacity-100' : 'opacity-75'"></i> <span>Notifications</span>
                                </a>
                                <a href="#" 
                                   @click.prevent="activeTab = 'connected-accounts'"
                                   class="flex items-center flex-shrink-0 h-10 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                   :class="activeTab === 'connected-accounts' ? 'bg-red-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
                                   style="background-color: rgba(220, 38, 38, 0.1);"
                                   :style="activeTab === 'connected-accounts' ? 'background-color: #dc2626;' : ''">
                                    <i class="mr-1.5 fa-solid fa-link text-sm" :class="activeTab === 'connected-accounts' ? 'opacity-100' : 'opacity-75'"></i> <span>Connected Accounts</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Grid -->
                    <div class="grid items-start grid-cols-1 grid-rows-1 mx-auto gap-x-5 gap-y-5 lg:mx-0 lg:grid-cols-3">
                        <!-- Right Sidebar - Store Credit and Coins -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:col-start-3 lg:row-end-1 lg:grid-cols-1 order-2 lg:order-1">
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
                                        <i class="mr-2 fa-solid fa-receipt"></i> View History
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
                                        <i class="mr-2 fa-solid fa-receipt"></i> View History
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Main Content Area -->
                        <div class="space-y-4 lg:space-y-6 lg:col-span-2 lg:row-span-2 lg:row-end-2 order-1 lg:order-2">
                            <!-- General Tab Content -->
                            <div x-show="activeTab === 'general'">
                                <!-- User Information Section -->
                            <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                <div class="space-y-1.5 px-4 sm:px-6 border-b sm:rounded-t-xl flex flex-row items-center justify-between py-3" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                    <h3 class="font-semibold font-display leading-none text-white">User Information</h3>
                                    <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-md ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                        <i class="mr-1.5 fa-solid fa-pencil"></i> Edit Profile
                                    </button>
                                </div>
                                <div class="px-0 sm:px-6 pt-0">
                                    <div class="grid grid-cols-2 lg:grid-cols-3">
                                        <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                                            <dt class="text-sm font-medium capitalize text-white">Username</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">saminia laamari</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                                            <dt class="text-sm font-medium capitalize text-white">Handle</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">saminia-laamari</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                                            <dt class="text-sm font-medium capitalize text-white">User ID</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">#763938</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                                            <dt class="text-sm font-medium capitalize text-white">Email address</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">asminvfs12@gmail.com</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                                            <dt class="text-sm font-medium capitalize text-white">Languages</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">_</dd>
                                        </div>
                                        <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                                            <dt class="text-sm font-medium capitalize text-white">Games</dt>
                                            <dd class="mt-1 text-sm leading-6 text-gray-400 sm:col-span-2 sm:mt-0">_</dd>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Two-Factor Authentication Section -->
                            <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-6 border-b sm:rounded-t-xl" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                    <h3 class="font-semibold font-display leading-none text-white">Two-Factor Authentication</h3>
                                </div>
                                <div class="sm:px-6 flex items-center w-full px-4 py-6">
                                    <span class="text-sm text-gray-400">Add an extra layer of security to your account by enabling two-factor authentication.</span>
                                    <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-md ml-auto ring-1 shrink-0 bg-red-600/20 hover:bg-red-600/30 text-red-400 border-red-500/30">
                                        <i class="mr-2 fa-solid fa-shield-check"></i> Enable
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Delete Account Section -->
                            <div class="px-4 py-5 rounded-lg sm:p-6 shadow" style="background: linear-gradient(to bottom right, #0e1015 0%, #1b1a1e 50%, #0e1015 100%); border: 1px solid #ef4444;">
                                <div class="flex flex-col gap-6 sm:items-center sm:justify-between sm:flex-row">
                                    <div>
                                        <h3 class="text-base font-semibold leading-6 text-white">Delete Account</h3>
                                        <div class="max-w-xl mt-2 text-sm text-gray-400">This is irreversible. We will permanently remove your account, you will lose all the store credit and loyalty coins you have.</div>
                                    </div>
                                    <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-2 px-4 text-sm rounded-md">
                                        <i class="mr-2 fa-solid fa-trash-alt"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Login Sessions Section -->
                            <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                <div class="flex space-y-1.5 px-4 sm:px-6 border-b sm:rounded-t-xl flex-row items-center py-4" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                    <h3 class="font-semibold font-display leading-none text-white">Login Sessions</h3>
                                    <div class="flex items-center ml-auto gap-x-1.5">
                                        <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-1.5 px-2 text-xs rounded-md ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                            <i class="mr-2 fa-solid fa-eye"></i> Show IPs
                                        </button>
                                        <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600/20 hover:bg-red-600/30 text-red-400 border-red-500/30 py-1.5 px-2 text-xs rounded-md ring-1">
                                            <i class="mr-2 fa-solid fa-xmark"></i> Logout All Devices
                                        </button>
                                    </div>
                                </div>
                                <div class="px-0 sm:px-6 pt-0">
                                    <ul role="list" class="divide-y rounded-md" style="border-color: #2d2c31;">
                                        <li class="flex flex-wrap items-center justify-between px-4 py-4 leading-6 gap-x-4 sm:px-0 gap-y-2">
                                            <div class="flex items-center flex-1 min-w-[150px]">
                                                <div class="flex items-center justify-center p-3 overflow-hidden border rounded-full shadow-sm w-14 h-14" style="background-color: #1b1a1e; border-color: #2d2c31;">
                                                    <i class="text-xl text-white fa-solid fa-desktop" aria-hidden="true"></i>
                                                </div>
                                                <div class="flex flex-col flex-1 min-w-0 ml-4">
                                                    <span class="items-center font-medium truncate font-display sm:flex text-white">
                                                        Windows 
                                                        <span class="sm:px-1.5 px-0.5 text-gray-400">·</span> Chrome 
                                                        <span class="inline-flex items-center font-medium ring-1 ring-inset px-2 py-1 text-xs rounded-md ml-1 font-sans sm:ml-2 bg-red-600/20 text-red-400 border-red-500/30">
                                                            <svg class="-ml-0.5 mr-1 h-2 w-2 shrink-0" fill="currentColor" viewBox="0 0 8 8">
                                                                <circle cx="4" cy="4" r="3"></circle>
                                                            </svg>
                                                            <span class="flex-1 truncate shrink-0">Current</span>
                                                        </span>
                                                    </span>
                                                    <span class="sm:flex items-center truncate flex-shrink-0 text-sm text-gray-400 gap-x-1.5">
                                                        Algeria, Algiers 
                                                        <span class="text-gray-400">·</span> 2 seconds ago
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center flex-shrink-0 gap-x-3">
                                                <div class="block w-24 rounded-md h-9" style="background-color: rgba(27, 26, 30, 0.5);"></div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            </div>
                            
                            <!-- Notifications Tab Content -->
                            <div x-show="activeTab === 'notifications'">
                                <!-- Telegram Notifications Section -->
                                <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                    <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-6 border-b sm:rounded-t-xl" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                        <h3 class="font-semibold font-display leading-none text-white">Order Notifications</h3>
                                        <p class="text-sm text-gray-400 mt-1">Connect your Telegram account to receive real-time order notifications</p>
                                    </div>
                                    <div class="sm:px-6 px-4 py-6">
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                            <div class="flex items-center gap-4 flex-1">
                                                <div class="flex items-center justify-center p-4 rounded-lg" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;">
                                                    <i class="fa-brands fa-telegram text-3xl" style="color: #0088cc;"></i>
                                                </div>
                                                <div class="flex flex-col">
                                                    <h4 class="text-base font-semibold text-white">Telegram Notifications</h4>
                                                    <p class="text-sm text-gray-400 mt-1">Get instant notifications about your orders, payments, and account updates on Telegram</p>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-md" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31; color: #9ca3af;">
                                                            <i class="mr-1.5 fa-solid fa-check-circle text-green-500"></i> Order Status Updates
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-md" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31; color: #9ca3af;">
                                                            <i class="mr-1.5 fa-solid fa-check-circle text-green-500"></i> Payment Confirmations
                                                        </span>
                                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-md" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31; color: #9ca3af;">
                                                            <i class="mr-1.5 fa-solid fa-check-circle text-green-500"></i> Account Alerts
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <button type="button" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2.5 px-6 text-sm rounded-md ring-1 hover:bg-blue-600 text-white focus:outline-blue-600" style="background-color: #0088cc; border-color: #0088cc;">
                                                    <i class="mr-2 fa-brands fa-telegram"></i> Connect Telegram
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Connection Status (if connected) -->
                                        <div x-show="false" class="mt-6 p-4 rounded-lg" style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-500/20">
                                                    <i class="fa-solid fa-check text-green-500"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-white">Connected to Telegram</p>
                                                    <p class="text-xs text-gray-400 mt-0.5">Notifications are being sent to @your_telegram_username</p>
                                                </div>
                                                <button type="button" class="text-sm text-red-400 hover:text-red-300 transition-colors">
                                                    Disconnect
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Other Notification Settings -->
                                <div class="sm:rounded-xl sm:mx-0 -mx-4 border" style="background-color: #0e1015; border-color: #2d2c31;">
                                    <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-6 border-b sm:rounded-t-xl" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                        <h3 class="font-semibold font-display leading-none text-white">Email Notifications</h3>
                                    </div>
                                    <div class="sm:px-6 px-4 py-6 space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-white">Order Updates</p>
                                                <p class="text-xs text-gray-400 mt-0.5">Receive email notifications when your order status changes</p>
                                            </div>
                                            <label class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" style="background-color: #dc2626;" role="switch" aria-checked="true">
                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between border-t pt-4" style="border-color: #2d2c31;">
                                            <div>
                                                <p class="text-sm font-medium text-white">Payment Confirmations</p>
                                                <p class="text-xs text-gray-400 mt-0.5">Get notified when payments are confirmed</p>
                                            </div>
                                            <label class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" style="background-color: #dc2626;" role="switch" aria-checked="true">
                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"></span>
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-between border-t pt-4" style="border-color: #2d2c31;">
                                            <div>
                                                <p class="text-sm font-medium text-white">Marketing Emails</p>
                                                <p class="text-xs text-gray-400 mt-0.5">Receive updates about new features and promotions</p>
                                            </div>
                                            <label class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" style="background-color: rgba(45, 44, 49, 0.5);" role="switch" aria-checked="false">
                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Connected Accounts Tab Content -->
                            <div x-show="activeTab === 'connected-accounts'">
                                <div class="sm:rounded-xl sm:mx-0 -mx-4 border max-w-2xl" style="background-color: #0e1015; border-color: #2d2c31;">
                                    <div class="flex flex-col space-y-1.5 px-4 sm:px-6 py-6 border-b sm:rounded-t-xl" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.2);">
                                        <h3 class="font-semibold font-display leading-none text-white">Connected Accounts</h3>
                                    </div>
                                    <div class="px-0 sm:px-6 pt-0">
                                        <div class="grid grid-cols-1 lg:grid-cols-1">
                                            <!-- Discord -->
                                            <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t flex items-center justify-between w-full" style="border-color: rgba(45, 44, 49, 0.5);">
                                                <div class="flex items-center">
                                                    <div class="flex items-center justify-center px-2.5 py-2.5 w-12 rounded-md" style="background-color: rgb(88, 101, 242);">
                                                        <i class="fab text-base text-white fa-discord"></i>
                                                    </div>
                                                    <div class="ml-2.5 truncate">
                                                        <div class="text-sm font-medium truncate text-white">Discord</div>
                                                        <div class="text-xs truncate text-gray-400">Connect your Discord account</div>
                                                    </div>
                                                </div>
                                                <div class="flex gap-x-2 items-center">
                                                    <a href="#" class="justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-md flex items-center ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                                        <i class="fa-solid fa-link mr-2"></i> Connect
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <!-- Steam -->
                                            <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t flex items-center justify-between w-full" style="border-color: rgba(45, 44, 49, 0.5);">
                                                <div class="flex items-center">
                                                    <div class="flex items-center justify-center px-2.5 py-2.5 w-12 rounded-md" style="background-color: rgb(30, 40, 55);">
                                                        <i class="fab text-base text-white fa-steam"></i>
                                                    </div>
                                                    <div class="ml-2.5 truncate">
                                                        <div class="text-sm font-medium truncate text-white">Steam</div>
                                                        <div class="text-xs truncate text-gray-400">Connect your Steam account</div>
                                                    </div>
                                                </div>
                                                <div class="flex gap-x-2 items-center">
                                                    <button type="button" class="inline-flex items-center justify-center text-sm transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary w-10 h-10 sm:h-9 sm:w-9 rounded-md" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                                        <i class="fa-solid fa-cog"></i>
                                                    </button>
                                                    <a href="#" class="justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-md flex items-center ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                                        <i class="fa-solid fa-link mr-2"></i> Connect
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <!-- Google (Connected) -->
                                            <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t flex items-center justify-between w-full" style="border-color: rgba(45, 44, 49, 0.5);">
                                                <div class="flex items-center">
                                                    <div class="flex items-center justify-center px-2.5 py-2.5 w-12 rounded-md" style="background-color: rgb(234, 67, 53);">
                                                        <i class="fab text-base text-white fa-google"></i>
                                                    </div>
                                                    <div class="ml-2.5 truncate">
                                                        <div class="text-sm font-medium truncate text-white">
                                                            Google <i class="ml-0.5 fa-solid fa-check-circle text-blue-500"></i>
                                                        </div>
                                                        <div class="text-xs truncate text-gray-400">Connected</div>
                                                    </div>
                                                </div>
                                                <div class="flex gap-x-2 items-center">
                                                    <button type="button" class="justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-transparent hover:bg-red-600/20 text-red-400 focus:outline-red-600 py-2 px-4 text-sm rounded-md flex items-center">
                                                        <i class="fa-solid fa-link-slash mr-2"></i> Disconnect
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Twitch -->
                                            <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t flex items-center justify-between w-full" style="border-color: rgba(45, 44, 49, 0.5);">
                                                <div class="flex items-center">
                                                    <div class="flex items-center justify-center px-2.5 py-2.5 w-12 rounded-md" style="background-color: rgb(145, 70, 255);">
                                                        <i class="fab text-base text-white fa-twitch"></i>
                                                    </div>
                                                    <div class="ml-2.5 truncate">
                                                        <div class="text-sm font-medium truncate text-white">Twitch</div>
                                                        <div class="text-xs truncate text-gray-400">Connect your Twitch account</div>
                                                    </div>
                                                </div>
                                                <div class="flex gap-x-2 items-center">
                                                    <a href="#" class="justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-md flex items-center ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                                        <i class="fa-solid fa-link mr-2"></i> Connect
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <!-- Facebook -->
                                            <div class="px-4 py-6 sm:col-span-1 sm:px-0 border-t flex items-center justify-between w-full" style="border-color: rgba(45, 44, 49, 0.5);">
                                                <div class="flex items-center">
                                                    <div class="flex items-center justify-center px-2.5 py-2.5 w-12 rounded-md" style="background-color: rgb(24, 119, 242);">
                                                        <i class="fab text-base text-white fa-facebook"></i>
                                                    </div>
                                                    <div class="ml-2.5 truncate">
                                                        <div class="text-sm font-medium truncate text-white">Facebook</div>
                                                        <div class="text-xs truncate text-gray-400">Connect your Facebook account</div>
                                                    </div>
                                                </div>
                                                <div class="flex gap-x-2 items-center">
                                                    <a href="#" class="justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2 px-4 text-sm rounded-md flex items-center ring-1 hover:bg-gray-800/50 text-white focus:outline-secondary" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31;">
                                                        <i class="fa-solid fa-link mr-2"></i> Connect
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
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
