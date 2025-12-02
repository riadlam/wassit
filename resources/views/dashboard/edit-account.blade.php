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
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="flex flex-wrap gap-4 justify-between items-center w-full lg:shrink-0 mb-8">
                    <div class="flex gap-x-3 items-center">
                        <div class="hidden justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm md:flex shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                            <i class="fa-lg fa-solid fa-pencil" aria-hidden="true"></i>
                        </div>
                        <div class="flex flex-col justify-center lg:flex-1">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl font-display text-white">Edit Account</h1>
                            <p class="relative text-sm sm:block text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2">Update your account listing details</p>
                        </div>
                    </div>
                    <a href="{{ route('account.listed-accounts') }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2.5 px-4 text-sm rounded-md ring-1 ring-secondary-ring text-gray-300 hover:text-white hover:bg-gray-800/50" style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;">
                        <i class="mr-2 fa-solid fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
                
                <!-- Edit Form -->
                <div class="rounded-xl overflow-hidden" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;">
                    <div class="p-6 sm:p-8 lg:p-10">
                        <form method="POST" action="{{ route('account.listed-accounts.update', $account->id) }}" enctype="multipart/form-data" id="editAccountForm">
                            @csrf
                            @method('PUT')
                            @if($errors->any())
                                <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20">
                                    <div class="flex items-start">
                                        <i class="fa-solid fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                                        <div>
                                            <p class="text-red-400 font-medium mb-2">Please fix the following errors:</p>
                                            <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20">
                                    <div class="flex items-start">
                                        <i class="fa-solid fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                                        <div>
                                            <p class="text-red-400 font-medium">{{ session('error') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(session('success'))
                                <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                                    <i class="fa-solid fa-check-circle mr-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif
                            <!-- Basic Information Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <i class="fa-solid fa-info-circle mr-3 text-red-600"></i>
                                    Basic Information
                                </h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Title -->
                                    <div class="md:col-span-2">
                                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                            Account Title <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="title"
                                            name="title"
                                            value="{{ $account->title }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="Epic Rank Account - 150+ Skins"
                                        >
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="md:col-span-2">
                                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            rows="4"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="Describe the account features"
                                        >{{ $account->description }}</textarea>
                                    </div>
                                    <!-- Price (DZD) -->
                                    <div>
                                        <label for="price_dzd" class="block text-sm font-medium text-gray-300 mb-2">
                                            Price (DZD)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">DZD</span>
                                            <input 
                                                type="number" 
                                                id="price_dzd" 
                                                name="price_dzd" 
                                                step="0.01"
                                                value="{{ $account->price_dzd ?? 0 }}"
                                                class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 pl-12 pr-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                placeholder="16228"
                                            >
                                        </div>
                                    </div>
                                    
                                    <!-- Status -->
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-300 mb-2">
                                            Status
                                        </label>
                                        <select 
                                            id="status" 
                                            name="status" 
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        >
                                            <option value="available" {{ ($account->status ?? 'available') === 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="disabled" {{ ($account->status ?? '') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="skins_count" class="block text-sm font-medium text-gray-300 mb-2">
                                            Skins Count
                                        </label>
                                        <input 
                                            type="number" 
                                            id="skins_count" 
                                            name="attributes[skins_count]" 
                                            value="{{ $attributesMap['skins_count'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="150"
                                        >
                                    </div>
                                    
                                    <!-- Diamonds -->
                                    <div>
                                        <label for="diamonds" class="block text-sm font-medium text-gray-300 mb-2">
                                            Diamonds
                                        </label>
                                        <input 
                                            type="number" 
                                            id="diamonds" 
                                            name="attributes[diamonds]" 
                                            value="{{ $attributesMap['diamonds'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="5000"
                                        >
                                    </div>
                                    
                                    <!-- Battle Points (BP) -->
                                    <div>
                                        <label for="bp" class="block text-sm font-medium text-gray-300 mb-2">
                                            Battle Points (BP)
                                        </label>
                                        <input 
                                            type="number" 
                                            id="bp" 
                                            name="attributes[bp]" 
                                            value="{{ $attributesMap['bp'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="25000"
                                        >
                                    </div>
                                    
                                    <!-- Level -->
                                    <div>
                                        <label for="level" class="block text-sm font-medium text-gray-300 mb-2">
                                            Account Level
                                        </label>
                                        <input 
                                            type="number" 
                                            id="level" 
                                            name="attributes[level]" 
                                            value="{{ $attributesMap['level'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="30"
                                        >
                                    </div>
                                    
                                    <!-- Win Rate -->
                                    <div>
                                        <label for="win_rate" class="block text-sm font-medium text-gray-300 mb-2">
                                            Win Rate (%)
                                        </label>
                                        <input 
                                            type="number" 
                                            id="win_rate" 
                                            name="attributes[win_rate]" 
                                            step="0.01"
                                            value="{{ $attributesMap['win_rate'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="65"
                                        >
                                    </div>
                                    
                                    <!-- Collection Tier -->
                                    <div>
                                        <label for="collection_tier" class="block text-sm font-medium text-gray-300 mb-2">
                                            {{ __('messages.collection_tier') }}
                                        </label>
                                        <select 
                                            id="collection_tier" 
                                            name="attributes[collection_tier]" 
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        >
                                            <option value="">-- Select Collection Tier --</option>
                                            <option value="Expert Collector" {{ ($attributesMap['collection_tier'] ?? '') === 'Expert Collector' ? 'selected' : '' }}>Expert Collector</option>
                                            <option value="Renowned Collector" {{ ($attributesMap['collection_tier'] ?? '') === 'Renowned Collector' ? 'selected' : '' }}>Renowned Collector</option>
                                            <option value="Exalted Collector" {{ ($attributesMap['collection_tier'] ?? '') === 'Exalted Collector' ? 'selected' : '' }}>Exalted Collector</option>
                                            <option value="Mega Collector" {{ ($attributesMap['collection_tier'] ?? '') === 'Mega Collector' ? 'selected' : '' }}>Mega Collector</option>
                                            <option value="World Collector" {{ ($attributesMap['collection_tier'] ?? '') === 'World Collector' ? 'selected' : '' }}>World Collector</option>
                                        </select>
                                    </div>
                                    <!-- Ensure status includes all valid options per validation -->
                                    <div class="md:col-span-2">
                                        <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                                        <select 
                                            id="status" 
                                            name="status" 
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        >
                                            @php $st = $account->status ?? 'available'; @endphp
                                            <option value="available" {{ $st === 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="disabled" {{ $st === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                            <option value="pending" {{ $st === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="sold" {{ $st === 'sold' ? 'selected' : '' }}>Sold</option>
                                            <option value="cancelled" {{ $st === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Highlighted Skins Section -->
                            <div class="mb-8" x-data="highlightedSkinsEdit({{ json_encode($attributesMap['highlighted_skins'] ?? '') }})" x-init="init()">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-star mr-3 text-red-600"></i>
                                        Highlighted Skins
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm text-gray-400" x-show="getSelectedCount() > 0">
                                            <span x-text="getSelectedCount()"></span> selected
                                        </span>
                                        <button 
                                            type="button"
                                            @click="clearAllSkins()"
                                            x-show="getSelectedCount() > 0"
                                            class="text-xs text-gray-400 hover:text-red-500 transition-colors"
                                        >
                                            Clear All
                                        </button>
                                    </div>
                                </h2>
                                
                                <!-- Search Bar -->
                                <div class="mb-4">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input 
                                            type="text" 
                                            x-model="searchQuery"
                                            placeholder="Search by hero name, skin name, or role..."
                                            class="w-full pl-10 pr-4 py-2.5 rounded-md text-sm text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        >
                                    </div>
                                </div>
                                
                                <!-- Loading State -->
                                <div x-show="loading" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Loading skins data...</p>
                                </div>
                                
                                <!-- Skins Selection -->
                                <div x-show="!loading" class="space-y-3 max-h-[600px] overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #2d2c31 #1b1a1e;">
                                    <template x-for="(category, roleIndex) in filteredCategories()" :key="roleIndex">
                                        <div class="border rounded-lg overflow-hidden" style="border-color: #2d2c31; background-color: #1b1a1e;">
                                            <!-- Role Header -->
                                            <button 
                                                type="button"
                                                @click="toggleRole(roleIndex)"
                                                class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-white hover:bg-opacity-50 transition-colors"
                                                style="background-color: rgba(27, 26, 30, 0.7);"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="expandedRoles[roleIndex] ? 'rotate-180' : ''"></i>
                                                    <span x-text="category.name"></span>
                                                </div>
                                                <span class="text-xs text-gray-400" x-text="category.heroes.length + ' heroes'"></span>
                                            </button>
                                            
                                            <!-- Heroes List -->
                                            <div 
                                                x-show="expandedRoles[roleIndex]"
                                                x-collapse
                                                class="px-4 pb-3 space-y-2"
                                            >
                                                <template x-for="(hero, heroIndex) in category.heroes" :key="heroIndex">
                                                    <div class="border rounded-md mt-2" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.3);">
                                                        <!-- Hero Header -->
                                                        <button 
                                                            type="button"
                                                            @click="toggleHero(roleIndex, heroIndex)"
                                                            class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-300 hover:text-white transition-colors"
                                                        >
                                                            <span x-text="hero.hero" class="capitalize"></span>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs text-gray-500" x-text="hero.skins.length + ' skins'"></span>
                                                                <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="expandedHeroes[`${roleIndex}-${heroIndex}`] ? 'rotate-180' : ''"></i>
                                                            </div>
                                                        </button>
                                                        
                                                        <!-- Skins List -->
                                                        <div 
                                                            x-show="expandedHeroes[`${roleIndex}-${heroIndex}`]"
                                                            x-collapse
                                                            class="px-3 pb-2 flex flex-wrap gap-2"
                                                        >
                                                            <template x-for="(skinObj, skinIndex) in (hero.skins_with_ids || [])" :key="skinIndex">
                                                                <button
                                                                    type="button"
                                                                    @click="toggleSkinById(skinObj.id)"
                                                                    class="px-3 py-1.5 text-xs rounded-md transition-all border"
                                                                    :class="isSkinSelectedById(skinObj.id) ? 'bg-red-600 border-red-600 text-white' : 'bg-transparent border-gray-600 text-gray-300 hover:border-red-500 hover:text-white'"
                                                                >
                                                                    <i class="fa-solid mr-1.5" :class="isSkinSelectedById(skinObj.id) ? 'fa-check-circle' : 'fa-circle'"></i>
                                                                    <span x-text="skinObj.name"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <!-- Empty State -->
                                    <div x-show="!loading && filteredCategories().length === 0" class="text-center py-12">
                                        <i class="fa-solid fa-search text-4xl text-gray-600 mb-3"></i>
                                        <p class="text-gray-400">No skins found matching your search</p>
                                    </div>
                                </div>
                                
                                <!-- Selected Skins Preview -->
                                <div x-show="getSelectedCount() > 0" class="mt-4 p-4 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <h3 class="text-sm font-semibold text-white mb-2">Selected Skins (<span x-text="getSelectedCount()"></span>)</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(skin, index) in getSelectedSkinsList()" :key="index">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                                <span x-text="`${skin.hero} - ${skin.name}`"></span>
                                                <button 
                                                    type="button"
                                                    @click="toggleSkinById(skin.id)"
                                                    class="hover:text-red-400 transition-colors"
                                                >
                                                    <i class="fa-solid fa-times text-xs"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Hidden Input for Form Submission -->
                                <input 
                                    type="hidden" 
                                    id="highlighted_skins_input" 
                                    name="attributes[highlighted_skins]"
                                    value="{{ $attributesMap['highlighted_skins'] ?? '' }}"
                                >
                            </div>
                            
                            <!-- Images Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <i class="fa-solid fa-images mr-3 text-red-600"></i>
                                    Account Images
                                </h2>
                                
                                <div class="space-y-4">
                                    <!-- Upload Images -->
                                    <div>
                                        <label for="images" class="block text-sm font-medium text-gray-300 mb-2">
                                            Account Images <span class="text-gray-500">(Max 10 images total)</span>
                                        </label>
                                        <label 
                                            for="images"
                                            id="uploadArea"
                                            class="flex items-center justify-center w-full border-2 border-dashed rounded-lg p-6 cursor-pointer hover:border-red-600 transition-colors" 
                                            style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.3);"
                                        >
                                            <div class="text-center pointer-events-none">
                                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-500 mb-2"></i>
                                                <p class="text-sm text-gray-400 mb-1">
                                                    <span class="text-red-600">Click to upload</span> or drag and drop
                                                </p>
                                                <p class="text-xs text-gray-500">PNG, JPG, WEBP up to 10MB each</p>
                                            </div>
                                        </label>
                                        <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                                    </div>
                                    
                                    <!-- All Images Grid (Current + New) -->
                                    <div id="allImagesContainer">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="imagesGrid">
                                            @if($account->images && $account->images->count() > 0)
                                                @foreach($account->images as $image)
                                                    <div class="relative group" data-image-id="{{ $image->id }}" data-image-type="existing">
                                                        <div class="aspect-video rounded-lg overflow-hidden" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                                                            <img 
                                                                src="{{ asset('storage/' . $image->url) }}" 
                                                                alt="Account image {{ $loop->iteration }}"
                                                                class="w-full h-full object-cover"
                                                                onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-500\'><i class=\'fa-solid fa-image text-2xl\'></i></div>';"
                                                            >
                                                        </div>
                                                        <button 
                                                            type="button" 
                                                            class="absolute top-2 right-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                                            onclick="removeImage({{ $image->id }}, this, 'existing')"
                                                        >
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                        <input type="hidden" name="keep_images[]" value="{{ $image->id }}" id="keep_image_{{ $image->id }}">
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-end gap-4 pt-6 border-t" style="border-color: #2d2c31;">
                                <a href="{{ route('account.listed-accounts') }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 px-6 text-sm rounded-md ring-1 ring-secondary-ring text-gray-300 hover:text-white hover:bg-gray-800/50" style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;">
                                    Cancel
                                </a>
                                <button 
                                    type="submit"
                                    id="submitBtn"
                                    class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 sm:py-2.5 px-8 text-sm rounded-md"
                                >
                                    <span id="submitText">Save Changes</span>
                                    <span id="submitLoading" style="display: none;">
                                        <i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editAccountForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    // Ensure highlighted skins hidden input is up-to-date (desktop + mobile)
                    try {
                        const hiddenInput = document.getElementById('highlighted_skins_input');
                        if (hiddenInput && Array.isArray(window.highlightedSelectedSkinIds)) {
                            hiddenInput.value = window.highlightedSelectedSkinIds.join(',');
                        }
                    } catch (_) {}
                    
                    // Validate at least one image remains (either existing kept or new uploads)
                    const imagesGrid = document.getElementById('imagesGrid');
                    const fileInput = document.getElementById('images');
                    
                    // Count existing images that will be kept
                    const keptExistingImages = imagesGrid.querySelectorAll('[data-image-type="existing"]').length;
                    
                    // Count new images
                    const newImagesCount = fileInput && fileInput.files ? fileInput.files.length : 0;
                    
                    // Total images after update
                    const totalImages = keptExistingImages + newImagesCount;
                    
                    if (totalImages < 1) {
                        e.preventDefault();
                        alert('At least one image is required. You cannot delete all images. Please keep at least one existing image or upload a new one.');
                        return false;
                    }
                    
                    
                    submitBtn.disabled = true;
                    if (submitText) submitText.style.display = 'none';
                    if (submitLoading) submitLoading.style.display = 'inline-flex';
                });
            }
            
            // Handle image upload
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('images');
            const imagesGrid = document.getElementById('imagesGrid');
            const currentImageCount = {{ $account->images ? $account->images->count() : 0 }};
            const maxImages = 10;
            
            // Track which existing images to keep/delete (make it global)
            window.deletedImageIds = [];
            
            if (uploadArea && fileInput) {
                // Label handles click automatically via 'for' attribute, no need for click handler
                // Only handle drag and drop
                
                // Drag and drop
                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.borderColor = '#ef4444';
                });
                
                uploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.borderColor = '#2d2c31';
                });
                
                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.style.borderColor = '#2d2c31';
                    
                    const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
                    if (files.length > 0) {
                        handleFileSelection(files);
                    }
                });
                
                // File input change - add new images to the grid
                fileInput.addEventListener('change', function(e) {
                    const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));
                    
                    // Count existing images (not marked for deletion)
                    const existingCount = imagesGrid.querySelectorAll('[data-image-type="existing"]').length;
                    const newCount = imagesGrid.querySelectorAll('[data-image-type="new"]').length;
                    const totalCurrent = existingCount + newCount;
                    
                    // Check total image count
                    if (totalCurrent + files.length > maxImages) {
                        alert(`Maximum ${maxImages} images allowed. You currently have ${totalCurrent} images. You can only add ${maxImages - totalCurrent} more.`);
                        fileInput.value = '';
                        return;
                    }
                    
                    // Add new images to the grid
                    if (files.length > 0 && imagesGrid) {
                        files.forEach((file) => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const div = document.createElement('div');
                                div.className = 'relative group';
                                div.setAttribute('data-image-type', 'new');
                                div.setAttribute('data-file-name', file.name);
                                div.innerHTML = `
                                    <div class="aspect-video rounded-lg overflow-hidden" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                                        <img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover">
                                    </div>
                                    <button type="button" onclick="removeImage(null, this, 'new')" class="absolute top-2 right-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                `;
                                imagesGrid.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        });
                    }
                });
            }
            
            function handleFileSelection(files) {
                // This function is only for drag and drop
                // Count existing images
                const existingCount = imagesGrid.querySelectorAll('[data-image-type="existing"]').length;
                const newCount = imagesGrid.querySelectorAll('[data-image-type="new"]').length;
                const totalCurrent = existingCount + newCount;
                
                // Check total image count
                if (totalCurrent + files.length > maxImages) {
                    alert(`Maximum ${maxImages} images allowed. You currently have ${totalCurrent} images. You can only add ${maxImages - totalCurrent} more.`);
                    return;
                }
                
                // Get existing files from input
                const existingFiles = fileInput.files ? Array.from(fileInput.files) : [];
                
                // Combine existing and new files using DataTransfer
                const dt = new DataTransfer();
                existingFiles.forEach(file => dt.items.add(file));
                files.forEach(file => dt.items.add(file));
                
                // Update file input
                fileInput.files = dt.files;
                
                // Add new images to grid
                files.forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative group';
                        div.setAttribute('data-image-type', 'new');
                        div.setAttribute('data-file-name', file.name);
                        div.innerHTML = `
                            <div class="aspect-video rounded-lg overflow-hidden" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                                <img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover">
                            </div>
                            <button type="button" onclick="removeImage(null, this, 'new')" class="absolute top-2 right-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        `;
                        imagesGrid.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
        
        // Remove image (existing or new)
        function removeImage(imageId, button, type) {
            const imageContainer = button.closest('.relative');
            if (!imageContainer) return;
            
            if (type === 'existing') {
                // Remove keep_images input - this marks the image for deletion
                // The controller will delete all images NOT in keep_images[]
                const keepInput = document.getElementById('keep_image_' + imageId);
                if (keepInput) {
                    keepInput.remove();
                }
            } else if (type === 'new') {
                // Remove new image from grid and file input
                const fileName = imageContainer.getAttribute('data-file-name');
                const fileInput = document.getElementById('images');
                
                if (fileInput) {
                    // Remove from file input using DataTransfer
                    const dt = new DataTransfer();
                    const currentFiles = Array.from(fileInput.files);
                    
                    currentFiles.forEach(file => {
                        if (file.name !== fileName) {
                            dt.items.add(file);
                        }
                    });
                    
                    fileInput.files = dt.files;
                }
            }
            
            // Remove from DOM with animation
            imageContainer.style.transition = 'opacity 0.3s';
            imageContainer.style.opacity = '0';
            setTimeout(() => {
                imageContainer.remove();
            }, 300);
        }
        
        // Make function globally accessible
        window.removeImage = removeImage;
        
        // Highlighted Skins Component for Edit (ID-based)
        function highlightedSkinsEdit(existingSkins) {
            return {
                categories: [],
                loading: false,
                searchQuery: '',
                expandedRoles: {},
                expandedHeroes: {},
                selectedSkinIds: [],
                initialValue: existingSkins || '',
                async init() {
                    await this.loadSkinsData();
                    this.prefillSelections();
                    // Expose selection for form submission safety
                    window.highlightedSelectedSkinIds = this.selectedSkinIds.slice();
                },
                async loadSkinsData() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/mlbb/skins');
                        if (!response.ok) throw new Error('Failed to load skins data: ' + response.status);
                        const data = await response.json();
                        let categories = (data.categories || []).sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }));
                        categories = categories.map(category => {
                            const sortedHeroes = (category.heroes || []).sort((a, b) => (a.hero || '').trim().toLowerCase().localeCompare((b.hero || '').trim().toLowerCase(), undefined, { sensitivity: 'base' }));
                            const heroesWithSortedSkins = sortedHeroes.map(hero => {
                                const withIds = (hero.skins_with_ids || []).slice().sort((x, y) => (x.name || '').toLowerCase().localeCompare((y.name || '').toLowerCase(), undefined, { sensitivity: 'base' }));
                                const namesOnly = withIds.map(s => s.name);
                                return { ...hero, skins_with_ids: withIds, skins: namesOnly };
                            });
                            return { ...category, heroes: heroesWithSortedSkins };
                        });
                        this.categories = categories;
                    } catch (error) {
                        this.categories = [];
                    } finally {
                        this.loading = false;
                    }
                },
                prefillSelections() {
                    if (!this.initialValue) return;
                    let raw = String(this.initialValue).trim();
                    // Handle JSON array format like [1,2,3]
                    if (/^\[.*\]$/.test(raw)) {
                        try {
                            const arr = JSON.parse(raw);
                            if (Array.isArray(arr)) {
                                this.selectedSkinIds = arr.map(v => Number(v)).filter(v => !isNaN(v));
                                this.updateHiddenInputs();
                                return;
                            }
                        } catch (_) {}
                    }
                    // Normalize comma-separated string of IDs, tolerate spaces
                    const trimmed = raw.replace(/\s+/g, '');
                    const isIds = /^[0-9]+(,[0-9]+)*$/.test(trimmed);
                    if (isIds) {
                        this.selectedSkinIds = trimmed.split(',').map(v => Number(v.trim())).filter(v => !isNaN(v));
                    } else {
                        let parts = [];
                        if (trimmed.includes('|')) parts = trimmed.split('|');
                        else if (trimmed.includes(',')) parts = trimmed.split(',');
                        else parts = [trimmed];
                        parts.map(p => p.trim()).filter(p => p).forEach(entry => {
                            const heroSkin = entry.split('-');
                            if (heroSkin.length >= 2) {
                                const hero = heroSkin[0].trim().toLowerCase();
                                const skin = heroSkin.slice(1).join('-').trim().toLowerCase();
                                const found = this.findSkinId(hero, skin);
                                if (found && !this.selectedSkinIds.includes(found)) this.selectedSkinIds.push(found);
                            }
                        });
                    }
                    this.updateHiddenInputs();
                },
                findSkinId(heroLower, skinLower) {
                    for (const category of this.categories) {
                        for (const hero of category.heroes) {
                            if (hero.hero.trim().toLowerCase() === heroLower) {
                                for (const s of (hero.skins_with_ids || [])) {
                                    if ((s.name || '').trim().toLowerCase() === skinLower) return Number(s.id);
                                }
                            }
                        }
                    }
                    return null;
                },
                toggleRole(roleIndex) { this.expandedRoles[roleIndex] = !this.expandedRoles[roleIndex]; },
                toggleHero(roleIndex, heroIndex) { const key = `${roleIndex}-${heroIndex}`; this.expandedHeroes[key] = !this.expandedHeroes[key]; },
                toggleSkinById(id) {
                    id = Number(id);
                    const idx = this.selectedSkinIds.indexOf(id);
                    if (idx > -1) this.selectedSkinIds.splice(idx, 1); else this.selectedSkinIds.push(id);
                    this.updateHiddenInputs();
                },
                isSkinSelectedById(id) { return this.selectedSkinIds.includes(Number(id)); },
                getSelectedCount() { return this.selectedSkinIds.length; },
                findSkinById(id) {
                    id = Number(id);
                    for (const category of this.categories) {
                        for (const hero of category.heroes) {
                            for (const s of (hero.skins_with_ids || [])) {
                                if (Number(s.id) === id) return { id: Number(s.id), hero: hero.hero, name: s.name };
                            }
                        }
                    }
                    return null;
                },
                getSelectedSkinsList() {
                    const out = [];
                    for (const id of this.selectedSkinIds) {
                        const info = this.findSkinById(id);
                        if (info) out.push(info);
                    }
                    return out;
                },
                updateHiddenInputs() {
                    const hiddenInput = document.getElementById('highlighted_skins_input');
                    if (hiddenInput) hiddenInput.value = this.selectedSkinIds.join(',');
                    // Mirror to global for mobile submit reliability
                    window.highlightedSelectedSkinIds = this.selectedSkinIds.slice();
                },
                clearAllSkins() {
                    this.selectedSkinIds = [];
                    this.expandedRoles = {};
                    this.expandedHeroes = {};
                    this.updateHiddenInputs();
                },
                filteredCategories() {
                    if (!this.searchQuery) return this.categories;
                    const query = this.searchQuery.toLowerCase();
                    return this.categories.map(category => {
                        const filteredHeroes = category.heroes.map(hero => {
                            const matchingSkins = (hero.skins_with_ids || []).filter(s => 
                                (s.name || '').toLowerCase().includes(query) || 
                                (hero.hero || '').toLowerCase().includes(query) ||
                                (category.name || '').toLowerCase().includes(query)
                            );
                            if (matchingSkins.length > 0) {
                                return { ...hero, skins_with_ids: matchingSkins, skins: matchingSkins.map(s => s.name) };
                            }
                            return null;
                        }).filter(h => h);
                        if (filteredHeroes.length > 0) {
                            return { ...category, heroes: filteredHeroes };
                        }
                        return null;
                    }).filter(c => c);
                }
            };
        }
    </script>
    @endpush
@endsection


