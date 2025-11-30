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
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="flex flex-wrap gap-4 justify-between items-center w-full lg:shrink-0 mb-8">
                    <div class="flex gap-x-3 items-center">
                        <div class="hidden justify-center items-center p-3 w-16 h-16 rounded-full border shadow-sm md:flex shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                            <i class="fa-lg fa-solid fa-plus" aria-hidden="true"></i>
                        </div>
                        <div class="flex flex-col justify-center lg:flex-1">
                            <h1 class="gap-4 max-w-4xl text-lg font-semibold tracking-tight sm:text-2xl font-display text-white">List New Account</h1>
                            <p class="relative text-sm sm:block text-gray-400 sm:max-w-md lg:max-w-3xl line-clamp-2">Create a new account listing</p>
                        </div>
                    </div>
                    <a href="{{ route('account.listed-accounts') }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2.5 px-4 text-sm rounded-md ring-1 ring-secondary-ring text-gray-300 hover:text-white hover:bg-gray-800/50" style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;">
                        <i class="mr-2 fa-solid fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
                
                <!-- Create Form -->
                <div class="rounded-xl overflow-hidden" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;" x-data="{ 
                    selectedGameId: '{{ old('game_id', '') }}',
                    mlbbId: {{ $mlbbId ?? 'null' }},
                    get isMLBB() {
                        if (!this.selectedGameId || !this.mlbbId) return false;
                        return String(this.selectedGameId) === String(this.mlbbId);
                    },
                    get isOtherGame() {
                        if (!this.selectedGameId || !this.mlbbId) return false;
                        return String(this.selectedGameId) !== String(this.mlbbId);
                    }
                }">
                    <div class="p-6 sm:p-8 lg:p-10">
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
                        
                        <form method="POST" action="{{ route('account.listed-accounts.store') }}" enctype="multipart/form-data" id="createAccountForm">
                            @csrf
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const form = document.getElementById('createAccountForm');
                                    if (form) {
                                        form.addEventListener('submit', function(e) {
                                            const gameSelect = document.getElementById('game_id');
                                            const selectedGameId = gameSelect ? gameSelect.value : '';
                                            const mlbbId = {{ $mlbbId ?? 'null' }};
                                            
                                            if (!selectedGameId || String(selectedGameId) !== String(mlbbId)) {
                                                e.preventDefault();
                                                alert('Please select Mobile Legends to create an account.');
                                                return false;
                                            }
                                            
                                            // Validate at least one image is selected
                                            const fileInput = document.getElementById('images');
                                            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                                                e.preventDefault();
                                                alert('At least one image is required. Please upload at least one image before submitting.');
                                                return false;
                                            }
                                            
                                            console.log('Form submitting...', {
                                                game_id: selectedGameId,
                                                mlbbId: mlbbId,
                                                formData: new FormData(form)
                                            });
                                        });
                                    }
                                });
                            </script>
                            
                            <!-- Game Selection -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <i class="fa-solid fa-gamepad mr-3 text-red-600"></i>
                                    Select Game
                                </h2>
                                
                                <div>
                                    <label for="game_id" class="block text-sm font-medium text-gray-300 mb-2">
                                        Game <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="game_id" 
                                        name="game_id" 
                                        required
                                        x-model="selectedGameId"
                                        class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    >
                                        <option value="">-- Select Game --</option>
                                        @foreach($games as $game)
                                            <option value="{{ $game->id }}" {{ old('game_id') == $game->id ? 'selected' : '' }}>{{ $game->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Coming Soon Message -->
                            <div x-show="isOtherGame" x-cloak x-transition class="mb-8">
                                <div class="rounded-lg p-8 text-center" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fa-solid fa-clock text-5xl text-gray-500 mb-4"></i>
                                        <h3 class="text-2xl font-semibold text-white mb-2">Coming Soon</h3>
                                        <p class="text-gray-400 text-lg">Stay tuned! We're working on adding support for this game.</p>
                                        <p class="text-gray-500 text-sm mt-2">For now, you can only list Mobile Legends accounts.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Fields (Only shown for MLBB) -->
                            <div x-show="isMLBB" x-cloak x-transition>
                                <!-- Basic Information Section -->
                                <div class="mb-8">
                                    <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                        <i class="fa-solid fa-info-circle mr-3 text-red-600"></i>
                                        Basic Information
                                    </h2>
                                    
                                    <div class="space-y-6">
                                        <!-- Title -->
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                                            Account Title <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="text" 
                                            id="title" 
                                            name="title" 
                                            required
                                            value="{{ old('title') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="Epic Rank Account - 150+ Skins"
                                        >
                                    </div>
                                    
                                    <!-- Description -->
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                                            Description <span class="text-red-500">*</span>
                                        </label>
                                        <textarea 
                                            id="description" 
                                            name="description" 
                                            rows="5"
                                            required
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all resize-none" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="Describe your account in detail..."
                                        >{{ old('description') }}</textarea>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Price -->
                                        <div>
                                            <label for="price_dzd" class="block text-sm font-medium text-gray-300 mb-2">
                                                Price (DZD) <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">DZD</span>
                                                <input 
                                                    type="number" 
                                                    id="price_dzd" 
                                                    name="price_dzd" 
                                                    step="0.01"
                                                    required
                                                    value="{{ old('price_dzd') }}"
                                                    class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 pl-12 pr-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                    placeholder="16228"
                                                >
                                            </div>
                                        </div>
                                        
                                        <!-- Status -->
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">
                                                Status <span class="text-red-500">*</span>
                                            </label>
                                            <select 
                                                id="status" 
                                                name="status" 
                                                required
                                                class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            >
                                                <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="disabled" {{ old('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Account Attributes Section -->
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <i class="fa-solid fa-list-check mr-3 text-red-600"></i>
                                    Account Attributes
                                </h2>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Rank -->
                                    <div>
                                        <label for="rank" class="block text-sm font-medium text-gray-300 mb-2">Rank</label>
                                        <input 
                                            type="text" 
                                            id="rank" 
                                            name="attributes[rank]" 
                                            value="{{ old('attributes.rank') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="Epic IV"
                                        >
                                    </div>
                                    
                                    <!-- Heroes Count -->
                                    <div>
                                        <label for="heroes_count" class="block text-sm font-medium text-gray-300 mb-2">Heroes Count</label>
                                        <input 
                                            type="number" 
                                            id="heroes_count" 
                                            name="attributes[heroes_count]" 
                                            value="{{ old('attributes.heroes_count') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="85"
                                        >
                                    </div>
                                    
                                    <!-- Skins Count -->
                                    <div>
                                        <label for="skins_count" class="block text-sm font-medium text-gray-300 mb-2">Skins Count</label>
                                        <input 
                                            type="number" 
                                            id="skins_count" 
                                            name="attributes[skins_count]" 
                                            value="{{ old('attributes.skins_count') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="150"
                                        >
                                    </div>
                                    
                                    <!-- Diamonds -->
                                    <div>
                                        <label for="diamonds" class="block text-sm font-medium text-gray-300 mb-2">Diamonds</label>
                                        <input 
                                            type="number" 
                                            id="diamonds" 
                                            name="attributes[diamonds]" 
                                            value="{{ old('attributes.diamonds') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="5000"
                                        >
                                    </div>
                                    
                                    <!-- Battle Points (BP) -->
                                    <div>
                                        <label for="bp" class="block text-sm font-medium text-gray-300 mb-2">Battle Points (BP)</label>
                                        <input 
                                            type="number" 
                                            id="bp" 
                                            name="attributes[bp]" 
                                            value="{{ old('attributes.bp') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="25000"
                                        >
                                    </div>
                                    
                                    <!-- Level -->
                                    <div>
                                        <label for="level" class="block text-sm font-medium text-gray-300 mb-2">Account Level</label>
                                        <input 
                                            type="number" 
                                            id="level" 
                                            name="attributes[level]" 
                                            value="{{ old('attributes.level') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="30"
                                        >
                                    </div>
                                    
                                    <!-- Win Rate -->
                                    <div>
                                        <label for="win_rate" class="block text-sm font-medium text-gray-300 mb-2">Win Rate (%)</label>
                                        <input 
                                            type="number" 
                                            id="win_rate" 
                                            name="attributes[win_rate]" 
                                            step="0.01"
                                            value="{{ old('attributes.win_rate') }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="65"
                                        >
                                    </div>
                                    
                                    <!-- Collection Tier -->
                                    <div>
                                        <label for="collection_tier" class="block text-sm font-medium text-gray-300 mb-2">{{ __('messages.collection_tier') }}</label>
                                        <select 
                                            id="collection_tier" 
                                            name="attributes[collection_tier]" 
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        >
                                            <option value="">-- Select Collection Tier --</option>
                                            <option value="Expert Collector" {{ old('attributes.collection_tier') === 'Expert Collector' ? 'selected' : '' }}>Expert Collector</option>
                                            <option value="Renowned Collector" {{ old('attributes.collection_tier') === 'Renowned Collector' ? 'selected' : '' }}>Renowned Collector</option>
                                            <option value="Exalted Collector" {{ old('attributes.collection_tier') === 'Exalted Collector' ? 'selected' : '' }}>Exalted Collector</option>
                                            <option value="Mega Collector" {{ old('attributes.collection_tier') === 'Mega Collector' ? 'selected' : '' }}>Mega Collector</option>
                                            <option value="World Collector" {{ old('attributes.collection_tier') === 'World Collector' ? 'selected' : '' }}>World Collector</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Images Section -->
                            <div class="mb-8" x-data="{ 
                                imageCount: 0,
                                maxImages: 10,
                                selectedFiles: [],
                                handleFileSelect(event) {
                                    const files = Array.from(event.target.files || []);
                                    if (files.length + this.imageCount > this.maxImages) {
                                        alert(`Maximum ${this.maxImages} images allowed. You can only select ${this.maxImages - this.imageCount} more.`);
                                        event.target.value = '';
                                        return;
                                    }
                                    this.selectedFiles = files;
                                    this.imageCount = files.length;
                                },
                                removeFile(index) {
                                    this.selectedFiles.splice(index, 1);
                                    this.imageCount = this.selectedFiles.length;
                                    // Update the file input
                                    const input = document.getElementById('images');
                                    const dt = new DataTransfer();
                                    this.selectedFiles.forEach(file => dt.items.add(file));
                                    input.files = dt.files;
                                }
                            }">
                                <h2 class="text-xl font-semibold text-white mb-6 flex items-center">
                                    <i class="fa-solid fa-images mr-3 text-red-600"></i>
                                    Account Images
                                </h2>
                                
                                <div class="space-y-4">
                                    <!-- Upload Images -->
                                    <div>
                                        <label for="images" class="block text-sm font-medium text-gray-300 mb-2">
                                            Upload Images <span class="text-gray-500">(Max 10 images)</span>
                                        </label>
                                        <div 
                                            class="flex items-center justify-center w-full border-2 border-dashed rounded-lg p-6 cursor-pointer hover:border-red-600 transition-colors" 
                                            style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.3);" 
                                            @click="$refs.fileInput.click()"
                                            @dragover.prevent="$event.dataTransfer.dropEffect = 'copy'"
                                            @drop.prevent="
                                                const files = Array.from($event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                                                if (files.length + imageCount > maxImages) {
                                                    alert(`Maximum ${maxImages} images allowed. You can only add ${maxImages - imageCount} more.`);
                                                    return;
                                                }
                                                selectedFiles = [...selectedFiles, ...files];
                                                imageCount = selectedFiles.length;
                                                const dt = new DataTransfer();
                                                selectedFiles.forEach(file => dt.items.add(file));
                                                $refs.fileInput.files = dt.files;
                                            "
                                        >
                                            <div class="text-center">
                                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-500 mb-2"></i>
                                                <p class="text-sm text-gray-400 mb-1">
                                                    <span class="text-red-600 hover:text-red-500">Click to upload</span> or drag and drop
                                                </p>
                                                <p class="text-xs text-gray-500">PNG, JPG, WEBP up to 10MB each (Max 10 images)</p>
                                                <p class="text-xs mt-2" :class="imageCount > 0 ? 'text-green-400' : 'text-gray-500'">
                                                    <span x-text="imageCount"></span> / <span x-text="maxImages"></span> images selected
                                                </p>
                                            </div>
                                            <input 
                                                type="file" 
                                                id="images" 
                                                name="images[]" 
                                                multiple 
                                                accept="image/jpeg,image/png,image/jpg,image/webp" 
                                                class="hidden"
                                                @change="handleFileSelect($event)"
                                                x-ref="fileInput"
                                            >
                                        </div>
                                        
                                        <!-- Selected Files Preview -->
                                        <div x-show="selectedFiles.length > 0" x-cloak class="mt-4">
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                <template x-for="(file, index) in selectedFiles" :key="index">
                                                    <div class="relative group">
                                                        <div class="aspect-video rounded-lg overflow-hidden" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                                                            <img 
                                                                :src="URL.createObjectURL(file)" 
                                                                :alt="file.name"
                                                                class="w-full h-full object-cover"
                                                            >
                                                        </div>
                                                        <button 
                                                            type="button"
                                                            @click="removeFile(index)"
                                                            class="absolute top-2 right-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                                        >
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                        <p class="text-xs text-gray-400 mt-1 truncate" x-text="file.name"></p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-end gap-4 pt-6 border-t" style="border-color: #2d2c31;" x-show="isMLBB" x-cloak>
                                <a href="{{ route('account.listed-accounts') }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 px-6 text-sm rounded-md ring-1 ring-secondary-ring text-gray-300 hover:text-white hover:bg-gray-800/50" style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;">
                                    Cancel
                                </a>
                                <button 
                                    type="submit"
                                    :disabled="!isMLBB"
                                    class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-3 sm:py-2.5 px-8 text-sm rounded-md"
                                >
                                    Create Account
                                </button>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

