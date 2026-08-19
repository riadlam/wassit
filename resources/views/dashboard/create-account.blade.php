@extends('layouts.app')

@php
    $initialStep = 1;
    if (old('game_id')) {
        $initialStep = 2;
    }
    if ($errors->hasAny(['title', 'description', 'price_dzd', 'status'])) {
        $initialStep = max($initialStep, 2);
    }
    if ($errors->has('attributes') || $errors->has('attributes.*')) {
        $initialStep = max($initialStep, 3);
    }
    if ($errors->has('images') || $errors->has('images.*')) {
        $initialStep = 7;
    }

    $listingPreview = config('listing_preview');

    $oldHighlightedSkinIds = array_values(array_filter(array_map(
        'intval',
        explode(',', (string) old('attributes.highlighted_skins', ''))
    )));
@endphp

@section('content')
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('storage/home_page/degaultbanner.webp') }}');"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>

    <div class="relative z-10 min-h-screen pt-16 sm:pt-16 pb-20 md:pb-8">
        @include('components.dashboard-nav')

        <div class="relative z-10 px-4 sm:px-6 lg:px-8" style="padding-top: 122px;">
            <div class="mx-auto max-w-6xl">
                <div class="flex flex-wrap gap-4 justify-between items-center mb-8">
                    <div class="flex gap-x-3 items-center">
                        <div class="hidden md:flex justify-center items-center p-3 w-16 h-16 rounded-full border shrink-0" style="background-color: #1b1a1e; border-color: #2d2c31; color: #9ca3af;">
                            <i class="fa-lg fa-solid fa-plus"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold tracking-tight sm:text-2xl text-white">List New Account</h1>
                            <p class="text-sm text-gray-400">Step-by-step listing wizard</p>
                        </div>
                    </div>
                    <a href="{{ route('account.listed-accounts') }}" class="inline-flex items-center py-2.5 px-4 text-sm rounded-md text-gray-300 hover:text-white ring-1" style="background-color: rgba(14, 16, 21, 0.5); border-color: #2d2c31;">
                        <i class="mr-2 fa-solid fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>

                <div class="rounded-xl overflow-hidden" style="background-color: rgba(14, 16, 21, 0.75); border: 1px solid #2d2c31; backdrop-blur-md;">
                    <div class="p-6 sm:p-8 lg:p-10">
                        @if($errors->any())
                            <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20">
                                <p class="text-red-400 font-medium mb-2">Please fix the following errors:</p>
                                <ul class="list-disc list-inside text-red-400 text-sm space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-6 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('account.listed-accounts.store') }}"
                            enctype="multipart/form-data"
                            id="createAccountForm"
                            x-data="createAccountWizard({ mlbbId: {{ $mlbbId ?? 'null' }}, initialStep: {{ $initialStep }}, initialGameId: '{{ (string) old('game_id', $mlbbId ?? '') }}' })"
                            @submit="handleSubmit($event)"
                        >
                            @csrf

                            {{-- Stepper --}}
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

                            {{-- Step 1: Game --}}
                            <div x-show="currentStep === 1" x-cloak x-transition.opacity.duration.200ms>
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center">
                                    <i class="fa-solid fa-gamepad mr-3 text-red-600"></i>
                                    Select Game
                                </h2>
                                <p class="text-sm text-gray-400 mb-6">Choose which game this account belongs to.</p>

                                <input type="hidden" name="game_id" x-model="selectedGameId">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($games as $game)
                                        @php
                                            $artworkPath = config('game_artwork.' . $game->slug);
                                            $gameIcon = ($artworkPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($artworkPath))
                                                ? \Illuminate\Support\Facades\Storage::url($artworkPath)
                                                : ($game->icon_url ?: null);
                                        @endphp
                                        <button
                                            type="button"
                                            @click="selectGame('{{ $game->id }}')"
                                            class="flex items-center gap-4 p-4 rounded-xl text-left transition-all ring-1"
                                            :class="String(selectedGameId) === '{{ $game->id }}' ? 'ring-red-500 bg-red-500/10' : 'ring-[#2d2c31] hover:ring-red-500/40 bg-[#1b1a1e]'"
                                        >
                                            @if($gameIcon)
                                                <img src="{{ $gameIcon }}" alt="{{ $game->name }}" class="h-14 w-14 rounded-xl object-cover shrink-0">
                                            @else
                                                <div class="flex h-14 w-14 items-center justify-center rounded-xl shrink-0" style="background-color: rgba(239,68,68,0.1);">
                                                    <i class="fa-solid fa-gamepad text-red-500"></i>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="font-semibold text-white truncate">{{ $game->name }}</p>
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    @if($game->slug === 'mlbb')
                                                        Available now
                                                    @else
                                                        Coming soon
                                                    @endif
                                                </p>
                                            </div>
                                            <i class="fa-solid fa-circle-check ml-auto text-red-500" x-show="String(selectedGameId) === '{{ $game->id }}'" x-cloak></i>
                                        </button>
                                    @endforeach
                                </div>

                                <div x-show="isOtherGame" x-cloak class="mt-6 rounded-lg p-6 text-center" style="background-color: rgba(27, 26, 30, 0.5); border: 1px solid #2d2c31;">
                                    <i class="fa-solid fa-clock text-4xl text-gray-500 mb-3"></i>
                                    <h3 class="text-lg font-semibold text-white mb-1">Coming Soon</h3>
                                    <p class="text-gray-400 text-sm">Only Mobile Legends listings are supported right now.</p>
                                </div>

                                <p x-show="stepError" x-text="stepError" class="mt-4 text-sm text-red-400"></p>
                            </div>

                            {{-- Step 2: Basic info --}}
                            <div x-show="currentStep === 2 && isMLBB" x-cloak x-transition.opacity.duration.200ms>
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center">
                                    <i class="fa-solid fa-info-circle mr-3 text-red-600"></i>
                                    Basic Information
                                </h2>
                                <p class="text-sm text-gray-400 mb-6">Title, description, price, and listing status.</p>

                                <div class="space-y-6">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Account Title <span class="text-red-500">*</span></label>
                                        <input type="text" id="title" name="title" required value="{{ old('title') }}" class="wizard-input" placeholder="Epic Rank Account - 150+ Skins">
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description <span class="text-red-500">*</span></label>
                                        <textarea id="description" name="description" rows="5" required class="wizard-input resize-none" placeholder="Describe your account in detail...">{{ old('description') }}</textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="price_dzd" class="block text-sm font-medium text-gray-300 mb-2">Price (DA) <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <span class="wizard-field-addon">DA</span>
                                                <input type="number" id="price_dzd" name="price_dzd" step="0.01" required value="{{ old('price_dzd') }}" class="wizard-input wizard-input--prefix" placeholder="16228">
                                            </div>
                                        </div>
                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status <span class="text-red-500">*</span></label>
                                            <select id="status" name="status" required class="wizard-input">
                                                <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                                                <option value="disabled" {{ old('status') === 'disabled' ? 'selected' : '' }}>Disabled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <p x-show="stepError" x-text="stepError" class="mt-4 text-sm text-red-400"></p>
                            </div>

                            {{-- Step 3: Attributes --}}
                            <div x-show="currentStep === 3 && isMLBB" x-cloak x-transition.opacity.duration.200ms>
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
                                                value="{{ old('attributes.'.$key) }}"
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
                                                <option value="{{ $tier }}" {{ old('attributes.collection_tier') === $tier ? 'selected' : '' }}>{{ $tier }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4: Skins --}}
                            <div id="accountSkinsPicker" x-show="currentStep === 4 && isMLBB" x-cloak x-transition.opacity.duration.200ms x-data="highlightedSkinsPicker()" x-ref="skinsPicker">
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center justify-between gap-3">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-star mr-3 text-red-600"></i>
                                        Highlighted Skins
                                    </span>
                                    <span class="text-sm text-gray-400" x-show="getSelectedCount() > 0">
                                        <span x-text="getSelectedCount()"></span> selected
                                    </span>
                                </h2>
                                <p class="text-sm text-gray-400 mb-4">Pick a hero, then check the skins you want to feature.</p>

                                <div x-show="!selectedHero" class="mb-4 relative">
                                    <i class="fa-solid fa-search wizard-field-icon"></i>
                                    <input type="text" x-model="searchQuery" placeholder="Search heroes..." class="wizard-input wizard-input--icon">
                                </div>

                                <template x-if="selectedHero">
                                    <div class="mb-4 flex items-center gap-3">
                                        <button type="button" @click="clearHero()" class="inline-flex items-center py-2 px-3 text-sm rounded-md text-gray-300 hover:text-white ring-1 ring-[#2d2c31]">
                                            <i class="fa-solid fa-arrow-left mr-2"></i>
                                            Heroes
                                        </button>
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img
                                                :src="selectedHero.avatar_url || placeholderAvatar"
                                                :alt="selectedHero.name"
                                                class="h-10 w-10 rounded-xl object-cover ring-1 ring-[#2d2c31] shrink-0"
                                                @@error="$event.target.src = placeholderAvatar"
                                            >
                                            <div class="min-w-0">
                                                <p class="font-semibold text-white truncate" x-text="selectedHero.name"></p>
                                                <p class="text-xs text-gray-400">
                                                    <span x-text="heroSkins.length"></span> skin(s)
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="listError" x-cloak class="mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm text-red-300" x-text="listError"></div>
                                <div x-show="detailError" x-cloak class="mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm text-red-300" x-text="detailError"></div>

                                <div x-show="loadingList && !selectedHero" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Loading heroes...</p>
                                </div>

                                <div x-show="!loadingList && !selectedHero" class="max-h-[560px] overflow-y-auto pr-1">
                                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 sm:gap-3">
                                        <template x-for="heroItem in filteredHeroes" :key="heroItem.id + '-' + heroItem.name">
                                            <button
                                                type="button"
                                                @click="selectHero(heroItem)"
                                                class="flex flex-col items-center gap-1.5 rounded-xl p-2 text-center transition ring-1 sm:gap-2 sm:p-3"
                                                :class="heroHasSelection(heroItem.name) ? 'ring-red-500 bg-red-500/10' : 'ring-[#2d2c31] bg-[#1b1a1e] hover:ring-red-500/40'"
                                            >
                                                <img
                                                    :src="heroItem.avatar_url || placeholderAvatar"
                                                    :alt="heroItem.name"
                                                    class="w-full aspect-square rounded-xl object-cover ring-1 ring-white/10"
                                                    loading="lazy"
                                                    @@error="$event.target.src = placeholderAvatar"
                                                >
                                                <span class="w-full truncate text-xs font-medium text-white" x-text="heroItem.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <p x-show="filteredHeroes.length === 0" class="py-12 text-center text-sm text-gray-500">No heroes found.</p>
                                </div>

                                <div x-show="selectedHero && loadingDetail" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Loading skins...</p>
                                </div>

                                <div x-show="selectedHero && !loadingDetail" class="max-h-[560px] overflow-y-auto pr-1">
                                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 sm:gap-3">
                                        <template x-for="(skin, index) in heroSkins" :key="(selectedHero?.name || 'hero') + '-' + index + '-' + skin.name">
                                            <label
                                                class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl ring-1 transition"
                                                :class="isSkinSelected(skin) ? 'ring-red-500 bg-red-500/10' : 'ring-[#2d2c31] bg-[#1b1a1e] hover:ring-red-500/40'"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="absolute right-1.5 top-1.5 z-20 h-4 w-4 rounded border-gray-500 text-red-600 focus:ring-red-500 sm:right-2 sm:top-2"
                                                    :checked="isSkinSelected(skin)"
                                                    @change="toggleSkin(skin)"
                                                >
                                                <div class="relative bg-[#0e1015]">
                                                    <img
                                                        :src="skinImage(skin)"
                                                        :alt="skin.name"
                                                        class="block w-full h-auto"
                                                        loading="lazy"
                                                        @@error="$event.target.src = placeholderAvatar"
                                                    >
                                                    <div class="pointer-events-none absolute inset-x-1.5 top-1.5 flex max-w-[70%] flex-col items-start gap-0.5">
                                                        <span
                                                            x-show="skin.painted"
                                                            class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 bg-rose-500/20 text-rose-200 ring-rose-400/40"
                                                        >Painted</span>
                                                        <template x-for="tag in (skin.tags || [])" :key="tag.name">
                                                            <img x-show="tag.image_url" :src="skinImage(tag)" :alt="tag.name" class="h-5 w-auto max-w-full object-contain object-left drop-shadow" loading="lazy">
                                                        </template>
                                                        <span
                                                            x-show="!skin.painted && (!skin.tags || !skin.tags.some(tag => tag.image_url)) && skin.rarity"
                                                            class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1"
                                                            :class="rarityBadgeClass(skin.rarity)"
                                                            x-text="skin.rarity"
                                                        ></span>
                                                    </div>
                                                </div>
                                                <div class="px-1.5 py-1.5 sm:px-2.5 sm:py-2">
                                                    <p class="text-xs font-medium text-white leading-snug truncate sm:text-sm" x-text="skin.name"></p>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                    <p x-show="heroSkins.length === 0" class="py-12 text-center text-sm text-gray-500">No skins found for this hero.</p>
                                </div>

                                <div x-show="getSelectedCount() > 0" class="mt-4 p-4 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="item in selectedSkins" :key="item.key">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                                <span x-text="item.hero + ' - ' + item.name"></span>
                                                <button type="button" @click="removeSelected(item.key)" class="hover:text-red-300">
                                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <input type="hidden" id="highlighted_skins_input" name="attributes[highlighted_skins]" value="{{ old('attributes.highlighted_skins') }}">
                            </div>

                            {{-- Step 5: Recalls --}}
                            <div id="accountRecallsPicker" x-show="currentStep === 5 && isMLBB" x-cloak x-transition.opacity.duration.200ms x-data="catalogItemPicker({ endpoint: '/api/mlbb/recalls', itemsKey: 'recalls', inputId: 'highlighted_recalls_input', step: 5 })" x-ref="recallsPicker">
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center justify-between gap-3">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-rotate mr-3 text-red-600"></i>
                                        Recalls
                                    </span>
                                    <span class="text-sm text-gray-400" x-show="selected.length > 0">
                                        <span x-text="selected.length"></span> selected
                                    </span>
                                </h2>
                                <p class="text-sm text-gray-400 mb-4">Pick standout recall effects to feature on your listing.</p>

                                <div class="mb-4 relative">
                                    <i class="fa-solid fa-search wizard-field-icon"></i>
                                    <input type="text" x-model="searchQuery" placeholder="Search recalls..." class="wizard-input wizard-input--icon">
                                </div>

                                <div x-show="error" x-cloak class="mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm text-red-300" x-text="error"></div>

                                <div x-show="loading" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Loading recalls...</p>
                                </div>

                                <div x-show="!loading" class="max-h-[560px] overflow-y-auto pr-1 space-y-5">
                                    <template x-for="group in filteredGroups" :key="group.group">
                                        <div>
                                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400" x-text="group.group"></h3>
                                            <div class="grid grid-cols-5 gap-3">
                                                <template x-for="catalogItem in group.items" :key="group.group + '-' + catalogItem.name">
                                                    <label
                                                        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl ring-1 transition"
                                                        :class="isSelected(catalogItem) ? 'ring-red-500 bg-red-500/10' : 'ring-[#2d2c31] bg-[#1b1a1e] hover:ring-red-500/40'"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="absolute right-2 top-2 z-20 h-4 w-4 rounded border-gray-500 text-red-600 focus:ring-red-500"
                                                            :checked="isSelected(catalogItem)"
                                                            @change="toggleItem(catalogItem)"
                                                        >
                                                        <div class="relative flex aspect-square items-center justify-center overflow-hidden bg-black/20 p-2">
                                                            <img
                                                                :src="catalogItem.image_url || catalogItem.thumbnail_url"
                                                                :alt="catalogItem.name"
                                                                class="max-h-full max-w-full object-contain"
                                                                loading="lazy"
                                                                @@error="$event.target.src = catalogItem.thumbnail_url"
                                                            >
                                                        </div>
                                                        <div class="px-2 py-2">
                                                            <p class="truncate text-xs font-medium text-white" x-text="catalogItem.name"></p>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="filteredGroups.length === 0" class="py-12 text-center text-sm text-gray-500">No recalls found.</p>
                                </div>

                                <div x-show="selected.length > 0" class="mt-4 p-4 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="name in selected" :key="name">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                                <span x-text="selectedLabel(name)"></span>
                                                <button type="button" @click="removeSelected(name)" class="hover:text-red-300">
                                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <input type="hidden" id="highlighted_recalls_input" name="attributes[highlighted_recalls]" value="{{ old('attributes.highlighted_recalls') }}">
                            </div>

                            {{-- Step 6: Emotes --}}
                            <div id="accountEmotesPicker" x-show="currentStep === 6 && isMLBB" x-cloak x-transition.opacity.duration.200ms x-data="catalogItemPicker({ endpoint: '/api/mlbb/emotes', itemsKey: 'emotes', inputId: 'highlighted_emotes_input', step: 6 })" x-ref="emotesPicker">
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center justify-between gap-3">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-face-smile mr-3 text-red-600"></i>
                                        Emotes
                                    </span>
                                    <span class="text-sm text-gray-400" x-show="selected.length > 0">
                                        <span x-text="selected.length"></span> selected
                                    </span>
                                </h2>
                                <p class="text-sm text-gray-400 mb-4">Pick standout battle emotes to feature on your listing.</p>

                                <div class="mb-4 relative">
                                    <i class="fa-solid fa-search wizard-field-icon"></i>
                                    <input type="text" x-model="searchQuery" placeholder="Search emotes..." class="wizard-input wizard-input--icon">
                                </div>

                                <div x-show="error" x-cloak class="mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm text-red-300" x-text="error"></div>

                                <div x-show="loading" class="text-center py-12">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Loading emotes...</p>
                                </div>

                                <div x-show="!loading" class="max-h-[560px] overflow-y-auto pr-1 space-y-5">
                                    <template x-for="group in filteredGroups" :key="group.group">
                                        <div>
                                            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400" x-text="group.group"></h3>
                                            <div class="grid grid-cols-5 gap-3">
                                                <template x-for="catalogItem in group.items" :key="group.group + '-' + catalogItem.name">
                                                    <label
                                                        class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl ring-1 transition"
                                                        :class="isSelected(catalogItem) ? 'ring-red-500 bg-red-500/10' : 'ring-[#2d2c31] bg-[#1b1a1e] hover:ring-red-500/40'"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="absolute right-2 top-2 z-20 h-4 w-4 rounded border-gray-500 text-red-600 focus:ring-red-500"
                                                            :checked="isSelected(catalogItem)"
                                                            @change="toggleItem(catalogItem)"
                                                        >
                                                        <div class="relative flex aspect-square items-center justify-center overflow-hidden bg-black/20 p-2">
                                                            <img
                                                                :src="catalogItem.image_url || catalogItem.thumbnail_url"
                                                                :alt="catalogItem.name"
                                                                class="max-h-full max-w-full object-contain"
                                                                loading="lazy"
                                                                @@error="$event.target.src = catalogItem.thumbnail_url"
                                                            >
                                                        </div>
                                                        <div class="px-2 py-2">
                                                            <p class="truncate text-xs font-medium text-white" x-text="catalogItem.name"></p>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="filteredGroups.length === 0" class="py-12 text-center text-sm text-gray-500">No emotes found.</p>
                                </div>

                                <div x-show="selected.length > 0" class="mt-4 p-4 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="name in selected" :key="name">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                                <span x-text="selectedLabel(name)"></span>
                                                <button type="button" @click="removeSelected(name)" class="hover:text-red-300">
                                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <input type="hidden" id="highlighted_emotes_input" name="attributes[highlighted_emotes]" value="{{ old('attributes.highlighted_emotes') }}">
                            </div>

                            {{-- Step 7: Images --}}
                            <div id="accountImagesPicker" x-show="currentStep === 7 && isMLBB" x-cloak x-transition.opacity.duration.200ms x-data="accountImagesPicker()" x-ref="imagesPicker">
                                <h2 class="text-xl font-semibold text-white mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-images text-red-600"></i>
                                    Account Photos
                                    <span class="relative inline-flex" @mouseenter="showPrimaryHelp = true" @mouseleave="showPrimaryHelp = false">
                                        <button
                                            type="button"
                                            class="flex h-6 w-6 items-center justify-center rounded-full text-gray-400 ring-1 ring-[#2d2c31] hover:text-white hover:ring-red-500/50"
                                            @click="showPrimaryHelp = !showPrimaryHelp"
                                            aria-label="Primary photo help"
                                        >
                                            <i class="fa-solid fa-question text-[11px]"></i>
                                        </button>
                                        <div
                                            x-show="showPrimaryHelp"
                                            x-cloak
                                            x-transition.opacity.duration.150ms
                                            class="absolute left-0 top-8 z-30 w-72 rounded-xl p-3 shadow-xl sm:w-80"
                                            style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        >
                                            <p class="text-xs text-gray-300 mb-2">The <span class="text-red-400 font-semibold">primary</span> photo should be your in-game Profile screen, like this:</p>
                                            <img
                                                src="{{ asset('images/mlbb-primary-photo-example.png') }}"
                                                alt="Example of an MLBB profile screenshot for the primary photo"
                                                class="w-full rounded-lg ring-1 ring-[#2d2c31]"
                                            >
                                            <p class="mt-2 text-[11px] text-gray-500">This photo is shown first on your listing. Other photos can be extra screenshots.</p>
                                        </div>
                                    </span>
                                </h2>
                                <p class="text-sm text-gray-400 mb-6">Upload screenshots. The first photo is the <span class="text-white">primary</span> listing image. At least one image is required.</p>

                                <div
                                    class="flex items-center justify-center w-full border-2 border-dashed rounded-lg p-8 cursor-pointer hover:border-red-600 transition-colors"
                                    style="border-color: #2d2c31; background-color: rgba(27, 26, 30, 0.3);"
                                    @click="$refs.fileInput.click()"
                                    @dragover.prevent
                                    @drop.prevent="addFiles(Array.from($event.dataTransfer.files).filter(f => f.type.startsWith('image/')))"
                                >
                                    <div class="text-center">
                                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-500 mb-3"></i>
                                        <p class="text-sm text-gray-400"><span class="text-red-500">Click to upload</span> or drag and drop</p>
                                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP up to 10MB each</p>
                                        <p class="text-xs mt-3" :class="imageCount > 0 ? 'text-green-400' : 'text-gray-500'">
                                            <span x-text="imageCount"></span> / <span x-text="maxImages"></span> selected
                                        </p>
                                    </div>
                                    <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" x-ref="fileInput" @change="addFiles(Array.from($event.target.files))">
                                </div>

                                <div x-show="selectedFiles.length > 0" x-cloak class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <template x-for="(file, index) in selectedFiles" :key="index">
                                        <div class="relative group">
                                            <div
                                                class="aspect-video rounded-lg overflow-hidden ring-2 transition"
                                                :class="index === 0 ? 'ring-red-500' : 'ring-[#2d2c31]'"
                                            >
                                                <img :src="URL.createObjectURL(file)" :alt="file.name" class="w-full h-full object-cover">
                                            </div>
                                            <span
                                                x-show="index === 0"
                                                class="absolute left-2 top-2 rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-red-600 text-white"
                                            >Primary</span>
                                            <button
                                                type="button"
                                                x-show="index !== 0"
                                                @click="setPrimary(index)"
                                                class="absolute left-2 top-2 rounded-md px-2 py-0.5 text-[10px] font-medium bg-black/70 text-gray-200 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >Set primary</button>
                                            <button type="button" @click="removeFile(index)" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-600 text-white text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <p x-show="stepError" x-text="stepError" class="mt-4 text-sm text-red-400"></p>
                            </div>

                            {{-- Step 8: View Listing Preview --}}
                            <div x-show="currentStep === 8 && isMLBB" x-cloak x-transition.opacity.duration.200ms x-data="listingPreview()" x-init="init()">
                                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h2 class="text-xl font-semibold text-white mb-1 flex items-center gap-2">
                                            <i class="fa-solid fa-eye text-red-600"></i>
                                            View Listing
                                        </h2>
                                        <p class="text-sm text-gray-400">This poster is saved as the store cover on desktop. Gallery photos stay on the account page. PNG download is desktop only (1080px for social posts).</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2" x-show="canDownloadPoster()">
                                        <button
                                            type="button"
                                            @click="downloadPoster()"
                                            :disabled="downloading"
                                            class="inline-flex items-center py-2.5 px-5 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white font-medium disabled:opacity-50"
                                        >
                                            <i class="fa-solid fa-download mr-2"></i>
                                            <span x-text="downloading ? (downloadStatus || 'Preparing…') : 'Download PNG'"></span>
                                        </button>
                                    </div>
                                </div>
                                <p x-show="!canDownloadPoster()" class="mb-4 text-sm text-amber-200/90 rounded-lg px-3 py-2" style="background: rgba(251, 191, 36, 0.08); border: 1px solid rgba(251, 191, 36, 0.2);">
                                    <i class="fa-solid fa-desktop mr-1.5"></i>
                                    PNG download needs a desktop browser. You can still preview here and create the listing — your primary screenshot is used as the store cover on mobile.
                                </p>
                                <p x-show="downloadError" x-text="downloadError" class="mb-4 text-sm text-red-400"></p>
                                <p x-show="downloading && downloadStatus" x-text="downloadStatus" class="mb-4 text-sm text-gray-400"></p>

                                <div x-show="loading" class="text-center py-16">
                                    <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-red-600"></div>
                                    <p class="text-gray-400 text-sm mt-3">Building preview...</p>
                                </div>

                                <div x-show="!loading" class="listing-poster-frame" x-ref="posterFrame">
                                <div class="listing-poster-scale-wrap" :style="posterPreviewStyle()">
                                <div id="listingPoster" class="listing-poster" :class="isPremiumLayout ? 'is-premium' : 'is-basic'">
                                    <img class="lp-bg" :src="posterBg" alt="">
                                    <div class="lp-featured" x-show="isPremiumLayout">
                                        <template x-for="(skin, idx) in featuredSkins" :key="'feat-' + idx">
                                            <div class="lp-skin lp-framable" :class="{ 'is-showing-hint': showFrameHint && idx === 0 }">
                                                <div
                                                    class="lp-frame-viewport"
                                                    :data-frame-key="'feat-' + idx"
                                                    @mousedown="startFrameDrag('feat-' + idx, $event)"
                                                    @touchstart.prevent="startFrameDrag('feat-' + idx, $event)"
                                                    @wheel.prevent="zoomFrame('feat-' + idx, $event)"
                                                    @dblclick.prevent="resetFrame('feat-' + idx)"
                                                >
                                                    <img :src="skin.image_url" :alt="skin.name" :style="frameStyle('feat-' + idx)" @@load="fitFrameToCover('feat-' + idx, $event)" @@error="$event.target.src = placeholderSkin" draggable="false">
                                                </div>
                                                <div
                                                    x-show="showFrameHint && idx === 0"
                                                    x-transition.opacity.duration.400ms
                                                    class="lp-move-hint"
                                                    data-html2canvas-ignore="true"
                                                    aria-hidden="true"
                                                >
                                                    <span class="lp-move-hint-pill">
                                                        <i class="fa-solid fa-up-down-left-right"></i>
                                                        Drag to reposition
                                                    </span>
                                                </div>
                                                <div class="lp-skin-tags">
                                                    <span x-show="skin.painted" class="lp-tag-painted">Painted</span>
                                                    <template x-for="tag in (skin.tags || [])" :key="tag.name">
                                                        <img x-show="tag.image_url" :src="tag.image_url" :alt="tag.name" class="lp-tag-img" @@error="$event.target.style.display='none'">
                                                    </template>
                                                    <span
                                                        x-show="!skin.painted && (!skin.tags || !skin.tags.some(tag => tag.image_url)) && skin.rarity"
                                                        class="lp-rarity"
                                                        :class="rarityClass(skin.rarity)"
                                                        x-text="skin.rarity"
                                                    ></span>
                                                </div>
                                                <div class="lp-skin-meta">
                                                    <p class="lp-skin-name" x-text="skin.name"></p>
                                                    <p class="lp-hero-name" x-text="skin.hero"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="lp-primary lp-framable">
                                        <div
                                            class="lp-frame-viewport"
                                            data-frame-key="primary"
                                            @mousedown="startFrameDrag('primary', $event)"
                                            @touchstart.prevent="startFrameDrag('primary', $event)"
                                            @wheel.prevent="zoomFrame('primary', $event)"
                                            @dblclick.prevent="resetFrame('primary')"
                                        >
                                            <img :src="primaryImageUrl" alt="Primary account screenshot" :style="frameStyle('primary')" @@load="fitFrameToCover('primary', $event)" draggable="false">
                                        </div>
                                        <span class="lp-primary-watermark" aria-hidden="true">Wassitmarket</span>
                                        <div class="lp-collection-badge" x-show="collectionTierImageUrl" x-cloak>
                                            <img
                                                class="lp-collection-badge-icon"
                                                :src="collectionTierImageUrl"
                                                :alt="collectionTier || 'Collection badge'"
                                            >
                                        </div>
                                    </div>

                                    <div class="lp-effects">
                                        <p class="lp-effects-title" x-text="isPremiumLayout ? 'BATTLE EFFECTS' : 'EMOTES'"></p>
                                        <div class="lp-effects-grid">
                                            <template x-for="(item, idx) in previewEmotes" :key="'fx-' + idx">
                                                <div class="lp-effect">
                                                    <img :src="item.image_url" :alt="item.name" @@error="$event.target.src = placeholderSkin">
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="lp-stats">
                                        <div class="lp-stat">
                                            <span class="lp-stat-val" x-text="stats.win_rate === '—' ? '—' : stats.win_rate + '%'"></span>
                                            <span class="lp-stat-lbl">WIN RATE</span>
                                        </div>
                                        <div class="lp-stat">
                                            <i class="fa-solid fa-user lp-stat-ico"></i>
                                            <span class="lp-stat-val" x-text="stats.heroes_count"></span>
                                        </div>
                                        <div class="lp-stat">
                                            <span class="lp-stat-val" x-text="stats.level"></span>
                                            <span class="lp-stat-lbl">LEVEL</span>
                                        </div>
                                        <div class="lp-stat">
                                            <i class="fa-solid fa-shirt lp-stat-ico"></i>
                                            <span class="lp-stat-val" x-text="stats.skins_count"></span>
                                        </div>
                                        <div class="lp-stat">
                                            <i class="fa-solid fa-medal lp-stat-ico"></i>
                                            <span class="lp-stat-val" x-text="stats.rank"></span>
                                            <span class="lp-stat-lbl">HIGHEST RANK</span>
                                        </div>
                                    </div>

                                    <div class="lp-recalls">
                                        <p class="lp-recalls-title">RECALLS</p>
                                        <div class="lp-recalls-row">
                                            <template x-for="(recall, idx) in previewRecalls" :key="'rc-' + idx">
                                                <div class="lp-recall">
                                                    <img :src="recall.image_url" :alt="recall.name" @@error="$event.target.src = placeholderSkin">
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="lp-gallery" :style="galleryStyle()">
                                        <template x-for="(skin, idx) in bottomSkins" :key="'bot-' + idx">
                                            <div class="lp-skin lp-framable" :class="gallerySkinClass()" :style="gallerySkinStyle(idx)">
                                                <div
                                                    class="lp-frame-viewport"
                                                    :data-frame-key="'bot-' + idx"
                                                    @mousedown="startFrameDrag('bot-' + idx, $event)"
                                                    @touchstart.prevent="startFrameDrag('bot-' + idx, $event)"
                                                    @wheel.prevent="zoomFrame('bot-' + idx, $event)"
                                                    @dblclick.prevent="resetFrame('bot-' + idx)"
                                                >
                                                    <img :src="skin.image_url" :alt="skin.name" :style="frameStyle('bot-' + idx)" @@load="fitFrameToCover('bot-' + idx, $event)" @@error="$event.target.src = placeholderSkin" draggable="false">
                                                </div>
                                                <div class="lp-skin-tags">
                                                    <span x-show="skin.painted" class="lp-tag-painted">Painted</span>
                                                    <template x-for="tag in (skin.tags || [])" :key="tag.name">
                                                        <img x-show="tag.image_url" :src="tag.image_url" :alt="tag.name" class="lp-tag-img" @@error="$event.target.style.display='none'">
                                                    </template>
                                                    <span
                                                        x-show="!skin.painted && (!skin.tags || !skin.tags.some(tag => tag.image_url)) && skin.rarity"
                                                        class="lp-rarity"
                                                        :class="rarityClass(skin.rarity)"
                                                        x-text="skin.rarity"
                                                    ></span>
                                                </div>
                                                <div class="lp-skin-meta">
                                                    <p class="lp-skin-name" x-text="skin.name"></p>
                                                    <p class="lp-hero-name" x-text="skin.hero"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="lp-price-slot">
                                        <span class="lp-price-value" x-text="formattedPrice"></span>
                                    </div>
                                </div>
                                </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4 pt-8 mt-8 border-t" style="border-color: #2d2c31;" x-show="isMLBB || currentStep === 1" x-cloak>
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
                                    <a href="{{ route('account.listed-accounts') }}" class="py-2.5 px-5 text-sm rounded-md text-gray-400 hover:text-white">Cancel</a>

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
                                        x-show="currentStep === steps.length"
                                        :disabled="submitting"
                                        class="inline-flex items-center py-2.5 px-8 text-sm rounded-md bg-red-600 hover:bg-red-700 text-white font-medium disabled:opacity-50"
                                    >
                                        <span x-show="!submitting">Create Listing</span>
                                        <span x-show="submitting" x-cloak><i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving listing...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        use App\Support\CollectionTierHelper;
        use App\Support\ListingPosterHelper;
        $listingCollectionBadges = CollectionTierHelper::badgeMap();
        $listingDummyCollectionBadge = $listingCollectionBadges['World Collector'] ?? '';
        $listingPremiumPoster = ListingPosterHelper::userHasPremiumLayout(auth()->user());
        $posterPricePremium = config('listing_poster.price.premium');
        $posterPriceBasic = config('listing_poster.price.basic');
    @endphp

    @push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@700;800;900&display=swap');
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
        .wizard-input--prefix {
            padding-left: 2.75rem;
        }
        .wizard-input--icon {
            padding-left: 2.5rem;
        }
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

        .listing-poster-frame {
            width: 100%;
            max-width: 100%;
            display: flex;
            justify-content: center;
            overflow: hidden;
        }
        .listing-poster-scale-wrap {
            width: 681px;
            height: 1024px;
            transform-origin: top center;
            will-change: transform;
            flex-shrink: 0;
        }
        .listing-poster,
        .listing-poster * {
            box-sizing: border-box;
        }
        .listing-poster {
            position: relative;
            width: 681px;
            height: 1024px;
            margin: 0 auto;
            overflow: hidden;
            font-family: Inter, ui-sans-serif, system-ui, "Segoe UI", sans-serif;
            background-color: #c80000;
        }
        .lp-bg {
            position: absolute;
            inset: 0;
            width: 681px;
            height: 1024px;
            object-fit: fill;
            z-index: 0;
            pointer-events: none;
        }
        .listing-poster > :not(.lp-bg) {
            z-index: 1;
        }
        .lp-featured {
            position: absolute;
            left: 14px;
            top: 200px;
            width: 248px;
            height: 286px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .lp-primary {
            position: absolute;
            left: 272px;
            top: 200px;
            width: 395px;
            height: 286px;
            overflow: hidden;
            border-radius: 12px;
            border: 3px solid #fff;
            background: #111;
            box-shadow: 0 3px 0 rgba(0,0,0,0.25);
        }
        .lp-framable .lp-frame-viewport {
            position: absolute;
            inset: 0;
            overflow: hidden;
            touch-action: none;
        }
        .lp-primary .lp-frame-viewport {
            border-radius: 9px;
        }
        .lp-primary-watermark {
            position: absolute;
            left: 50%;
            top: 52%;
            z-index: 3;
            pointer-events: none;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.22);
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.28);
            white-space: nowrap;
            user-select: none;
            text-transform: none;
        }
        .lp-collection-badge {
            position: absolute;
            right: 8px;
            left: auto;
            top: 8px;
            z-index: 8;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            pointer-events: none;
        }
        .lp-collection-badge-icon {
            width: 54px;
            height: 54px;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.55));
        }
        .lp-framable .lp-frame-viewport {
            cursor: grab;
        }
        .lp-framable .lp-frame-viewport.is-dragging {
            cursor: grabbing;
        }
        .lp-frame-viewport img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
            transform-origin: center center;
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none;
            will-change: transform;
        }
        .lp-rarity,
        .lp-skin-tags,
        .lp-skin-meta {
            z-index: 2;
            pointer-events: none;
        }
        .lp-skin-tags {
            position: absolute;
            top: 5px;
            right: 6px;
            left: auto;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            max-width: 70%;
        }
        .lp-tag-img {
            height: 18px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.65));
        }
        .lp-tag-painted {
            font-size: 6px;
            font-weight: 900;
            padding: 2px 5px;
            background: rgba(190, 24, 93, 0.9);
            color: #fff;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .lp-effects {
            position: absolute;
            left: 14px;
            top: 494px;
            width: 248px;
            height: 128px;
            background: rgba(10,10,10,0.88);
            border: 2px solid #fff;
            border-radius: 10px;
            padding: 6px 7px 7px;
        }
        .lp-effects-title {
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.14em;
            color: #fff;
            text-align: center;
            margin-bottom: 5px;
        }
        .lp-effects-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 4px;
            height: calc(100% - 20px);
        }
        .lp-effect {
            background: #0f0f0f;
            border-radius: 6px;
            overflow: hidden;
        }
        .lp-effect img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .lp-stats {
            position: absolute;
            left: 272px;
            top: 494px;
            width: 395px;
            height: 64px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            align-items: center;
            background: rgba(18,18,18,0.92);
            border: 2px solid #fff;
            border-radius: 10px;
            color: #fff;
            padding: 4px;
        }
        .lp-stat {
            text-align: center;
            border-right: 1px solid #3f3f46;
            padding: 0 2px;
        }
        .lp-stat:last-child { border-right: 0; }
        .lp-stat-val {
            display: block;
            font-weight: 900;
            font-size: 15px;
            line-height: 1.1;
        }
        .lp-stat-val-sm { font-size: 8px; letter-spacing: 0.04em; }
        .lp-stat-lbl {
            display: block;
            font-size: 7px;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #d4d4d8;
            margin-top: 2px;
        }
        .lp-stat-ico {
            display: block;
            font-size: 13px;
            color: #e5e7eb;
            margin: 0 auto 2px;
        }
        .lp-recalls {
            position: absolute;
            left: 272px;
            top: 564px;
            width: 395px;
            height: 70px;
            z-index: 4;
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
        }
        .lp-recalls-title {
            display: none;
        }
        .lp-recalls-row {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 4px;
            height: 100%;
        }
        .lp-recall {
            flex: 1 1 0;
            min-width: 0;
            min-height: 0;
            height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border-radius: 0;
            overflow: hidden;
        }
        .lp-recall img {
            width: auto;
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            display: block;
            transform: none;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));
        }
        .lp-gallery {
            position: absolute;
            left: 14px;
            top: 638px;
            width: 653px;
            height: 182px;
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
        }
        .lp-skin {
            position: relative;
            background: #111;
            border-radius: 8px;
            overflow: hidden;
            min-height: 0;
            border: 2px solid #fff;
        }
        .lp-skin.is-showing-hint {
            z-index: 3;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.55), 0 0 16px rgba(251, 191, 36, 0.28);
        }
        .lp-skin.is-showing-hint .lp-frame-viewport img {
            animation: lpHintPan 2.55s cubic-bezier(0.45, 0.05, 0.2, 1) 0.18s 1 both;
        }
        .lp-move-hint {
            position: absolute;
            left: 50%;
            top: 44%;
            z-index: 6;
            transform: translate(-50%, -50%);
            pointer-events: none;
            width: max-content;
            max-width: 94%;
        }
        .lp-move-hint-pill {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 999px;
            background: rgba(8, 14, 28, 0.78);
            border: 1px solid rgba(255, 255, 255, 0.38);
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.03em;
            line-height: 1;
            white-space: nowrap;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
            animation: lpHintFloat 2.55s ease-in-out 1 both;
        }
        .lp-move-hint-pill i {
            font-size: 8px;
            color: #fde68a;
        }
        .lp-move-hint-pill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(115deg, transparent 18%, rgba(255, 255, 255, 0.38) 48%, transparent 72%);
            transform: translateX(-130%);
            animation: lpHintShine 1.45s ease 0.25s 2;
        }
        @keyframes lpHintPan {
            0% { translate: 0 0; }
            30% { translate: -13px 0; }
            68% { translate: 13px 0; }
            100% { translate: 0 0; }
        }
        @keyframes lpHintFloat {
            0% { opacity: 0; transform: translateY(4px) scale(0.96); }
            14% { opacity: 1; transform: translateY(0) scale(1); }
            82% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-2px) scale(0.98); }
        }
        @keyframes lpHintShine {
            to { transform: translateX(130%); }
        }
        .lp-rarity {
            position: static;
            transform: none;
            font-size: 7px;
            font-weight: 900;
            padding: 2px 6px;
            background: #111;
            color: #fff;
            border: 1px solid #fbbf24;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        .lp-rarity.is-prime,
        .lp-rarity.is-legend { border-color: #fbbf24; color: #fde68a; }
        .lp-rarity.is-collector { border-color: #c084fc; color: #e9d5ff; }
        .lp-rarity.is-special,
        .lp-rarity.is-star { border-color: #67e8f9; color: #a5f3fc; }
        .lp-rarity.is-elite { border-color: #93c5fd; color: #dbeafe; }
        .lp-skin-meta {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 4px 5px 5px;
            background: rgba(8, 18, 40, 0.92);
        }
        .lp-skin-name {
            font-size: 9px;
            font-weight: 800;
            color: #5eead4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.15;
        }
        .lp-hero-name {
            font-size: 8px;
            font-weight: 700;
            color: #93c5fd;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .lp-price-slot {
            position: absolute;
            left: {{ $posterPricePremium['left'] }}px;
            top: {{ $posterPricePremium['top'] }}px;
            width: {{ $posterPricePremium['width'] }}px;
            height: {{ $posterPricePremium['height'] }}px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
            pointer-events: none;
            z-index: 10;
            overflow: visible;
        }
        .lp-price-value {
            display: inline-block;
            font-family: "Bebas Neue", "Montserrat", Impact, sans-serif;
            font-size: {{ $posterPricePremium['font_size'] }}px;
            font-weight: 400;
            letter-spacing: 0.04em;
            line-height: 1;
            transform: rotate({{ $posterPricePremium['rotate'] }}deg) translate({{ $posterPricePremium['translate_x'] }}px, {{ $posterPricePremium['translate_y'] }}px);
            transform-origin: center center;
            background: linear-gradient(180deg, #ff8080 0%, #ef4444 28%, #dc2626 62%, #991b1b 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: #dc2626;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 1px 0 rgba(255, 255, 255, 0.95))
                drop-shadow(0 2px 0 rgba(153, 27, 27, 0.18))
                drop-shadow(0 4px 8px rgba(127, 29, 29, 0.28));
        }
        @supports not ((-webkit-background-clip: text) or (background-clip: text)) {
            .lp-price-value {
                color: #dc2626;
                -webkit-text-fill-color: #dc2626;
                text-shadow:
                    0 1px 0 rgba(255, 255, 255, 0.95),
                    0 2px 0 rgba(153, 27, 27, 0.18),
                    0 4px 8px rgba(127, 29, 29, 0.28);
            }
        }

        .listing-poster.is-basic .lp-featured {
            display: none;
        }
        .listing-poster.is-basic .lp-collection-badge {
            right: 10px;
            top: 10px;
            width: 64px;
            height: 64px;
        }
        .listing-poster.is-basic .lp-collection-badge-icon {
            width: 60px;
            height: 60px;
        }
        .listing-poster.is-basic .lp-effects {
            left: 10px;
            top: 10px;
            width: 248px;
            height: 128px;
        }
        .listing-poster.is-basic .lp-recalls {
            left: 10px;
            top: 144px;
            width: 248px;
            height: 128px;
            z-index: 4;
            background: rgba(10,10,10,0.88);
            border: 2px solid #fff;
            border-radius: 10px;
            padding: 6px 7px 7px;
        }
        .listing-poster.is-basic .lp-recalls-title {
            display: block;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.14em;
            color: #fff;
            text-align: center;
            margin-bottom: 5px;
        }
        .listing-poster.is-basic .lp-recalls-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 4px;
            height: calc(100% - 20px);
            min-height: 0;
        }
        .listing-poster.is-basic .lp-recall {
            min-width: 0;
            min-height: 0;
            height: 100%;
            background: #0f0f0f;
            border-radius: 6px;
            overflow: hidden;
        }
        .listing-poster.is-basic .lp-recall img {
            width: 100%;
            height: 100%;
            max-width: none;
            object-fit: contain;
            filter: none;
        }
        .listing-poster.is-basic .lp-primary {
            left: 266px;
            top: 10px;
            width: 405px;
            height: 262px;
        }
        .listing-poster.is-basic .lp-stats {
            left: 10px;
            top: 280px;
            width: 661px;
            height: 54px;
            overflow: hidden;
            padding: 4px 6px;
        }
        .listing-poster.is-basic .lp-stat {
            min-width: 0;
            overflow: hidden;
            padding: 0 3px;
        }
        .listing-poster.is-basic .lp-stat-val {
            font-size: 13px;
        }
        .listing-poster.is-basic .lp-stat-val-sm {
            font-size: 7px;
        }
        .listing-poster.is-basic .lp-stat-lbl {
            font-size: 6px;
            letter-spacing: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .listing-poster.is-basic .lp-stat-ico {
            font-size: 11px;
            margin-bottom: 1px;
        }
        .listing-poster.is-basic .lp-gallery {
            left: 10px;
            top: 342px;
            width: 661px;
            height: 568px;
            display: flex;
            flex-wrap: wrap;
            align-content: stretch;
            gap: 4px;
        }
        .listing-poster.is-basic .lp-skin.is-large-tile .lp-skin-name { font-size: 11px; }
        .listing-poster.is-basic .lp-skin.is-large-tile .lp-hero-name { font-size: 9px; }
        .listing-poster.is-basic .lp-skin.is-large-tile .lp-tag-img { height: 16px; }
        .listing-poster.is-basic .lp-skin.is-medium-tile .lp-skin-name { font-size: 8px; }
        .listing-poster.is-basic .lp-skin.is-medium-tile .lp-hero-name { font-size: 7px; }
        .listing-poster.is-basic .lp-skin {
            border-width: 1.5px;
            border-radius: 6px;
        }
        .listing-poster.is-basic .lp-skin-meta {
            padding: 3px 3px 3px;
        }
        .listing-poster.is-basic .lp-skin-name {
            font-size: 7px;
        }
        .listing-poster.is-basic .lp-hero-name {
            font-size: 6px;
        }
        .listing-poster.is-basic .lp-tag-img {
            height: 12px;
        }
        .listing-poster.is-basic .lp-price-slot {
            left: {{ $posterPriceBasic['left'] }}px;
            top: {{ $posterPriceBasic['top'] }}px;
            width: {{ $posterPriceBasic['width'] }}px;
            height: {{ $posterPriceBasic['height'] }}px;
            overflow: hidden;
        }
        .listing-poster.is-basic .lp-price-value {
            font-size: {{ $posterPriceBasic['font_size'] }}px;
            letter-spacing: 0.03em;
            line-height: 1;
            transform: rotate({{ $posterPriceBasic['rotate'] }}deg) translate({{ $posterPriceBasic['translate_x'] }}px, {{ $posterPriceBasic['translate_y'] }}px);
        }

    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        const CREATE_DRAFT_KEY = 'wasit.createAccount.draft.v1';
        const CREATE_IMAGES_DB = 'wasitCreateAccountImages';
        const POSTER_WIDTH = 681;
        const POSTER_HEIGHT = 1024;
        const POSTER_EXPORT_WIDTH = 1080;
        const POSTER_EXPORT_SCALE = POSTER_EXPORT_WIDTH / POSTER_WIDTH;

        function readCreateDraft() {
            try {
                return JSON.parse(sessionStorage.getItem(CREATE_DRAFT_KEY) || 'null');
            } catch {
                return null;
            }
        }

        function writeCreateDraft(data) {
            try {
                sessionStorage.setItem(CREATE_DRAFT_KEY, JSON.stringify(data));
            } catch (error) {
                console.warn('Could not save listing draft', error);
            }
        }

        function clearCreateAccountDraft() {
            try {
                sessionStorage.removeItem(CREATE_DRAFT_KEY);
            } catch {
                // ignore
            }
            try {
                indexedDB.deleteDatabase(CREATE_IMAGES_DB);
            } catch {
                // ignore
            }
        }

        function openCreateImagesDb() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open(CREATE_IMAGES_DB, 1);
                request.onupgradeneeded = () => {
                    const db = request.result;
                    if (!db.objectStoreNames.contains('images')) {
                        db.createObjectStore('images');
                    }
                };
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            });
        }

        async function saveCreateDraftImages(files) {
            const db = await openCreateImagesDb();
            const payload = await Promise.all((files || []).map(async (file) => ({
                name: file.name,
                type: file.type,
                lastModified: file.lastModified,
                buffer: await file.arrayBuffer(),
            })));
            await new Promise((resolve, reject) => {
                const tx = db.transaction('images', 'readwrite');
                tx.objectStore('images').put(payload, 'files');
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error);
            });
            db.close();
        }

        async function loadCreateDraftImages() {
            try {
                const db = await openCreateImagesDb();
                const payload = await new Promise((resolve, reject) => {
                    const tx = db.transaction('images', 'readonly');
                    const request = tx.objectStore('images').get('files');
                    request.onsuccess = () => resolve(request.result || []);
                    request.onerror = () => reject(request.error);
                });
                db.close();
                return (payload || []).map((row) => new File([row.buffer], row.name, {
                    type: row.type,
                    lastModified: row.lastModified,
                }));
            } catch {
                return [];
            }
        }

        function notifyCreateDraftChanged() {
            window.dispatchEvent(new CustomEvent('wasit-create-draft-changed'));
        }
        function createAccountWizard({ mlbbId, initialStep, initialGameId }) {
            return {
                currentStep: initialStep || 1,
                selectedGameId: initialGameId || '',
                mlbbId,
                stepError: '',
                submitting: false,
                hasServerOld: @json($errors->any()),
                _draftTimer: null,
                _draftRestoring: false,
                steps: [
                    { id: 1, label: 'Game' },
                    { id: 2, label: 'Details' },
                    { id: 3, label: 'Stats' },
                    { id: 4, label: 'Skins' },
                    { id: 5, label: 'Recalls' },
                    { id: 6, label: 'Emotes' },
                    { id: 7, label: 'Photos' },
                    { id: 8, label: 'Preview' },
                ],
                get isMLBB() {
                    return this.selectedGameId && this.mlbbId && String(this.selectedGameId) === String(this.mlbbId);
                },
                get isOtherGame() {
                    return this.selectedGameId && this.mlbbId && String(this.selectedGameId) !== String(this.mlbbId);
                },
                init() {
                    this._draftRestoring = true;
                    if (!this.hasServerOld) {
                        this.restoreDraft();
                    }
                    this.bindDraftPersistence();
                    this._draftRestoring = false;
                },
                bindDraftPersistence() {
                    const form = document.getElementById('createAccountForm');
                    const save = () => this.queueDraftSave();
                    const flush = () => {
                        clearTimeout(this._draftTimer);
                        this.saveDraft();
                    };
                    form?.addEventListener('input', save);
                    form?.addEventListener('change', save);
                    this.$watch('currentStep', save);
                    this.$watch('selectedGameId', save);
                    window.addEventListener('wasit-create-draft-changed', save);
                    window.addEventListener('pagehide', flush);
                    window.addEventListener('beforeunload', flush);
                    document.addEventListener('click', (event) => {
                        const link = event.target.closest('a[href]');
                        if (!link || event.defaultPrevented || event.button !== 0) return;
                        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                        try {
                            const url = new URL(link.href, window.location.href);
                            if (url.origin !== window.location.origin) return;
                            if (!url.pathname.includes('/listed-accounts/create')) {
                                clearCreateAccountDraft();
                            }
                        } catch {
                            // ignore
                        }
                    });
                },
                queueDraftSave() {
                    if (this._draftRestoring) return;
                    clearTimeout(this._draftTimer);
                    this._draftTimer = setTimeout(() => this.saveDraft(), 250);
                },
                compactDraftSkins(skins) {
                    return (skins || []).map(({ key, id, hero, name }) => ({
                        key,
                        id,
                        hero,
                        name,
                    }));
                },
                saveDraft() {
                    if (this._draftRestoring) return;
                    const form = document.getElementById('createAccountForm');
                    if (!form) return;
                    const fields = {};
                    Array.from(form.elements).forEach((el) => {
                        if (!el.name || el.type === 'file' || el.type === 'submit' || el.type === 'button') return;
                        fields[el.name] = el.value;
                    });
                    const skinsData = this.safePicker('accountSkinsPicker');
                    const recallsData = this.safePicker('accountRecallsPicker');
                    const emotesData = this.safePicker('accountEmotesPicker');
                    const preview = this.getListingPreview();
                    const existingDraft = readCreateDraft();

                    let selectedSkins = existingDraft?.selectedSkins || [];
                    if (skinsData?.initialized) {
                        selectedSkins = skinsData.selectedSkins || [];
                    } else if (skinsData?.selectedSkins?.length) {
                        selectedSkins = skinsData.selectedSkins;
                    }

                    let selectedRecalls = existingDraft?.selectedRecalls || [];
                    let selectedRecallKeys = existingDraft?.selectedRecallKeys || [];
                    if (recallsData?.selectedItems?.length) {
                        selectedRecalls = recallsData.selectedItems;
                        selectedRecallKeys = recallsData.selected || [];
                    }

                    let selectedEmotes = existingDraft?.selectedEmotes || [];
                    let selectedEmoteKeys = existingDraft?.selectedEmoteKeys || [];
                    if (emotesData?.selectedItems?.length) {
                        selectedEmotes = emotesData.selectedItems;
                        selectedEmoteKeys = emotesData.selected || [];
                    }

                    writeCreateDraft({
                        step: this.currentStep,
                        selectedGameId: this.selectedGameId,
                        fields,
                        selectedSkins: this.compactDraftSkins(selectedSkins),
                        selectedSkinKeys: selectedSkins.map((item) => item.key).filter(Boolean),
                        selectedRecalls,
                        selectedRecallKeys,
                        selectedEmotes,
                        selectedEmoteKeys,
                        imageFrames: preview?.imageFrames || existingDraft?.imageFrames || {},
                    });
                    const imagesData = this.getImagesPicker();
                    if (imagesData?.initialized) {
                        saveCreateDraftImages(imagesData.selectedFiles).catch(() => {});
                    }
                },
                restoreDraft() {
                    const draft = readCreateDraft();
                    if (!draft) return;
                    if (draft.selectedGameId) {
                        this.selectedGameId = String(draft.selectedGameId);
                    }
                    if (draft.step >= 1 && draft.step <= this.steps.length) {
                        this.currentStep = draft.step;
                    }
                    const form = document.getElementById('createAccountForm');
                    Object.entries(draft.fields || {}).forEach(([name, value]) => {
                        const el = form?.elements?.[name];
                        if (!el || el.type === 'file' || el.type === 'hidden' && name === '_token') return;
                        el.value = value ?? '';
                    });
                },
                getImagesPicker() {
                    const el = this.$refs.imagesPicker
                        || document.getElementById('accountImagesPicker');
                    if (!el || typeof Alpine === 'undefined') return null;
                    try {
                        return Alpine.$data(el) ?? null;
                    } catch {
                        return null;
                    }
                },
                getListingPreview() {
                    const el = document.getElementById('listingPoster');
                    if (!el || typeof Alpine === 'undefined') return null;
                    try {
                        return Alpine.$data(el) ?? null;
                    } catch {
                        return null;
                    }
                },
                safePicker(id) {
                    const el = document.getElementById(id);
                    if (!el || typeof Alpine === 'undefined') return null;
                    try {
                        return Alpine.$data(el) ?? null;
                    } catch {
                        return null;
                    }
                },
                getImageCount() {
                    const picker = this.getImagesPicker();
                    if (picker?.selectedFiles?.length) {
                        return picker.selectedFiles.length;
                    }
                    const native = document.getElementById('images');
                    return native?.files?.length ?? 0;
                },
                selectGame(id) {
                    this.selectedGameId = String(id);
                    this.stepError = '';
                },
                goToStep(step) {
                    if (step < this.currentStep) {
                        this.currentStep = step;
                        this.stepError = '';
                        return;
                    }
                    for (let s = this.currentStep; s < step; s++) {
                        if (!this.validateStep(s)) return;
                    }
                    this.currentStep = step;
                    this.stepError = '';
                },
                validateStep(step) {
                    this.stepError = '';
                    if (step === 1) {
                        if (!this.selectedGameId) {
                            this.stepError = 'Please select a game.';
                            return false;
                        }
                        if (!this.isMLBB) {
                            this.stepError = 'Only Mobile Legends is supported right now.';
                            return false;
                        }
                    }
                    if (step === 2) {
                        const title = document.getElementById('title')?.value.trim();
                        const description = document.getElementById('description')?.value.trim();
                        const price = document.getElementById('price_dzd')?.value.trim();
                        if (!title || !description || !price) {
                            this.stepError = 'Please fill in title, description, and price.';
                            return false;
                        }
                    }
                    if (step === 7) {
                        if (this.getImageCount() < 1) {
                            this.stepError = 'Please upload at least one image.';
                            return false;
                        }
                    }
                    return true;
                },
                nextStep() {
                    if (!this.validateStep(this.currentStep)) return;
                    if (this.currentStep < this.steps.length) {
                        this.currentStep++;
                        this.stepError = '';
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        this.stepError = '';
                    }
                },
                async handleSubmit(event) {
                    if (this.currentStep !== this.steps.length) {
                        event.preventDefault();
                        return;
                    }
                    if (!this.validateStep(2) || !this.validateStep(7)) {
                        event.preventDefault();
                        return;
                    }

                    const pickerData = this.getImagesPicker();
                    if (!pickerData?.selectedFiles?.length) {
                        return;
                    }

                    event.preventDefault();
                    this.submitting = true;
                    this.stepError = '';

                    try {
                        const form = event.target;
                        const formData = new FormData(form);
                        formData.delete('images[]');
                        formData.delete('listing_cover');

                        const preview = this.getListingPreview();
                        if (preview && typeof preview.exportPosterFile === 'function') {
                            try {
                                const cover = await preview.exportPosterFile();
                                if (cover) {
                                    formData.append('listing_cover', cover, 'listing-poster.png');
                                }
                            } catch (error) {
                                console.error(error);
                            }
                        }

                        pickerData.selectedFiles.forEach(file => formData.append('images[]', file));

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            clearCreateAccountDraft();
                            window.location.href = @json(route('account.listed-accounts'));
                            return;
                        }
                        this.submitting = false;
                        this.stepError = data.message || 'Failed to create listing.';
                        if (data.errors) console.error(data.errors);
                    } catch {
                        this.submitting = false;
                        this.stepError = 'Failed to create listing. Please try again.';
                    }
                },
            };
        }

        function highlightedSkinsPicker() {
            return {
                heroes: [],
                searchQuery: '',
                selectedHero: null,
                heroSkins: [],
                skinsCache: {},
                skinIdMap: {},
                skinById: {},
                selectedSkins: [],
                pendingSkinIds: [],
                initialized: false,
                loadingList: false,
                loadingDetail: false,
                listError: '',
                detailError: '',
                detailRequestId: 0,
                oldSelectedIds: @json($oldHighlightedSkinIds),
                placeholderAvatar: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96"><rect width="96" height="96" fill="%231b1a1e"/><text x="48" y="54" text-anchor="middle" fill="%2371717a" font-size="14">MLBB</text></svg>',
                init() {
                    this.restoreSelections();
                    this.updateHiddenInputs();
                    this.$watch('currentStep', (step) => {
                        if (step === 4) {
                            if (!this.selectedSkins.length) {
                                this.restoreSelections();
                            }
                            this.enrichSelectedSkins();
                            this.updateHiddenInputs();
                        }
                    });
                    Promise.all([this.loadHeroes(), this.loadSkinIdMap()])
                        .then(() => {
                            this.enrichSelectedSkins();
                            this.updateHiddenInputs();
                        })
                        .finally(() => {
                            this.initialized = true;
                        });
                },
                restoreSelections() {
                    const draft = readCreateDraft();
                    const draftSkins = draft?.selectedSkins?.length
                        ? draft.selectedSkins
                        : null;
                    if (draftSkins) {
                        this.selectedSkins = draftSkins.map((item) => ({ ...item }));
                        return;
                    }

                    const raw = String(document.getElementById('highlighted_skins_input')?.value || '').trim();
                    if (/^\d+(\s*,\s*\d+)*$/.test(raw)) {
                        this.pendingSkinIds = raw.split(',').map((value) => Number(value.trim())).filter((value) => value > 0);
                        return;
                    }

                    if ((this.oldSelectedIds || []).length) {
                        this.pendingSkinIds = [...this.oldSelectedIds];
                    }
                },
                get filteredHeroes() {
                    const needle = this.searchQuery.trim().toLowerCase();
                    if (!needle) return this.heroes;
                    return this.heroes.filter((item) => item.name.toLowerCase().includes(needle));
                },
                async loadHeroes() {
                    this.loadingList = true;
                    this.listError = '';
                    try {
                        const response = await fetch('/mlbb/playground/heroes');
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Failed to load heroes.');
                        this.heroes = payload.heroes || [];
                    } catch (error) {
                        this.listError = error.message;
                        this.heroes = [];
                    } finally {
                        this.loadingList = false;
                    }
                },
                async loadSkinIdMap() {
                    try {
                        const response = await fetch('/api/mlbb/skins');
                        if (!response.ok) return;
                        const data = await response.json();
                        const map = {};
                        const byId = {};
                        for (const category of (data.categories || [])) {
                            for (const hero of (category.heroes || [])) {
                                const heroKey = String(hero.hero || '').toLowerCase().trim();
                                map[heroKey] = map[heroKey] || {};
                                for (const skin of (hero.skins_with_ids || [])) {
                                    const skinKey = String(skin.name || '').toLowerCase().trim();
                                    map[heroKey][skinKey] = Number(skin.id);
                                    byId[Number(skin.id)] = { id: Number(skin.id), hero: hero.hero, name: skin.name };
                                }
                            }
                        }
                        this.skinIdMap = map;
                        this.skinById = byId;
                    } catch (e) {
                        console.error(e);
                    }
                },
                hydrateOldSelections() {
                    this.selectedSkins = (this.pendingSkinIds?.length ? this.pendingSkinIds : (this.oldSelectedIds || []))
                        .map((id) => {
                            const info = this.skinById[Number(id)];
                            if (!info) return null;
                            return {
                                key: this.skinKey(info.hero, info.name),
                                id: info.id,
                                hero: info.hero,
                                name: info.name,
                            };
                        })
                        .filter(Boolean);
                    this.pendingSkinIds = [];
                },
                enrichSelectedSkins() {
                    if (this.pendingSkinIds.length && Object.keys(this.skinById).length) {
                        this.hydrateOldSelections();
                    }

                    this.selectedSkins = (this.selectedSkins || []).map((item) => ({
                        ...item,
                        id: item.id ?? this.lookupSkinId(item.hero, item.name),
                    }));

                    for (const item of this.selectedSkins) {
                        if (item.image_url || !item.hero || !item.name) {
                            continue;
                        }
                        const cached = this.skinsCache[item.hero];
                        if (!cached) {
                            continue;
                        }
                        const match = cached.find((skin) => this.skinKey(item.hero, skin.name) === item.key);
                        if (match) {
                            item.image_url = match.image_url || match.thumbnail_url || item.image_url;
                            item.rarity = item.rarity || match.rarity || 'Skin';
                            item.tags = item.tags?.length ? item.tags : (match.tags || []);
                        }
                    }
                },
                skinKey(heroName, skinName) {
                    return `${String(heroName).toLowerCase().trim()}||${String(skinName).toLowerCase().trim()}`;
                },
                visibleSkins(skins) {
                    return (skins || []).slice(2);
                },
                lookupSkinId(heroName, skinName) {
                    const heroKey = String(heroName).toLowerCase().trim();
                    const skinKey = String(skinName).toLowerCase().trim();
                    return this.skinIdMap[heroKey]?.[skinKey] ?? null;
                },
                skinImage(item) {
                    const url = item?.image_url || item?.thumbnail_url || '';
                    if (!url) return this.placeholderAvatar;
                    if (url.startsWith('blob:') || url.startsWith('data:')) return url;
                    if (url.startsWith('/')) return url;
                    try {
                        const parsed = new URL(url, window.location.origin);
                        if (parsed.pathname.startsWith('/storage/')) {
                            return parsed.pathname + parsed.search;
                        }
                        if (parsed.origin === window.location.origin) {
                            return parsed.pathname + parsed.search;
                        }
                    } catch (_) {}
                    if (url.startsWith(window.location.origin)) {
                        return url.slice(window.location.origin.length) || url;
                    }
                    return @json(route('mlbb.image-proxy')) + '?url=' + encodeURIComponent(url);
                },
                async selectHero(heroItem) {
                    this.selectedHero = heroItem;
                    this.detailError = '';
                    const cached = this.skinsCache[heroItem.name];
                    if (cached) {
                        this.heroSkins = cached;
                        this.enrichSelectedSkins();
                        this.updateHiddenInputs();
                        return;
                    }
                    const requestId = ++this.detailRequestId;
                    this.loadingDetail = true;
                    this.heroSkins = [];
                    try {
                        const response = await fetch(`/mlbb/playground/heroes/${encodeURIComponent(heroItem.name)}`);
                        if (requestId !== this.detailRequestId) return;
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Failed to load skins.');
                        const skins = this.visibleSkins(payload.hero?.skins || []);
                        this.skinsCache[heroItem.name] = skins;
                        this.heroSkins = skins;
                        this.enrichSelectedSkins();
                        this.updateHiddenInputs();
                    } catch (error) {
                        if (requestId === this.detailRequestId) {
                            this.detailError = error.message;
                            this.heroSkins = [];
                        }
                    } finally {
                        if (requestId === this.detailRequestId) {
                            this.loadingDetail = false;
                        }
                    }
                },
                clearHero() {
                    this.selectedHero = null;
                    this.heroSkins = [];
                    this.detailError = '';
                },
                isSkinSelected(skin) {
                    if (!this.selectedHero) return false;
                    const key = this.skinKey(this.selectedHero.name, skin.name);
                    return this.selectedSkins.some((item) => item.key === key);
                },
                heroHasSelection(heroName) {
                    const prefix = `${String(heroName).toLowerCase().trim()}||`;
                    return this.selectedSkins.some((item) => item.key.startsWith(prefix));
                },
                toggleSkin(skin) {
                    if (!this.selectedHero) return;
                    const key = this.skinKey(this.selectedHero.name, skin.name);
                    const idx = this.selectedSkins.findIndex((item) => item.key === key);
                    if (idx > -1) {
                        this.selectedSkins.splice(idx, 1);
                    } else {
                        this.selectedSkins.push({
                            key,
                            id: this.lookupSkinId(this.selectedHero.name, skin.name),
                            hero: this.selectedHero.name,
                            name: skin.name,
                            image_url: skin.image_url || skin.thumbnail_url,
                            rarity: skin.rarity || 'Skin',
                            tags: skin.tags || [],
                        });
                    }
                    this.updateHiddenInputs();
                    notifyCreateDraftChanged();
                },
                removeSelected(key) {
                    this.selectedSkins = this.selectedSkins.filter((item) => item.key !== key);
                    this.updateHiddenInputs();
                    notifyCreateDraftChanged();
                },
                getSelectedCount() {
                    return this.selectedSkins.length;
                },
                updateHiddenInputs() {
                    const input = document.getElementById('highlighted_skins_input');
                    if (!input) return;
                    input.value = this.selectedSkins
                        .map((item) => item.id)
                        .filter((id) => id !== null && id !== undefined)
                        .join(',');
                },
                rarityBadgeClass(rarity) {
                    const name = String(rarity || '').toLowerCase();
                    if (name.includes('painted')) return 'bg-rose-500/15 text-rose-300 ring-rose-400/40';
                    if (name === 'm7' || name.includes('m7')) return 'bg-red-500/15 text-red-300 ring-red-400/40';
                    if (name.includes('legend')) return 'bg-amber-400/15 text-amber-300 ring-amber-400/40';
                    if (name.includes('collector')) return 'bg-fuchsia-500/15 text-fuchsia-300 ring-fuchsia-400/40';
                    if (name.includes('prime')) return 'bg-orange-500/15 text-orange-300 ring-orange-400/40';
                    if (name.includes('lucky')) return 'bg-yellow-500/15 text-yellow-300 ring-yellow-400/40';
                    if (name.includes('starlight')) return 'bg-cyan-500/15 text-cyan-300 ring-cyan-400/40';
                    if (name.includes('epic') || name.includes('deluxe')) return 'bg-pink-500/15 text-pink-300 ring-pink-400/40';
                    if (name.includes('elite')) return 'bg-blue-500/15 text-blue-300 ring-blue-400/40';
                    if (name.includes('normal') || name === 'basic' || name.includes('common')) return 'bg-zinc-500/15 text-zinc-300 ring-zinc-400/30';
                    if (name.includes('special') || name.includes('exceptional')) return 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/40';
                    if (name.includes('exquisite')) return 'bg-violet-500/15 text-violet-300 ring-violet-400/40';
                    return 'bg-zinc-500/15 text-zinc-300 ring-zinc-400/30';
                },
            };
        }

        function catalogItemPicker({ endpoint, itemsKey, inputId, step }) {
            return {
                endpoint,
                itemsKey,
                inputId,
                step,
                groups: [],
                searchQuery: '',
                selected: [],
                selectedItems: [],
                loading: false,
                loaded: false,
                error: '',
                init() {
                    const draft = readCreateDraft();
                    const draftItems = this.itemsKey === 'recalls'
                        ? draft?.selectedRecalls
                        : this.itemsKey === 'emotes'
                            ? draft?.selectedEmotes
                            : null;
                    const draftKeys = this.itemsKey === 'recalls'
                        ? draft?.selectedRecallKeys
                        : this.itemsKey === 'emotes'
                            ? draft?.selectedEmoteKeys
                            : null;
                    const input = document.getElementById(this.inputId);
                    const raw = String(input?.value || '').trim();
                    if (draftKeys?.length) {
                        this.selected = draftKeys;
                        this.selectedItems = draftItems || [];
                    } else if (/^\d+(\s*,\s*\d+)*$/.test(raw)) {
                        this.selected = raw.split(',').map((value) => Number(value.trim())).filter((value) => value > 0);
                    } else {
                        this.selected = raw.split('|').map((value) => value.trim()).filter(Boolean);
                    }
                    const maybeLoad = () => {
                        if (this.currentStep === this.step && !this.loaded && !this.loading) {
                            this.load();
                        }
                    };
                    maybeLoad();
                    this.$watch('currentStep', maybeLoad);
                    this.updateHiddenInput();
                },
                get filteredGroups() {
                    const needle = this.searchQuery.trim().toLowerCase();
                    return this.groups
                        .map((group) => {
                            const items = (group.items || []).filter((item) =>
                                !needle || String(item.name || '').toLowerCase().includes(needle)
                            );
                            return items.length ? { ...group, items } : null;
                        })
                        .filter(Boolean);
                },
                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const response = await fetch(this.endpoint);
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Failed to load catalog.');
                        const groups = payload.groups || [];
                        this.groups = groups.length
                            ? groups.map((group) => ({
                                group: group.group,
                                items: group[this.itemsKey] || group.items || [],
                            }))
                            : [{ group: 'All', items: payload[this.itemsKey] || [] }];
                        this.loaded = true;
                        this.hydrateSelectedFromNames();
                    } catch (error) {
                        this.error = error.message;
                        this.groups = [];
                    } finally {
                        this.loading = false;
                    }
                },
                itemKey(item) {
                    return item.id != null ? Number(item.id) : item.name;
                },
                findItem(key) {
                    for (const group of this.groups) {
                        for (const item of (group.items || [])) {
                            if (this.itemKey(item) === key || item.name === key) return item;
                        }
                    }
                    return null;
                },
                hydrateSelectedFromNames() {
                    this.selectedItems = [];
                    this.selected = this.selected.map((key) => {
                        const item = this.findItem(key);
                        if (item) this.selectedItems.push(item);
                        return item ? this.itemKey(item) : key;
                    });
                    this.updateHiddenInput();
                },
                isSelected(item) {
                    const key = this.itemKey(item);
                    return this.selected.some((value) => value === key || String(value) === String(key) || value === item.name);
                },
                toggleItem(item) {
                    const key = this.itemKey(item);
                    const idx = this.selected.findIndex((value) => value === key || String(value) === String(key) || value === item.name);
                    if (idx > -1) {
                        this.selected.splice(idx, 1);
                        this.selectedItems = this.selectedItems.filter((row) => this.itemKey(row) !== key);
                    } else {
                        this.selected.push(key);
                        this.selectedItems.push(item);
                    }
                    this.updateHiddenInput();
                    notifyCreateDraftChanged();
                },
                selectedLabel(key) {
                    return this.findItem(key)?.name || key;
                },
                removeSelected(key) {
                    this.selected = this.selected.filter((value) => value !== key && String(value) !== String(key));
                    this.selectedItems = this.selectedItems.filter((row) => this.itemKey(row) !== key && row.name !== key);
                    this.updateHiddenInput();
                    notifyCreateDraftChanged();
                },
                updateHiddenInput() {
                    const input = document.getElementById(this.inputId);
                    if (!input) return;
                    const ids = this.selected.filter((value) => Number.isInteger(Number(value)) && Number(value) > 0 && String(Number(value)) === String(value));
                    input.value = ids.length === this.selected.length
                        ? ids.join(',')
                        : this.selected.join('|');
                },
            };
        }

        function listingPreview() {
            return {
                loading: false,
                building: false,
                downloading: false,
                downloadError: '',
                downloadStatus: '',
                primaryImageUrl: '',
                placeholderSkin: @json(asset('images/mlbb-primary-photo-example.png')),
                featuredSkins: [],
                bottomSkins: [],
                previewEmotes: [],
                previewRecalls: [],
                previewUseDummyData: false,
                isPremiumLayout: @json($listingPremiumPoster),
                posterBg: @json($listingPremiumPoster ? asset('images/listing-poster-bg.png') : asset('images/listing-poster-bg-basic.jpg')),
                gallerySkinCount: @json($listingPremiumPoster ? 6 : 48),
                galleryLayout: { cols: 8, rows: 6, count: 0 },
                imageFrames: {},
                posterPreviewScale: 1,
                _posterResizeHandler: null,
                dragging: null,
                showFrameHint: false,
                frameHintPlayed: false,
                sampleSkinsCache: null,
                imageProxy: @json(route('mlbb.image-proxy')),
                stats: {},
                formattedPrice: '0',
                title: '',
                collectionBadges: @json($listingCollectionBadges),
                collectionTier: '',
                collectionTierImageUrl: '',
                accountCode: '',
                init() {
                    const draft = readCreateDraft();
                    if (draft?.imageFrames && Object.keys(draft.imageFrames).length) {
                        this.imageFrames = { ...draft.imageFrames };
                    }
                    this.initImageFrames();
                    this.featuredSkins = this.isPremiumLayout ? this.padSkins([], 2) : [];
                    this.bottomSkins = this.isPremiumLayout ? this.padSkins([], 6) : [];
                    this.galleryLayout = this.pickGalleryGrid(this.bottomSkins.length);
                    this.previewEmotes = this.padCatalogItems([], 6);
                    this.previewRecalls = this.padCatalogItems([], 6);
                    this.updatePosterPreviewScale();
                    this._posterResizeHandler = () => this.updatePosterPreviewScale();
                    window.addEventListener('resize', this._posterResizeHandler);
                    const wizard = this.wizard();
                    if (!wizard) return;
                    this.$watch(() => wizard.currentStep, (step) => {
                        if (step === 8) {
                            this.buildPreview();
                            this.$nextTick(() => this.updatePosterPreviewScale());
                        }
                    });
                    setTimeout(() => {
                        if (wizard.currentStep === 8) {
                            this.buildPreview();
                            this.$nextTick(() => this.updatePosterPreviewScale());
                        }
                    }, 250);
                },
                posterPreviewStyle() {
                    return {
                        transform: `scale(${this.posterPreviewScale})`,
                        transformOrigin: 'top center',
                    };
                },
                updatePosterPreviewScale() {
                    const frame = this.$refs.posterFrame;
                    if (!frame) return;
                    const available = Math.max(0, frame.clientWidth) || 681;
                    this.posterPreviewScale = Math.min(1, available / 681);
                    const scaledHeight = Math.ceil(1024 * this.posterPreviewScale);
                    frame.style.height = `${scaledHeight}px`;
                    frame.style.minHeight = `${scaledHeight}px`;
                },
                wizard() {
                    const form = document.getElementById('createAccountForm');
                    return form && typeof Alpine !== 'undefined' ? Alpine.$data(form) : null;
                },
                field(id, fallback = '—') {
                    const value = document.getElementById(id)?.value?.trim();
                    return value || fallback;
                },
                formatPrice(raw) {
                    const digits = String(raw || '0').replace(/[^\d]/g, '');
                    if (!digits) return '0';
                    return Number(digits).toLocaleString('fr-DZ');
                },
                collectionTierImageUrlFor(tier) {
                    const value = String(tier || '').trim();
                    if (!value) return '';
                    return this.collectionBadges[value] || '';
                },
                applyCollectionTierFromForm() {
                    const tier = this.field('collection_tier', '');
                    this.collectionTier = tier === '—' ? '' : tier;
                    this.collectionTierImageUrl = this.collectionTierImageUrlFor(this.collectionTier);
                },
                applyDummyCollectionBadge() {
                    this.collectionTier = 'World Collector';
                    this.collectionTierImageUrl = this.collectionBadges['World Collector'] || '';
                },
                randomCode() {
                    return 'ML' + Math.random().toString(36).substring(2, 8).toUpperCase();
                },
                pickerData(refName) {
                    const ids = {
                        imagesPicker: 'accountImagesPicker',
                        skinsPicker: 'accountSkinsPicker',
                        recallsPicker: 'accountRecallsPicker',
                        emotesPicker: 'accountEmotesPicker',
                    };
                    const wizard = this.wizard();
                    const el = wizard?.$refs?.[refName]
                        || (ids[refName] ? document.getElementById(ids[refName]) : null);
                    if (!el || typeof Alpine === 'undefined') return null;
                    try {
                        return Alpine.$data(el) ?? null;
                    } catch {
                        return null;
                    }
                },
                pickGalleryGrid(count) {
                    const n = Math.max(0, Math.min(48, Number(count) || 0));
                    if (n <= 0) {
                        return { cols: 8, rows: 6, count: 0 };
                    }
                    if (n === 1) {
                        return { cols: 1, rows: 1, count: 1 };
                    }

                    const areaW = 661;
                    const areaH = 568;
                    const maxCols = Math.min(8, n);
                    const maxRows = 6;
                    let best = null;

                    for (let cols = 1; cols <= maxCols; cols++) {
                        const rows = Math.ceil(n / cols);
                        if (rows > maxRows) continue;

                        const cellW = (areaW - (cols - 1) * 4) / cols;
                        const cellH = (areaH - (rows - 1) * 4) / rows;
                        const aspect = cellW / Math.max(cellH, 1);
                        const aspectPenalty = Math.abs(Math.log(aspect / 0.78));
                        const emptyPenalty = (cols * rows - n) * 0.4;
                        const sizeBonus = Math.log(Math.max(cellW * cellH, 1)) * 0.15;
                        const score = sizeBonus - aspectPenalty - emptyPenalty;

                        if (!best || score > best.score) {
                            best = { cols, rows, score };
                        }
                    }

                    if (!best) {
                        return { cols: Math.min(8, n), rows: Math.min(6, Math.ceil(n / Math.min(8, n))), count: n };
                    }

                    return { cols: best.cols, rows: best.rows, count: n };
                },
                galleryStyle() {
                    if (this.isPremiumLayout) return {};
                    return {
                        display: 'flex',
                        flexWrap: 'wrap',
                        alignContent: 'stretch',
                    };
                },
                gallerySkinClass() {
                    if (this.isPremiumLayout) return '';
                    const cols = this.galleryLayout?.cols || 8;
                    if (cols <= 3) return 'is-large-tile';
                    if (cols <= 5) return 'is-medium-tile';
                    return '';
                },
                gallerySkinStyle(idx) {
                    if (this.isPremiumLayout) return {};
                    const count = this.galleryLayout?.count || this.bottomSkins.length;
                    const cols = this.galleryLayout?.cols || 1;
                    const rows = this.galleryLayout?.rows || 1;
                    if (!count) return {};
                    const gap = 4;
                    const row = Math.floor(idx / cols);
                    const lastRowCount = count - cols * (rows - 1);
                    const itemsInRow = row === rows - 1 ? lastRowCount : cols;
                    const width = `calc((100% - ${(itemsInRow - 1) * gap}px) / ${itemsInRow})`;
                    const height = `calc((100% - ${(rows - 1) * gap}px) / ${rows})`;
                    return {
                        flex: `0 0 ${width}`,
                        width,
                        height,
                    };
                },
                initImageFrames() {
                    const previous = { ...(this.imageFrames || {}) };
                    const frames = {
                        primary: this.cloneFrameState(previous.primary),
                    };
                    for (let i = 0; i < 2; i++) {
                        frames[`feat-${i}`] = this.cloneFrameState(previous[`feat-${i}`]);
                    }
                    const botCount = Math.max(this.bottomSkins.length, this.isPremiumLayout ? 6 : 48);
                    for (let i = 0; i < botCount; i++) {
                        frames[`bot-${i}`] = this.cloneFrameState(previous[`bot-${i}`]);
                    }
                    this.imageFrames = frames;
                },
                cloneFrameState(previous) {
                    if (previous) {
                        return { ...previous };
                    }
                    return { x: 0, y: 0, scale: 1, coverScale: 1, adjusted: false };
                },
                coverScaleFor(img) {
                    const viewport = img?.parentElement;
                    const vw = viewport?.clientWidth || 0;
                    const vh = viewport?.clientHeight || 0;
                    const iw = img?.naturalWidth || 0;
                    const ih = img?.naturalHeight || 0;
                    if (!vw || !vh || !iw || !ih) return 1;
                    const contain = Math.min(vw / iw, vh / ih);
                    const cover = Math.max(vw / iw, vh / ih);
                    return contain > 0 ? cover / contain : 1;
                },
                fitFrameToCover(key, event) {
                    this.refitFrameImage(key, event?.target);
                },
                refitFrameImage(key, img) {
                    if (!img?.naturalWidth || !this.imageFrames[key]) return;
                    const coverScale = this.coverScaleFor(img);
                    const frame = this.imageFrames[key];
                    this.imageFrames[key] = {
                        ...frame,
                        coverScale,
                        scale: frame.adjusted ? frame.scale : coverScale,
                        x: frame.adjusted ? frame.x : 0,
                        y: frame.adjusted ? frame.y : 0,
                    };
                },
                refitPosterFrames() {
                    this.$nextTick(() => {
                        const poster = document.getElementById('listingPoster');
                        if (!poster) return;
                        const primaryImg = poster.querySelector('.lp-primary img');
                        if (primaryImg?.complete) {
                            this.refitFrameImage('primary', primaryImg);
                        }
                        poster.querySelectorAll('.lp-featured .lp-skin').forEach((el, idx) => {
                            const img = el.querySelector('.lp-frame-viewport img');
                            if (img?.complete) {
                                this.refitFrameImage(`feat-${idx}`, img);
                            }
                        });
                        poster.querySelectorAll('.lp-gallery .lp-skin').forEach((el, idx) => {
                            const img = el.querySelector('.lp-frame-viewport img');
                            if (img?.complete) {
                                this.refitFrameImage(`bot-${idx}`, img);
                            }
                        });
                        this.updatePosterPreviewScale();
                        this.playFrameHint();
                    });
                },
                playFrameHint() {
                    if (!this.isPremiumLayout || this.frameHintPlayed) return;
                    try {
                        if (sessionStorage.getItem('wasit.listing.moveHint.v1')) return;
                    } catch {
                        // ignore
                    }
                    this.frameHintPlayed = true;
                    if (this._hintTimer) clearTimeout(this._hintTimer);
                    this._hintTimer = setTimeout(() => {
                        this.showFrameHint = true;
                        this._hintTimer = setTimeout(() => this.dismissFrameHint(), 2900);
                    }, 380);
                },
                dismissFrameHint() {
                    this.showFrameHint = false;
                    if (this._hintTimer) {
                        clearTimeout(this._hintTimer);
                        this._hintTimer = null;
                    }
                    try {
                        sessionStorage.setItem('wasit.listing.moveHint.v1', '1');
                    } catch {
                        // ignore
                    }
                },
                frameStyle(key) {
                    const frame = this.imageFrames[key] || { x: 0, y: 0, scale: 1 };
                    return {
                        transform: `translate(${frame.x}px, ${frame.y}px) scale(${frame.scale})`,
                    };
                },
                resetFrame(key) {
                    if (!this.imageFrames[key]) return;
                    const frame = this.imageFrames[key];
                    const scale = frame.coverScale || 1;
                    this.imageFrames[key] = { ...frame, x: 0, y: 0, scale, adjusted: false };
                    notifyCreateDraftChanged();
                },
                resetAllFrames() {
                    Object.keys(this.imageFrames).forEach((key) => this.resetFrame(key));
                },
                framePoint(event) {
                    if (event.touches?.length) {
                        return { x: event.touches[0].clientX, y: event.touches[0].clientY };
                    }
                    return { x: event.clientX, y: event.clientY };
                },
                startFrameDrag(key, event) {
                    if (!this.imageFrames[key]) return;
                    this.dismissFrameHint();
                    event.preventDefault();
                    const point = this.framePoint(event);
                    const frame = this.imageFrames[key];
                    this.dragging = {
                        key,
                        startX: point.x,
                        startY: point.y,
                        origX: frame.x,
                        origY: frame.y,
                    };
                    this._onFrameDrag = (e) => this.onFrameDrag(e);
                    this._onFrameDragEnd = () => this.endFrameDrag();
                    document.addEventListener('mousemove', this._onFrameDrag);
                    document.addEventListener('mouseup', this._onFrameDragEnd);
                    document.addEventListener('touchmove', this._onFrameDrag, { passive: false });
                    document.addEventListener('touchend', this._onFrameDragEnd);
                    event.currentTarget?.classList?.add('is-dragging');
                },
                onFrameDrag(event) {
                    if (!this.dragging) return;
                    event.preventDefault();
                    const point = this.framePoint(event);
                    const frame = this.imageFrames[this.dragging.key];
                    if (!frame) return;
                    const previewScale = this.posterPreviewScale || 1;
                    this.imageFrames[this.dragging.key] = {
                        ...frame,
                        x: this.dragging.origX + ((point.x - this.dragging.startX) / previewScale),
                        y: this.dragging.origY + ((point.y - this.dragging.startY) / previewScale),
                        adjusted: true,
                    };
                },
                endFrameDrag() {
                    document.querySelectorAll('.lp-frame-viewport.is-dragging').forEach((el) => {
                        el.classList.remove('is-dragging');
                    });
                    this.dragging = null;
                    if (this._onFrameDrag) {
                        document.removeEventListener('mousemove', this._onFrameDrag);
                        document.removeEventListener('touchmove', this._onFrameDrag);
                    }
                    if (this._onFrameDragEnd) {
                        document.removeEventListener('mouseup', this._onFrameDragEnd);
                        document.removeEventListener('touchend', this._onFrameDragEnd);
                    }
                    notifyCreateDraftChanged();
                },
                zoomFrame(key, event) {
                    if (!this.imageFrames[key]) return;
                    this.dismissFrameHint();
                    const frame = this.imageFrames[key];
                    const next = event.deltaY > 0 ? frame.scale * 0.9 : frame.scale * 1.14;
                    const scale = Math.min(20, Math.max(0.4, next));
                    this.imageFrames[key] = { ...frame, scale, adjusted: true };
                    notifyCreateDraftChanged();
                },
                proxiedUrl(url) {
                    if (!url) return '';
                    if (url.startsWith('blob:') || url.startsWith('data:')) return url;
                    if (url.startsWith('/') && !url.startsWith('//')) return url;
                    try {
                        const parsed = new URL(url, window.location.origin);
                        if (parsed.pathname.startsWith('/storage/')) {
                            return parsed.pathname + parsed.search;
                        }
                        if (parsed.origin === window.location.origin) {
                            return parsed.pathname + parsed.search;
                        }
                    } catch (_) {}
                    if (url.startsWith(window.location.origin)) {
                        return url.slice(window.location.origin.length) || url;
                    }
                    return this.imageProxy + '?url=' + encodeURIComponent(url);
                },
                rarityClass(rarity) {
                    const value = String(rarity || '').toLowerCase();
                    if (value.includes('prime') || value.includes('legend')) return 'is-prime';
                    if (value.includes('collector') || value.includes('epic')) return 'is-collector';
                    if (value.includes('special') || value.includes('star') || value.includes('lucky')) return 'is-special';
                    if (value.includes('elite')) return 'is-elite';
                    return '';
                },
                normalizeSkinTags(tags) {
                    return (tags || [])
                        .map((tag) => ({
                            name: tag.name || '',
                            image_url: tag.image_url ? this.proxiedUrl(tag.image_url) : '',
                        }))
                        .filter((tag) => tag.image_url);
                },
                normalizeSkin(skin) {
                    return {
                        hero: skin.hero || 'Hero',
                        name: skin.name || 'Skin',
                        image_url: this.proxiedUrl(skin.image_url || skin.thumbnail_url) || this.placeholderSkin,
                        rarity: skin.rarity || 'Skin',
                        tags: this.normalizeSkinTags(skin.tags),
                        painted: !!skin.painted,
                    };
                },
                async fetchJson(url, ms = 6000) {
                    const ctrl = new AbortController();
                    const timer = setTimeout(() => ctrl.abort(), ms);
                    try {
                        const response = await fetch(url, {
                            signal: ctrl.signal,
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json' },
                        });
                        if (!response.ok) throw new Error('Request failed');
                        return await response.json();
                    } finally {
                        clearTimeout(timer);
                    }
                },
                applyLocalFields() {
                    const imagesData = this.pickerData('imagesPicker');
                    if (imagesData?.selectedFiles?.[0]) {
                        this.primaryImageUrl = URL.createObjectURL(imagesData.selectedFiles[0]);
                    } else {
                        this.primaryImageUrl = this.placeholderSkin;
                    }

                    this.title = this.field('title', 'Account Listing');
                    this.formattedPrice = this.formatPrice(this.field('price_dzd', '0'));
                    this.applyCollectionTierFromForm();
                    this.accountCode = this.accountCode || this.randomCode();
                    this.stats = {
                        win_rate: this.field('win_rate', '0'),
                        heroes_count: this.field('heroes_count', '0'),
                        skins_count: this.field('skins_count', '0'),
                        rank: this.field('rank', '—'),
                        diamonds: this.field('diamonds', '0'),
                        bp: this.field('bp', '0'),
                        level: this.field('level', '0'),
                    };
                },
                padSkins(skins, count) {
                    const placeholder = {
                        hero: 'Hero',
                        name: 'Skin',
                        image_url: this.placeholderSkin,
                        rarity: 'Skin',
                        tags: [],
                        painted: false,
                    };
                    const list = skins.length ? [...skins] : [];
                    while (list.length < count) {
                        list.push(skins.length ? skins[list.length % skins.length] : placeholder);
                    }
                    return list.slice(0, count);
                },
                waitForImages(el, timeoutMs = 12000) {
                    const images = [...el.querySelectorAll('img')];
                    return Promise.all(images.map((img) => {
                        if (img.complete && img.naturalWidth > 0) {
                            return Promise.resolve();
                        }
                        if (img.complete) {
                            return Promise.resolve();
                        }
                        return new Promise((resolve) => {
                            const finish = () => resolve();
                            const timer = window.setTimeout(finish, timeoutMs);
                            img.addEventListener('load', () => {
                                window.clearTimeout(timer);
                                finish();
                            }, { once: true });
                            img.addEventListener('error', () => {
                                window.clearTimeout(timer);
                                finish();
                            }, { once: true });
                        });
                    }));
                },
                async ensurePosterImagesReady(el) {
                    el.querySelectorAll('img').forEach((img) => {
                        img.loading = 'eager';
                        img.decoding = 'sync';
                    });
                    await this.waitForImages(el, 12000);
                },
                isMobileDevice() {
                    return window.matchMedia('(max-width: 767px)').matches
                        || /Android|iPhone|iPad|iPod/i.test(navigator.userAgent || '');
                },
                canDownloadPoster() {
                    return !this.isMobileDevice();
                },
                posterExportScale() {
                    return POSTER_EXPORT_SCALE;
                },
                posterExportFilename() {
                    return 'wassitmarket-listing-1080.png';
                },
                posterExportMime() {
                    return 'image/png';
                },
                posterExportQuality() {
                    return 1;
                },
                posterImageMaxDimension() {
                    return 2048;
                },
                releaseCanvas(canvas) {
                    if (!canvas) return;
                    canvas.width = 0;
                    canvas.height = 0;
                },
                yieldToBrowser(ms = 0) {
                    return new Promise((resolve) => window.setTimeout(resolve, ms));
                },
                exportTimeoutMs() {
                    return 60000;
                },
                blobToDataUrl(blob) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(blob);
                    });
                },
                tryImageElementToDataUrl(img) {
                    if (!img?.naturalWidth || !img?.naturalHeight) {
                        return null;
                    }
                    try {
                        const maxDim = this.posterImageMaxDimension();
                        let width = img.naturalWidth;
                        let height = img.naturalHeight;
                        if (width > maxDim || height > maxDim) {
                            const scale = maxDim / Math.max(width, height);
                            width = Math.max(1, Math.round(width * scale));
                            height = Math.max(1, Math.round(height * scale));
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        if (!ctx) return null;
                        ctx.drawImage(img, 0, 0, width, height);
                        const dataUrl = canvas.toDataURL('image/png');
                        this.releaseCanvas(canvas);
                        return dataUrl;
                    } catch {
                        return null;
                    }
                },
                async resolvePosterImageDataUrl(img, src) {
                    if (!src) return null;
                    if (src.startsWith('data:')) return src;

                    const fromElement = this.tryImageElementToDataUrl(img);
                    if (fromElement) return fromElement;

                    try {
                        const controller = new AbortController();
                        const timer = window.setTimeout(() => controller.abort(), 12000);
                        const response = await fetch(src, {
                            credentials: 'same-origin',
                            signal: controller.signal,
                        });
                        window.clearTimeout(timer);
                        if (!response.ok) return null;
                        return await this.blobToDataUrl(await response.blob());
                    } catch (error) {
                        console.warn('Poster image fetch failed', src, error);
                        return null;
                    }
                },
                async buildPosterImageDataMap(posterEl) {
                    const map = new Map();
                    const images = [...posterEl.querySelectorAll('img')];

                    for (const img of images) {
                        const key = img.dataset.posterSrc || img.currentSrc || img.src || '';
                        if (!key || map.has(key)) continue;
                        const dataUrl = await this.resolvePosterImageDataUrl(img, key);
                        if (dataUrl) {
                            map.set(key, dataUrl);
                        }
                        await this.yieldToBrowser(0);
                    }

                    return map;
                },
                async withPosterExportLayout(run) {
                    const frame = this.$refs.posterFrame;
                    const prevScale = this.posterPreviewScale;
                    const prevHeight = frame?.style.height || '';
                    const prevMinHeight = frame?.style.minHeight || '';

                    this.posterPreviewScale = 1;
                    if (frame) {
                        frame.style.height = `${POSTER_HEIGHT}px`;
                        frame.style.minHeight = `${POSTER_HEIGHT}px`;
                    }

                    await this.$nextTick();
                    await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
                    await new Promise((resolve) => window.setTimeout(resolve, 100));

                    try {
                        return await run();
                    } finally {
                        this.posterPreviewScale = prevScale;
                        if (frame) {
                            frame.style.height = prevHeight;
                            frame.style.minHeight = prevMinHeight;
                            this.updatePosterPreviewScale();
                        }
                    }
                },
                getFrameViewportSize(viewport) {
                    const previewScale = this.posterPreviewScale || 1;
                    let vw = Math.round((viewport.clientWidth || viewport.offsetWidth || 0) / previewScale);
                    let vh = Math.round((viewport.clientHeight || viewport.offsetHeight || 0) / previewScale);
                    if (vw > 0 && vh > 0) {
                        return { vw, vh };
                    }

                    const host = viewport.closest('.lp-skin, .lp-primary, .lp-framable');
                    if (host) {
                        vw = Math.round((host.clientWidth || host.offsetWidth || 0) / previewScale);
                        vh = Math.round((host.clientHeight || host.offsetHeight || 0) / previewScale);
                    }

                    return {
                        vw: Math.max(1, vw),
                        vh: Math.max(1, vh),
                    };
                },
                applyPosterFrameImages(posterEl, rasterized) {
                    const states = [];

                    posterEl.querySelectorAll('[data-frame-key] img').forEach((img) => {
                        states.push({
                            img,
                            src: img.getAttribute('src') || '',
                            transform: img.style.transform || '',
                            position: img.style.position || '',
                            inset: img.style.inset || '',
                            left: img.style.left || '',
                            top: img.style.top || '',
                            width: img.style.width || '',
                            height: img.style.height || '',
                            maxWidth: img.style.maxWidth || '',
                            maxHeight: img.style.maxHeight || '',
                            margin: img.style.margin || '',
                            objectFit: img.style.objectFit || '',
                            objectPosition: img.style.objectPosition || '',
                        });
                    });

                    posterEl.querySelectorAll('[data-frame-key]').forEach((viewport) => {
                        const key = viewport.getAttribute('data-frame-key');
                        const img = viewport.querySelector('img');
                        if (!key || !img) return;

                        const baked = rasterized.get(key);
                        if (!baked) return;

                        img.src = baked;
                        this.flattenCloneFrameImage(img);
                    });

                    return states;
                },
                restorePosterImageState(states) {
                    states.forEach((state) => {
                        const { img } = state;
                        if (!img) return;
                        img.src = state.src;
                        img.style.transform = state.transform;
                        img.style.position = state.position;
                        img.style.inset = state.inset;
                        img.style.left = state.left;
                        img.style.top = state.top;
                        img.style.width = state.width;
                        img.style.height = state.height;
                        img.style.maxWidth = state.maxWidth;
                        img.style.maxHeight = state.maxHeight;
                        img.style.margin = state.margin;
                        img.style.objectFit = state.objectFit;
                        img.style.objectPosition = state.objectPosition;
                    });
                },
                stampPosterImageSources(posterEl) {
                    posterEl.querySelectorAll('img').forEach((img) => {
                        img.dataset.posterSrc = img.currentSrc || img.src || img.getAttribute('src') || '';
                    });
                },
                frameViewportBackground(viewport) {
                    const host = viewport?.closest('.lp-framable') || viewport?.parentElement;
                    if (!host) return '#111111';
                    const bg = window.getComputedStyle(host).backgroundColor;
                    if (!bg || bg === 'transparent' || bg === 'rgba(0, 0, 0, 0)') {
                        return '#111111';
                    }
                    return bg;
                },
                loadDecodedImage(src) {
                    return new Promise((resolve, reject) => {
                        if (!src) {
                            reject(new Error('empty image src'));
                            return;
                        }
                        const image = new Image();
                        if (!src.startsWith('data:') && !src.startsWith('blob:')) {
                            image.crossOrigin = 'anonymous';
                        }
                        image.onload = () => resolve(image);
                        image.onerror = () => reject(new Error('image load failed'));
                        image.src = src;
                    });
                },
                readFrameState(key) {
                    const stored = this.imageFrames?.[key] || {};

                    return {
                        x: Number(stored.x) || 0,
                        y: Number(stored.y) || 0,
                        scale: Number(stored.scale) || 1,
                    };
                },
                rasterizeFrameImage(sourceImg, vw, vh, frame, background = '#111111') {
                    const iw = sourceImg.naturalWidth || sourceImg.width;
                    const ih = sourceImg.naturalHeight || sourceImg.height;
                    if (!iw || !ih || !vw || !vh) return null;

                    const scale = Number(frame?.scale) || 1;
                    const tx = Number(frame?.x) || 0;
                    const ty = Number(frame?.y) || 0;
                    const cx = vw / 2;
                    const cy = vh / 2;
                    const contain = Math.min(vw / iw, vh / ih);
                    const dw = iw * contain;
                    const dh = ih * contain;
                    const dx = (vw - dw) / 2;
                    const dy = (vh - dh) / 2;

                    const canvas = document.createElement('canvas');
                    canvas.width = vw;
                    canvas.height = vh;
                    const ctx = canvas.getContext('2d');
                    if (!ctx) return null;

                    ctx.fillStyle = background;
                    ctx.fillRect(0, 0, vw, vh);
                    ctx.save();
                    ctx.beginPath();
                    ctx.rect(0, 0, vw, vh);
                    ctx.clip();
                    // CSS: transform: translate(tx, ty) scale(s); transform-origin: center
                    ctx.translate(cx, cy);
                    ctx.translate(tx, ty);
                    ctx.scale(scale, scale);
                    ctx.translate(-cx, -cy);
                    ctx.drawImage(sourceImg, dx, dy, dw, dh);
                    ctx.restore();

                    const dataUrl = canvas.toDataURL('image/png');
                    this.releaseCanvas(canvas);
                    return dataUrl;
                },
                flattenCloneFrameImage(img) {
                    img.style.transform = 'none';
                    img.style.position = 'absolute';
                    img.style.inset = '0';
                    img.style.left = '0';
                    img.style.top = '0';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.maxWidth = 'none';
                    img.style.maxHeight = 'none';
                    img.style.margin = '0';
                    img.style.objectFit = 'fill';
                    img.style.objectPosition = 'center center';
                    img.style.willChange = 'auto';
                },
                async bakePosterFrames(posterEl, imageMap) {
                    const rasterized = new Map();
                    for (const viewport of posterEl.querySelectorAll('[data-frame-key]')) {
                        const key = viewport.getAttribute('data-frame-key');
                        const img = viewport.querySelector('img');
                        if (!key || !img) continue;

                        const original = img.dataset.posterSrc || img.currentSrc || img.src || '';
                        const inlined = imageMap.get(original);

                        try {
                            let decoded = null;
                            if (img.complete && img.naturalWidth > 0) {
                                decoded = img;
                            } else if (inlined) {
                                decoded = await this.loadDecodedImage(inlined);
                            } else if (original) {
                                decoded = await this.loadDecodedImage(original);
                            }
                            if (!decoded) continue;

                            const { vw, vh } = this.getFrameViewportSize(viewport);
                            const background = this.frameViewportBackground(viewport);
                            const frame = this.readFrameState(key);
                            const dataUrl = this.rasterizeFrameImage(decoded, vw, vh, frame, background);
                            if (dataUrl) {
                                rasterized.set(key, dataUrl);
                            }
                        } catch (error) {
                            console.warn('Frame bake failed', key, error);
                        }
                    }
                    return rasterized;
                },
                applyPosterImageDataMap(posterEl, imageMap) {
                    posterEl.querySelectorAll('img').forEach((img) => {
                        if (img.closest('[data-frame-key]')) return;
                        const original = img.dataset.posterSrc || img.getAttribute('src') || '';
                        const inlined = imageMap.get(original);
                        if (inlined?.startsWith('data:')) {
                            img.src = inlined;
                        }
                    });
                },
                applyRasterizedFrames(posterEl, rasterized, imageMap) {
                    rasterized.forEach((dataUrl, key) => {
                        const viewport = posterEl.querySelector(`[data-frame-key="${key}"]`);
                        const img = viewport?.querySelector('img');
                        if (!img) return;
                        img.src = dataUrl;
                        this.flattenCloneFrameImage(img);
                    });

                    posterEl.querySelectorAll('[data-frame-key]').forEach((viewport) => {
                        const key = viewport.getAttribute('data-frame-key');
                        if (!key || rasterized.has(key)) return;
                        const img = viewport.querySelector('img');
                        if (!img) return;
                        const original = img.dataset.posterSrc || img.getAttribute('src') || '';
                        const inlined = imageMap.get(original);
                        if (inlined?.startsWith('data:')) {
                            img.src = inlined;
                            this.flattenCloneFrameImage(img);
                        }
                    });
                },
                syncPosterFrameImagesFromLive(live, clone) {
                    if (!live || !clone) return;

                    live.querySelectorAll('[data-frame-key]').forEach((liveViewport) => {
                        const key = liveViewport.getAttribute('data-frame-key');
                        if (!key) return;
                        const cloneViewport = clone.querySelector(`[data-frame-key="${key}"]`);
                        const liveImg = liveViewport.querySelector('img');
                        const cloneImg = cloneViewport?.querySelector('img');
                        if (!liveImg || !cloneImg) return;
                        cloneImg.src = liveImg.currentSrc || liveImg.src || '';
                        cloneImg.style.cssText = liveImg.style.cssText;
                    });
                },
                syncPosterCloneFromLive(live, clone) {
                    if (!live || !clone) return;

                    const selectors = [
                        '.lp-skin-name',
                        '.lp-hero-name',
                        '.lp-stat-val',
                        '.lp-stat-lbl',
                        '.lp-effects-title',
                        '.lp-recalls-title',
                        '.lp-price-value',
                        '.lp-rarity',
                    ];

                    selectors.forEach((selector) => {
                        const liveNodes = live.querySelectorAll(selector);
                        const cloneNodes = clone.querySelectorAll(selector);
                        liveNodes.forEach((node, index) => {
                            if (!cloneNodes[index]) return;
                            cloneNodes[index].textContent = node.textContent;
                        });
                    });

                    const liveSkinCards = live.querySelectorAll('.lp-skin');
                    const cloneSkinCards = clone.querySelectorAll('.lp-skin');
                    liveSkinCards.forEach((node, index) => {
                        if (!cloneSkinCards[index]) return;
                        cloneSkinCards[index].style.cssText = node.style.cssText;
                        cloneSkinCards[index].className = node.className;
                    });

                    clone.querySelectorAll('[x-cloak]').forEach((node) => {
                        node.removeAttribute('x-cloak');
                        node.style.display = '';
                    });
                },
                hardenPosterTextForExport(clone) {
                    const view = clone.ownerDocument?.defaultView;

                    clone.querySelectorAll('.lp-skin-meta').forEach((node) => {
                        node.style.overflow = 'visible';
                        node.style.zIndex = '5';
                        node.style.pointerEvents = 'none';
                    });

                    const tuneLabel = (node, color, minHeight) => {
                        node.style.overflow = 'visible';
                        node.style.textOverflow = 'clip';
                        node.style.whiteSpace = 'nowrap';
                        node.style.display = 'block';
                        node.style.width = '100%';
                        node.style.maxWidth = '100%';
                        node.style.opacity = '1';
                        node.style.visibility = 'visible';
                        node.style.color = color;
                        node.style.webkitTextFillColor = color;
                        node.style.lineHeight = '1.2';
                        node.style.minHeight = minHeight;

                        if (view) {
                            const fontSize = parseFloat(view.getComputedStyle(node).fontSize) || 0;
                            if (fontSize > 0 && fontSize < 8) {
                                node.style.fontSize = '8px';
                            }
                        }
                    };

                    clone.querySelectorAll('.lp-skin-name').forEach((node) => tuneLabel(node, '#5eead4', '11px'));
                    clone.querySelectorAll('.lp-hero-name').forEach((node) => tuneLabel(node, '#93c5fd', '10px'));

                    clone.querySelectorAll('.lp-skin-tags').forEach((node) => {
                        node.style.zIndex = '6';
                    });
                },
                preparePosterCloneForExport(clone, { imageMap, rasterized, live, framesAlreadyBaked = false }) {
                    if (!clone) return;
                    const scaleWrap = clone.closest('.listing-poster-scale-wrap');
                    if (scaleWrap) {
                        scaleWrap.style.transform = 'none';
                        scaleWrap.style.width = `${POSTER_WIDTH}px`;
                        scaleWrap.style.height = `${POSTER_HEIGHT}px`;
                    }
                    clone.style.transform = 'none';
                    clone.style.width = `${POSTER_WIDTH}px`;
                    clone.style.height = `${POSTER_HEIGHT}px`;
                    clone.classList.remove('is-showing-hint');
                    clone.querySelectorAll('.lp-move-hint,[data-html2canvas-ignore]').forEach((node) => node.remove());

                    this.applyPosterImageDataMap(clone, imageMap);

                    if (live && framesAlreadyBaked) {
                        this.syncPosterFrameImagesFromLive(live, clone);
                    } else {
                        this.applyRasterizedFrames(clone, rasterized, imageMap);
                    }

                    if (live) {
                        this.syncPosterCloneFromLive(live, clone);
                    }
                    this.hardenPosterTextForExport(clone);

                    clone.querySelectorAll('.lp-price-slot').forEach((node) => {
                        node.style.overflow = 'visible';
                        node.style.zIndex = '10';
                        node.style.display = 'flex';
                        node.style.alignItems = 'center';
                        node.style.justifyContent = 'center';
                    });
                    clone.querySelectorAll('.lp-price-value').forEach((node) => {
                        const isBasic = node.closest('.listing-poster.is-basic');
                        node.style.background = 'none';
                        node.style.backgroundClip = 'border-box';
                        node.style.webkitBackgroundClip = 'border-box';
                        node.style.color = '#dc2626';
                        node.style.webkitTextFillColor = '#dc2626';
                        node.style.filter = 'none';
                        node.style.display = 'inline-block';
                        node.style.lineHeight = '1';
                        node.style.fontFamily = '"Bebas Neue", "Montserrat", Impact, sans-serif';
                        node.style.fontSize = isBasic ? '36px' : '46px';
                        node.style.transformOrigin = 'center center';
                        node.style.transform = 'rotate(-10deg) translateY(-2px)';
                        node.style.opacity = '1';
                        node.style.visibility = 'visible';
                    });
                },
                async fetchSampleSkins(count) {
                    if (this.sampleSkinsCache?.length >= count) {
                        return this.sampleSkinsCache.slice(0, count).map((skin) => this.normalizeSkin(skin));
                    }

                    try {
                        const payload = await this.fetchJson(`/api/mlbb/skins/sample?count=${count}`, 20000);
                        const skins = (payload.skins || []).filter((skin) => skin.image_url || skin.thumbnail_url);
                        this.sampleSkinsCache = skins;
                        return skins.map((skin) => this.normalizeSkin(skin));
                    } catch (error) {
                        console.error('Sample skins fetch failed:', error);
                        return [];
                    }
                },
                enrichSkinsFromPicker(skins, skinsData) {
                    if (!skinsData?.skinsCache) {
                        return skins;
                    }

                    return skins.map((skin) => {
                        if (skin.image_url || skin.thumbnail_url) {
                            return skin;
                        }

                        const cached = skinsData.skinsCache[skin.hero] || [];
                        const match = cached.find((row) => row.name === skin.name);
                        if (!match) {
                            return skin;
                        }

                        return {
                            ...skin,
                            image_url: match.image_url || match.thumbnail_url || skin.image_url,
                            thumbnail_url: match.thumbnail_url || null,
                            rarity: skin.rarity || match.rarity || 'Skin',
                            tags: skin.tags?.length ? skin.tags : (match.tags || []),
                            painted: skin.painted ?? match.painted ?? false,
                        };
                    });
                },
                normalizeCatalogItem(item) {
                    return {
                        id: item.id ?? null,
                        name: item.name || 'Item',
                        image_url: this.proxiedUrl(item.image_url || item.thumbnail_url || '') || this.placeholderSkin,
                    };
                },
                padCatalogItems(items, count) {
                    const list = items.map((item) => this.normalizeCatalogItem(item));
                    while (list.length < count) {
                        list.push({
                            id: null,
                            name: 'Item',
                            image_url: this.placeholderSkin,
                        });
                    }
                    return list.slice(0, count);
                },
                async fetchRandomCatalog(endpoint, itemsKey, count) {
                    try {
                        const payload = await this.fetchJson(`${endpoint}?count=${count}`, 20000);
                        const items = payload[itemsKey] || [];
                        return items.filter((item) => item.image_url || item.thumbnail_url);
                    } catch (error) {
                        console.error('Catalog fetch failed:', endpoint, error);
                        return [];
                    }
                },
                async buildPreview() {
                    if (this.building) return;
                    this.building = true;
                    this.loading = false;

                    try {
                        if (this.previewUseDummyData) {
                            await this.applyDummyPreview();
                            return;
                        }

                        this.applyLocalFields();

                        const skinsData = this.pickerData('skinsPicker');
                        const emotesData = this.pickerData('emotesPicker');
                        const recallsData = this.pickerData('recallsPicker');

                        let skins = this.enrichSkinsFromPicker(
                            (skinsData?.selectedSkins || []),
                            skinsData
                        )
                            .filter((skin) => skin.image_url || skin.thumbnail_url)
                            .map((skin) => this.normalizeSkin(skin));

                        const extraPromise = this.isPremiumLayout && skins.length < 8
                            ? this.fetchSampleSkins(8 - skins.length)
                            : Promise.resolve([]);
                        const emotesPromise = emotesData?.selectedItems?.length
                            ? Promise.resolve(emotesData.selectedItems)
                            : this.fetchRandomCatalog('/api/mlbb/emotes/sample', 'emotes', 6);
                        const recallsPromise = recallsData?.selectedItems?.length
                            ? Promise.resolve(recallsData.selectedItems)
                            : this.fetchRandomCatalog('/api/mlbb/recalls/sample', 'recalls', 6);

                        const [extra, emotes, recalls] = await Promise.all([
                            extraPromise,
                            emotesPromise,
                            recallsPromise,
                        ]);

                        if (extra.length) {
                            skins = [...skins, ...extra];
                        }

                        this.applyPosterSkins(skins);
                        this.previewEmotes = this.padCatalogItems(emotes, 6);
                        this.previewRecalls = this.padCatalogItems(recalls, 6);
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.building = false;
                        this.loading = false;
                        this.refitPosterFrames();
                    }
                },
                applyPosterSkins(skins) {
                    if (this.isPremiumLayout) {
                        this.featuredSkins = this.padSkins(skins.slice(0, 2), 2);
                        this.bottomSkins = this.padSkins(skins.length > 2 ? skins.slice(2) : skins, 6);
                        this.galleryLayout = { cols: 6, rows: 1, count: this.bottomSkins.length };
                        this.initImageFrames();
                        return;
                    }

                    const visible = (skins || []).slice(0, 48);
                    this.featuredSkins = [];
                    this.bottomSkins = visible;
                    this.galleryLayout = this.pickGalleryGrid(visible.length);
                    this.initImageFrames();
                },
                async applyDummyPreview() {
                    this.primaryImageUrl = this.placeholderSkin;
                    this.formattedPrice = '479.999';
                    this.applyDummyCollectionBadge();
                    this.stats = {
                        win_rate: '53',
                        heroes_count: '133',
                        skins_count: '281',
                        rank: '123',
                        diamonds: '5000',
                        bp: '25000',
                        level: '30',
                    };

                    const fallbackSkins = [
                        { hero: 'Granger', name: 'Cosmic Finality', image_url: this.placeholderSkin, rarity: 'Prime' },
                        { hero: 'Hayabusa', name: 'Shura', image_url: this.placeholderSkin, rarity: 'Collector' },
                        { hero: 'Gusion', name: 'KOF', image_url: this.placeholderSkin, rarity: 'Special' },
                        { hero: 'Miya', name: 'Modena Butterfly', image_url: this.placeholderSkin, rarity: 'Star' },
                        { hero: 'Dyrroth', name: 'Orochi Chris', image_url: this.placeholderSkin, rarity: 'KOF' },
                        { hero: 'Ruby', name: 'Lady Zombie', image_url: this.placeholderSkin, rarity: 'Collector' },
                        { hero: 'Saber', name: 'Golden Rose', image_url: this.placeholderSkin, rarity: 'Elite' },
                        { hero: 'Chang\'e', name: 'Bunny', image_url: this.placeholderSkin, rarity: 'Star' },
                    ];

                    const [sampleSkins, emotes, recalls] = await Promise.all([
                        this.fetchSampleSkins(12),
                        this.fetchRandomCatalog('/api/mlbb/emotes/sample', 'emotes', 6),
                        this.fetchRandomCatalog('/api/mlbb/recalls/sample', 'recalls', 6),
                    ]);

                    const skins = sampleSkins.length
                        ? sampleSkins
                        : fallbackSkins.map((skin) => this.normalizeSkin(skin));

                    this.applyPosterSkins(skins);
                    this.previewEmotes = this.padCatalogItems(emotes, 6);
                    this.previewRecalls = this.padCatalogItems(recalls, 6);
                },
                async exportPosterBlob() {
                    return this.withPosterExportLayout(async () => {
                    const el = document.getElementById('listingPoster');
                    if (!el || typeof html2canvas !== 'function') {
                        return null;
                    }

                    const started = Date.now();
                    while (this.loading && Date.now() - started < 20000) {
                        await new Promise((resolve) => setTimeout(resolve, 200));
                    }

                    this.endFrameDrag();
                    this.dismissFrameHint();
                    await this.$nextTick();

                    if (document.fonts?.ready) {
                        await Promise.race([
                            document.fonts.ready,
                            new Promise((resolve) => window.setTimeout(resolve, 4000)),
                        ]);
                    }

                    this.downloadStatus = 'Loading poster images…';
                    await this.ensurePosterImagesReady(el);
                    this.stampPosterImageSources(el);
                    this.downloadStatus = 'Preparing artwork…';
                    const imageMap = await this.buildPosterImageDataMap(el);
                    const rasterized = await this.bakePosterFrames(el, imageMap);

                    if (rasterized.size === 0 && imageMap.size === 0) {
                        throw new Error('Could not prepare poster images. Wait for the preview to finish loading, then try again.');
                    }

                    const restoreState = this.applyPosterFrameImages(el, rasterized);
                    this.applyPosterImageDataMap(el, imageMap);
                    await this.waitForImages(el, 3000);
                    await this.yieldToBrowser(50);

                    this.downloadStatus = 'Rendering PNG…';
                    const exportScale = this.posterExportScale();
                    let canvas;
                    try {
                        canvas = await Promise.race([
                            html2canvas(el, {
                                backgroundColor: '#c80000',
                                width: POSTER_WIDTH,
                                height: POSTER_HEIGHT,
                                scale: exportScale,
                                useCORS: true,
                                allowTaint: true,
                                logging: false,
                                foreignObjectRendering: false,
                                scrollX: 0,
                                scrollY: 0,
                                imageTimeout: 15000,
                                onclone: (clonedDoc) => {
                                    this.preparePosterCloneForExport(
                                        clonedDoc.getElementById('listingPoster'),
                                        { imageMap, rasterized, live: el, framesAlreadyBaked: true }
                                    );
                                },
                            }),
                            new Promise((_, reject) => {
                                window.setTimeout(
                                    () => reject(new Error('Poster export timed out. Try again when images finish loading.')),
                                    this.exportTimeoutMs()
                                );
                            }),
                        ]);
                    } finally {
                        this.restorePosterImageState(restoreState);
                        imageMap.clear();
                        rasterized.clear();
                    }

                    const mime = this.posterExportMime();
                    const quality = this.posterExportQuality();
                    const blob = await new Promise((resolve, reject) => {
                        canvas.toBlob(
                            (file) => file ? resolve(file) : reject(new Error('Could not export image.')),
                            mime,
                            quality
                        );
                    });
                    this.releaseCanvas(canvas);
                    await this.yieldToBrowser(50);
                    return blob;
                    });
                },
                async exportPosterFile() {
                    if (!this.canDownloadPoster()) {
                        return null;
                    }
                    const blob = await this.exportPosterBlob();
                    if (!blob) return null;
                    return new File([blob], 'listing-poster.png', { type: 'image/png' });
                },
                async downloadPoster() {
                    this.downloadError = '';
                    this.downloadStatus = '';
                    if (!this.canDownloadPoster()) {
                        this.downloadError = 'PNG download is available on desktop only. Open this page on a computer to export.';
                        return;
                    }
                    if (typeof html2canvas !== 'function') {
                        this.downloadError = 'Download library failed to load. Refresh and try again.';
                        return;
                    }
                    if (!document.getElementById('listingPoster')) {
                        this.downloadError = 'Poster is not ready yet.';
                        return;
                    }
                    this.downloading = true;
                    try {
                        const blob = await this.exportPosterBlob();
                        if (!blob) {
                            this.downloadError = 'Poster is not ready yet.';
                            return;
                        }
                        this.downloadStatus = 'Saving PNG…';
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = this.posterExportFilename();
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        window.setTimeout(() => URL.revokeObjectURL(url), 2000);
                    } catch (error) {
                        console.error(error);
                        this.downloadError = error?.message || 'Could not generate PNG. Wait for skins to finish loading, then try again.';
                    } finally {
                        this.downloading = false;
                        this.downloadStatus = '';
                    }
                },
            };
        }

        function accountImagesPicker() {
            return {
                imageCount: 0,
                maxImages: 10,
                selectedFiles: [],
                showPrimaryHelp: false,
                initialized: false,
                async init() {
                    const stored = await loadCreateDraftImages();
                    if (stored.length) {
                        this.addFiles(stored, { silent: true });
                    }
                    this.initialized = true;
                    notifyCreateDraftChanged();
                },
                addFiles(files, options = {}) {
                    const allowed = this.maxImages - this.imageCount;
                    const next = files.filter((f) => {
                        if (f.type && f.type.startsWith('image/')) return true;
                        return /\.(jpe?g|png|webp|gif)$/i.test(f.name || '');
                    }).slice(0, allowed);
                    if (files.length > allowed) {
                        alert(`Maximum ${this.maxImages} images allowed.`);
                    }
                    this.selectedFiles = [...this.selectedFiles, ...next];
                    this.imageCount = this.selectedFiles.length;
                    const native = document.getElementById('images');
                    if (native && this.selectedFiles.length) {
                        const transfer = new DataTransfer();
                        this.selectedFiles.forEach((file) => transfer.items.add(file));
                        native.files = transfer.files;
                    }
                    if (!options.silent) {
                        notifyCreateDraftChanged();
                    }
                },
                setPrimary(index) {
                    if (index <= 0) return;
                    const [file] = this.selectedFiles.splice(index, 1);
                    this.selectedFiles.unshift(file);
                    notifyCreateDraftChanged();
                },
                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                    this.imageCount = this.selectedFiles.length;
                    notifyCreateDraftChanged();
                },
            };
        }
    </script>
    @endpush
@endsection
