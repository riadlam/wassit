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
                            
                            <!-- Highlighted Skins Section -->
                            <div class="mb-8" x-data="{
                                categories: [],
                                loading: false,
                                searchQuery: '',
                                expandedRoles: {},
                                expandedHeroes: {},
                                selectedSkinIds: [],
                                async init() {
                                    await this.loadSkinsData();
                                },
                                async loadSkinsData() {
                                    this.loading = true;
                                    try {
                                        console.log('Fetching skins data from /api/mlbb/skins...');
                                        const response = await fetch('/api/mlbb/skins');
                                        console.log('Response status:', response.status);
                                        
                                        if (!response.ok) throw new Error('Failed to load skins data: ' + response.status);
                                        
                                        const data = await response.json();
                                        console.log('Loaded skins data:', data);
                                        console.log('Categories count:', data.categories?.length || 0);
                                        
                                        // Sort categories alphabetically
                                        let categories = (data.categories || []).sort((a, b) => {
                                            return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
                                        });
                                        
                                        // Sort heroes and skins alphabetically
                                        categories = categories.map(category => {
                                            const sortedHeroes = (category.heroes || []).sort((a, b) => {
                                                const heroA = (a.hero || '').trim().toLowerCase();
                                                const heroB = (b.hero || '').trim().toLowerCase();
                                                return heroA.localeCompare(heroB, undefined, { sensitivity: 'base' });
                                            });
                                            
                                            const heroesWithSortedSkins = sortedHeroes.map(hero => {
                                                // Build both plain names and id-bearing structures, sorted by name
                                                const withIds = (hero.skins_with_ids || []).slice().sort((a, b) => {
                                                    return (a.name || '').toLowerCase().localeCompare((b.name || '').toLowerCase(), undefined, { sensitivity: 'base' });
                                                });
                                                const namesOnly = withIds.map(s => s.name);
                                                return { ...hero, skins_with_ids: withIds, skins: namesOnly };
                                            });
                                            
                                            return { ...category, heroes: heroesWithSortedSkins };
                                        });
                                        
                                        this.categories = categories;
                                        console.log('Processed categories:', this.categories.length);
                                        console.log('First category:', this.categories[0]);
                                    } catch (error) {
                                        console.error('Error loading skins data:', error);
                                        console.error('Error details:', error.message);
                                        this.categories = [];
                                    } finally {
                                        this.loading = false;
                                    }
                                },
                                toggleRole(roleIndex) {
                                    this.expandedRoles[roleIndex] = !this.expandedRoles[roleIndex];
                                },
                                toggleHero(roleIndex, heroIndex) {
                                    const key = `${roleIndex}-${heroIndex}`;
                                    this.expandedHeroes[key] = !this.expandedHeroes[key];
                                },
                                toggleSkinById(skinId) {
                                    const id = Number(skinId);
                                    const idx = this.selectedSkinIds.indexOf(id);
                                    if (idx > -1) {
                                        this.selectedSkinIds.splice(idx, 1);
                                    } else {
                                        this.selectedSkinIds.push(id);
                                    }
                                    this.updateHiddenInputs();
                                },
                                isSkinSelectedById(skinId) {
                                    return this.selectedSkinIds.includes(Number(skinId));
                                },
                                getSelectedCount() {
                                    return this.selectedSkinIds.length;
                                },
                                getSelectedSkinsList() {
                                    const out = [];
                                    for (const id of this.selectedSkinIds) {
                                        const info = this.findSkinById(id);
                                        if (info) out.push(`${info.hero} - ${info.name}`);
                                    }
                                    return out;
                                },
                                findSkinById(id) {
                                    id = Number(id);
                                    for (const category of this.categories) {
                                        for (const hero of category.heroes) {
                                            for (const s of (hero.skins_with_ids || [])) {
                                                if (Number(s.id) === id) return { hero: hero.hero, name: s.name };
                                            }
                                        }
                                    }
                                    return null;
                                },
                                updateHiddenInputs() {
                                    // Update hidden input with selected skins
                                    const hiddenInput = document.getElementById('highlighted_skins_input');
                                    if (hiddenInput) {
                                        hiddenInput.value = this.selectedSkinIds.join(',');
                                    }
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
                                            const matchingSkins = hero.skins.filter(skin => 
                                                skin.toLowerCase().includes(query) || 
                                                hero.hero.toLowerCase().includes(query) ||
                                                category.name.toLowerCase().includes(query)
                                            );
                                            if (matchingSkins.length > 0) {
                                                return { ...hero, skins: matchingSkins };
                                            }
                                            return null;
                                        }).filter(h => h);
                                        if (filteredHeroes.length > 0) {
                                            return { ...category, heroes: filteredHeroes };
                                        }
                                        return null;
                                    }).filter(c => c);
                                }
                            }">
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
                                        <p class="text-xs text-gray-500 mt-2">Total categories: <span x-text="categories.length"></span></p>
                                        <p class="text-xs text-gray-500">Search query: <span x-text="searchQuery || 'none'"></span></p>
                                    </div>
                                </div>
                                
                                <!-- Selected Skins Preview -->
                                <div x-show="getSelectedCount() > 0" class="mt-4 p-4 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <h3 class="text-sm font-semibold text-white mb-2">Selected Skins (<span x-text="getSelectedCount()"></span>)</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(skinText, index) in getSelectedSkinsList()" :key="index">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                                <span x-text="skinText"></span>
                                                <button 
                                                    type="button"
                                                    @click="
                                                        const skinParts = skinText.split(' - ');
                                                        const heroName = skinParts[0].trim().toLowerCase();
                                                        const skinName = skinParts[1].trim().toLowerCase();
                                                        const keyToRemove = `${heroName}::${skinName}`;
                                                        selectedSkins = selectedSkins.filter(key => key !== keyToRemove);
                                                        updateHiddenInputs();
                                                    "
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
                                    value=""
                                >
                            </div>
                            
                            <!-- Images Section -->
                            <div class="mb-8" x-data="{ 
                                imageCount: 0,
                                maxImages: 10,
                                selectedFiles: [],
                                handleFileSelect(event) {
                                    const files = Array.from(event.target.files || []);
                                    const allowed = this.maxImages - this.imageCount;
                                    if (files.length > allowed) {
                                        alert(`Maximum ${this.maxImages} images allowed. You can only add ${allowed} more.`);
                                        // Keep current selection; clear the input to avoid accidental replacement
                                        event.target.value = '';
                                        return;
                                    }
                                    // Append new files to existing selection
                                    this.selectedFiles = [...this.selectedFiles, ...files];
                                    this.imageCount = this.selectedFiles.length;
                                    // Sync back to the file input so form submits all selected files
                                    const input = document.getElementById('images');
                                    const dt = new DataTransfer();
                                    this.selectedFiles.forEach(file => dt.items.add(file));
                                    input.files = dt.files;
                                    // Clear the native input to allow re-selecting the same file again if needed
                                    event.target.value = '';
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

