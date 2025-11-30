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
                        @if($errors->any())
                            <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20">
                                <ul class="list-disc list-inside text-red-400 text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('account.listed-accounts.update', $account->id) }}" enctype="multipart/form-data" id="editAccountForm">
                            @csrf
                            @method('PUT')
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
                                            value="{{ $account->title }}"
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
                                        >{{ $account->description }}</textarea>
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
                                                    value="{{ number_format($account->price_dzd / 100, 2, '.', '') }}"
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
                                                <option value="available" {{ $account->status === 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="disabled" {{ $account->status === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                                <option value="sold" {{ $account->status === 'sold' ? 'selected' : '' }}>Sold</option>
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
                                    @php
                                        $attributesMap = [];
                                        foreach ($account->attributes as $attr) {
                                            $attributesMap[$attr->attribute_key] = $attr->attribute_value;
                                        }
                                    @endphp
                                    
                                    <!-- Rank -->
                                    <div>
                                        <label for="rank" class="block text-sm font-medium text-gray-300 mb-2">
                                            Rank
                                        </label>
                                        <input 
                                            type="text" 
                                            id="rank" 
                                            name="attributes[rank]" 
                                            value="{{ $attributesMap['rank'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="Epic IV"
                                        >
                                    </div>
                                    
                                    <!-- Heroes Count -->
                                    <div>
                                        <label for="heroes_count" class="block text-sm font-medium text-gray-300 mb-2">
                                            Heroes Count
                                        </label>
                                        <input 
                                            type="number" 
                                            id="heroes_count" 
                                            name="attributes[heroes_count]" 
                                            value="{{ $attributesMap['heroes_count'] ?? '' }}"
                                            class="w-full block border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none py-2.5 px-4 text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                            placeholder="85"
                                        >
                                    </div>
                                    
                                    <!-- Skins Count -->
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
                                </div>
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
                    
                    console.log('Form is submitting...');
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
    </script>
    @endpush
@endsection


