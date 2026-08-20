@extends('layouts.app')

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('{{ asset('storage/home_page/degaultbanner.webp') }}'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>

    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen pt-16 sm:pt-16 pb-20 md:pb-8">
        @include('components.dashboard-nav')

        <div class="relative z-10 px-4 sm:px-6 lg:px-8" style="padding-top: 122px;">
            <div class="mx-auto max-w-6xl">
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
                    <a href="{{ route('account.listed-accounts') }}" class="inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-2.5 px-4 text-sm rounded-md ring-1 text-gray-300 hover:text-white hover:bg-gray-800/50" style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;">
                        <i class="mr-2 fa-solid fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>

                <div class="rounded-xl overflow-hidden" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-filter: blur(12px);">
                    <div class="p-6 sm:p-8 lg:p-10">
                        <form
                            method="POST"
                            action="{{ route('account.listed-accounts.update', $account->id) }}"
                            enctype="multipart/form-data"
                            id="editAccountForm"
                            x-data="editAccountWizard()"
                        >
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
                                        <p class="text-red-400 font-medium">{{ session('error') }}</p>
                                    </div>
                                </div>
                            @endif
                            @if(session('success'))
                                <div class="mb-6 p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                                    <i class="fa-solid fa-check-circle mr-2"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            {{-- Stepper (same layout as create) --}}
                            <div class="mb-8">
                                <div class="hidden sm:flex items-center justify-between gap-2">
                                    <template x-for="(step, index) in steps" :key="step.id">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <button
                                                type="button"
                                                @click="goToStep(step.id)"
                                                class="flex items-center gap-2 min-w-0 group"
                                                :class="currentStep >= step.id ? 'text-white' : 'text-gray-500'"
                                            >
                                                <span
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-semibold transition-colors"
                                                    :class="currentStep === step.id ? 'bg-red-600 text-white' : (currentStep > step.id ? 'bg-red-600/20 text-red-400 ring-1 ring-red-500/40' : 'bg-[#1b1a1e] ring-1 ring-[#2d2c31]')"
                                                >
                                                    <span x-show="currentStep <= step.id" x-text="step.id"></span>
                                                    <i x-show="currentStep > step.id" x-cloak class="fa-solid fa-check text-xs"></i>
                                                </span>
                                                <span class="truncate text-sm font-medium" x-text="step.label"></span>
                                            </button>
                                            <div x-show="index < steps.length - 1" class="mx-2 h-px flex-1" :class="currentStep > step.id ? 'bg-red-500/50' : 'bg-[#2d2c31]'"></div>
                                        </div>
                                    </template>
                                </div>

                                <div class="sm:hidden">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-400">Step <span x-text="currentStep"></span> of <span x-text="steps.length"></span></span>
                                        <span class="font-medium text-white" x-text="steps[currentStep - 1]?.label"></span>
                                    </div>
                                    <div class="h-2 rounded-full overflow-hidden" style="background-color: #1b1a1e;">
                                        <div class="h-full bg-red-600 transition-all duration-300" :style="`width: ${(currentStep / steps.length) * 100}%`"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 1: Details --}}
                            <div x-show="currentStep === 1" x-cloak x-transition.opacity.duration.200ms>
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center">
                                    <i class="fa-solid fa-info-circle mr-3 text-red-600"></i>
                                    Basic Information
                                </h2>
                                <p class="text-sm text-gray-400 mb-6">Title, description, price, and listing status.</p>

                                <div class="space-y-6">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Account Title <span class="text-red-500">*</span></label>
                                        <input type="text" id="title" name="title" required value="{{ old('title', $account->title) }}" class="wizard-input" placeholder="Epic Rank Account - 150+ Skins">
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description <span class="text-red-500">*</span></label>
                                        <textarea id="description" name="description" rows="5" required class="wizard-input resize-none" placeholder="Describe your account in detail...">{{ old('description', $account->description) }}</textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="price_dzd" class="block text-sm font-medium text-gray-300 mb-2">Price (DA) <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <span class="wizard-field-addon">DA</span>
                                                <input type="number" id="price_dzd" name="price_dzd" step="0.01" required value="{{ old('price_dzd', $account->price_dzd ?? 0) }}" class="wizard-input wizard-input--prefix" placeholder="16228">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status <span class="text-red-500">*</span></label>
                                            @php $st = old('status', $account->status ?? 'available'); @endphp
                                            <select id="status" name="status" required class="wizard-input">
                                                <option value="available" {{ $st === 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="disabled" {{ $st === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                                <option value="pending" {{ $st === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="sold" {{ $st === 'sold' ? 'selected' : '' }}>Sold</option>
                                                <option value="cancelled" {{ $st === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2: Stats --}}
                            <div x-show="currentStep === 2" x-cloak x-transition.opacity.duration.200ms>
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center">
                                    <i class="fa-solid fa-list-check mr-3 text-red-600"></i>
                                    Account Stats
                                </h2>
                                <p class="text-sm text-gray-400 mb-6">Optional details buyers look for. You can skip empty fields.</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach([
                                        ['rank', 'Rank', 'text', 'Epic IV'],
                                        ['heroes_count', 'Heroes Count', 'number', '85'],
                                        ['skins_count', 'Skins Count', 'number', '150'],
                                        ['diamonds', 'Diamonds', 'number', '5000'],
                                        ['bp', 'Battle Points (BP)', 'number', '25000'],
                                        ['level', 'Account Level', 'number', '30'],
                                        ['win_rate', 'Win Rate (%)', 'number', '65'],
                                    ] as [$key, $label, $type, $placeholder])
                                        <div>
                                            <label for="{{ $key }}" class="block text-sm font-medium text-gray-300 mb-2">{{ $label }}</label>
                                            <input
                                                type="{{ $type }}"
                                                id="{{ $key }}"
                                                name="attributes[{{ $key }}]"
                                                @if($key === 'win_rate') step="0.01" @endif
                                                value="{{ old('attributes.'.$key, $attributesMap[$key] ?? '') }}"
                                                class="wizard-input"
                                                placeholder="{{ $placeholder }}"
                                            >
                                        </div>
                                    @endforeach

                                    <div class="md:col-span-2">
                                        <label for="collection_tier" class="block text-sm font-medium text-gray-300 mb-2">{{ __('messages.collection_tier') }}</label>
                                        <select id="collection_tier" name="attributes[collection_tier]" class="wizard-input">
                                            <option value="">-- Select Collection Tier --</option>
                                            @foreach(['Expert Collector', 'Renowned Collector', 'Exalted Collector', 'Mega Collector', 'World Collector'] as $tier)
                                                <option value="{{ $tier }}" {{ old('attributes.collection_tier', $attributesMap['collection_tier'] ?? '') === $tier ? 'selected' : '' }}>{{ $tier }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3: Skins --}}
                            <div x-show="currentStep === 3" x-cloak x-transition.opacity.duration.200ms x-data="highlightedSkinsEdit({{ json_encode($attributesMap['highlighted_skins'] ?? '') }})" x-init="init()">
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center justify-between gap-3">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-star mr-3 text-red-600"></i>
                                        Highlighted Skins
                                    </span>
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
                                <p class="text-sm text-gray-400 mb-4">Search and select skins featured on this listing.</p>

                                <div class="mb-4 relative">
                                    <i class="fa-solid fa-search wizard-field-icon"></i>
                                    <input
                                        type="text"
                                        x-model="searchQuery"
                                        placeholder="Search by hero name, skin name, or role..."
                                        class="wizard-input wizard-input--icon"
                                    >
                                </div>

                                <div x-show="loading" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Loading skins data...</p>
                                </div>

                                <div x-show="!loading" class="space-y-3 max-h-[600px] overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #2d2c31 #1b1a1e;">
                                    <template x-for="(category, roleIndex) in filteredCategories()" :key="roleIndex">
                                        <div class="border rounded-lg overflow-hidden" style="border-color: #2d2c31; background-color: #1b1a1e;">
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

                                            <div x-show="expandedRoles[roleIndex]" x-collapse class="px-4 pb-3 space-y-2">
                                                <template x-for="(hero, heroIndex) in category.heroes" :key="heroIndex">
                                                    <div class="border rounded-md mt-2" style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.3);">
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

                                                        <div x-show="expandedHeroes[`${roleIndex}-${heroIndex}`]" x-collapse class="px-3 pb-2 flex flex-wrap gap-2">
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

                                    <div x-show="!loading && filteredCategories().length === 0" class="text-center py-12">
                                        <i class="fa-solid fa-search text-4xl text-gray-600 mb-3"></i>
                                        <p class="text-gray-400">No skins found matching your search</p>
                                    </div>
                                </div>

                                <div x-show="getSelectedCount() > 0" class="mt-4 p-4 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <h3 class="text-sm font-semibold text-white mb-2">Selected Skins (<span x-text="getSelectedCount()"></span>)</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(skin, index) in getSelectedSkinsList()" :key="index">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                                <span x-text="`${skin.hero} - ${skin.name}`"></span>
                                                <button type="button" @click="toggleSkinById(skin.id)" class="hover:text-red-400 transition-colors">
                                                    <i class="fa-solid fa-times text-xs"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    id="highlighted_skins_input"
                                    name="attributes[highlighted_skins]"
                                    value="{{ $attributesMap['highlighted_skins'] ?? '' }}"
                                >
                            </div>

                            {{-- Step 4: Photos --}}
                            <div x-show="currentStep === 4" x-cloak x-transition.opacity.duration.200ms>
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center">
                                    <i class="fa-solid fa-images mr-3 text-red-600"></i>
                                    Account Images
                                </h2>
                                <p class="text-sm text-gray-400 mb-6">Keep, remove, or upload gallery photos (max 10). The listing poster is kept separately as cover.</p>

                                <div class="space-y-4">
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

                                    <div id="allImagesContainer">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="imagesGrid">
                                            @if($account->images && $account->images->where('is_cover', false)->count() > 0)
                                                @foreach($account->images->where('is_cover', false) as $image)
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

                            {{-- Footer nav --}}
                            <div class="flex items-center justify-between gap-4 pt-8 mt-8 border-t" style="border-color: #2d2c31;">
                                <button
                                    type="button"
                                    x-show="currentStep > 1"
                                    @click="prevStep()"
                                    class="inline-flex items-center py-2.5 px-5 text-sm rounded-md text-gray-300 hover:text-white ring-1 ring-[#2d2c31]"
                                >
                                    <i class="fa-solid fa-arrow-left mr-2"></i>
                                    Back
                                </button>
                                <div x-show="currentStep === 1"></div>

                                <div class="flex items-center gap-3 ml-auto">
                                    <a
                                        href="{{ route('account.listed-accounts') }}"
                                        class="inline-flex items-center py-2.5 px-5 text-sm rounded-md text-gray-300 hover:text-white ring-1 ring-[#2d2c31]"
                                    >
                                        Cancel
                                    </a>
                                    <button
                                        type="button"
                                        x-show="currentStep < steps.length"
                                        @click="nextStep()"
                                        class="inline-flex items-center py-2.5 px-6 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white font-medium"
                                    >
                                        Continue
                                        <i class="fa-solid fa-arrow-right ml-2"></i>
                                    </button>
                                    <button
                                        type="submit"
                                        id="submitBtn"
                                        x-show="currentStep === steps.length"
                                        class="inline-flex items-center py-2.5 px-8 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white font-medium disabled:opacity-50"
                                    >
                                        <span id="submitText">Save Changes</span>
                                        <span id="submitLoading" style="display: none;">
                                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .wizard-input {
            display: block;
            width: 100%;
            min-height: 2.75rem;
            border-radius: 0.375rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #fff;
            background-color: #1b1a1e;
            border: 1px solid #2d2c31;
        }
        .wizard-input--prefix { padding-left: 2.75rem; }
        .wizard-input--icon { padding-left: 2.5rem; }
        .wizard-field-addon,
        .wizard-field-icon {
            pointer-events: none;
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            align-items: center;
            padding-left: 0.75rem;
            color: #9ca3af;
            font-size: 0.875rem;
            line-height: 1;
        }
        .wizard-field-icon {
            width: 2.5rem;
            justify-content: center;
            padding-left: 0;
        }
        .wizard-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.35);
        }
        .wizard-input::placeholder { color: #6b7280; }
        select.wizard-input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
            padding-right: 2.5rem;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function editAccountWizard() {
            return {
                currentStep: 1,
                steps: [
                    { id: 1, label: 'Details' },
                    { id: 2, label: 'Stats' },
                    { id: 3, label: 'Skins' },
                    { id: 4, label: 'Photos' },
                ],
                goToStep(step) {
                    const target = Number(step);
                    if (!Number.isFinite(target) || target < 1 || target > this.steps.length) return;
                    this.currentStep = target;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                nextStep() {
                    if (this.currentStep < this.steps.length) {
                        this.currentStep += 1;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep -= 1;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editAccountForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    try {
                        const hiddenInput = document.getElementById('highlighted_skins_input');
                        if (hiddenInput && Array.isArray(window.highlightedSelectedSkinIds)) {
                            hiddenInput.value = window.highlightedSelectedSkinIds.join(',');
                        }
                    } catch (_) {}

                    const imagesGrid = document.getElementById('imagesGrid');
                    const fileInput = document.getElementById('images');
                    const keptExistingImages = imagesGrid ? imagesGrid.querySelectorAll('[data-image-type="existing"]').length : 0;
                    const newImagesCount = fileInput && fileInput.files ? fileInput.files.length : 0;
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

            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('images');
            const imagesGrid = document.getElementById('imagesGrid');
            const maxImages = 10;
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);

            window.deletedImageIds = [];

            if (uploadArea && fileInput && imagesGrid) {
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
                    if (files.length > 0) handleFileSelection(files);
                });

                fileInput.addEventListener('change', function(e) {
                    const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));
                    const existingCount = imagesGrid.querySelectorAll('[data-image-type="existing"]').length;
                    const newCount = imagesGrid.querySelectorAll('[data-image-type="new"]').length;
                    const totalCurrent = existingCount + newCount;

                    if (totalCurrent + files.length > maxImages) {
                        alert(`Maximum ${maxImages} images allowed. You currently have ${totalCurrent} images. You can only add ${maxImages - totalCurrent} more.`);
                        fileInput.value = '';
                        return;
                    }

                    files.forEach((file) => {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            const div = document.createElement('div');
                            const safeFileName = escapeHtml(file.name);
                            div.className = 'relative group';
                            div.setAttribute('data-image-type', 'new');
                            div.setAttribute('data-file-name', file.name);
                            div.innerHTML = `
                                <div class="aspect-video rounded-lg overflow-hidden" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                                    <img src="${ev.target.result}" alt="${safeFileName}" class="w-full h-full object-cover">
                                </div>
                                <button type="button" onclick="removeImage(null, this, 'new')" class="absolute top-2 right-2 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 hover:bg-red-700 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            `;
                            imagesGrid.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }

            function handleFileSelection(files) {
                const existingCount = imagesGrid.querySelectorAll('[data-image-type="existing"]').length;
                const newCount = imagesGrid.querySelectorAll('[data-image-type="new"]').length;
                const totalCurrent = existingCount + newCount;

                if (totalCurrent + files.length > maxImages) {
                    alert(`Maximum ${maxImages} images allowed. You currently have ${totalCurrent} images. You can only add ${maxImages - totalCurrent} more.`);
                    return;
                }

                const existingFiles = fileInput.files ? Array.from(fileInput.files) : [];
                const dt = new DataTransfer();
                existingFiles.forEach(file => dt.items.add(file));
                files.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;

                files.forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        const safeFileName = escapeHtml(file.name);
                        div.className = 'relative group';
                        div.setAttribute('data-image-type', 'new');
                        div.setAttribute('data-file-name', file.name);
                        div.innerHTML = `
                            <div class="aspect-video rounded-lg overflow-hidden" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                                <img src="${e.target.result}" alt="${safeFileName}" class="w-full h-full object-cover">
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

            function removeImage(imageId, button, type) {
                const imageContainer = button.closest('[data-image-type]');
                if (!imageContainer) return;

                if (type === 'existing' && imageId) {
                    const keepInput = document.getElementById('keep_image_' + imageId);
                    if (keepInput) keepInput.remove();
                    window.deletedImageIds.push(imageId);
                } else if (type === 'new') {
                    const fileName = imageContainer.getAttribute('data-file-name');
                    if (fileInput && fileInput.files && fileName) {
                        const dt = new DataTransfer();
                        Array.from(fileInput.files).forEach((file) => {
                            if (file.name !== fileName) dt.items.add(file);
                        });
                        fileInput.files = dt.files;
                    }
                }

                imageContainer.style.transition = 'opacity 0.3s';
                imageContainer.style.opacity = '0';
                setTimeout(() => imageContainer.remove(), 300);
            }

            window.removeImage = removeImage;
        });

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
