@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        @php
            // Use game-specific banner if available, otherwise default
            $gameBanner = null;
            if ($game->slug === 'mlbb') {
                $gameBanner = Storage::url('home_page/mlbbbanner.webp');
            }
            $defaultBanner = 'https://wassit.diaszone.com/storage/home_page/degaultbanner.webp';
            $initialBanner = $gameBanner ?: $defaultBanner;
        @endphp
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('{{ $initialBanner }}'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen" style="padding-top: 80px;">
        <!-- Small Header -->
        <div class="border-b" style="background-color: #252429; border-color: #2d2c31;">
            <div class="container mx-auto px-4 py-4">
                <div class="flex items-center gap-3 mb-1">
                    @if($game->slug === 'mlbb')
                        <img src="{{ asset('storage/home_games/mobile-legends.png') }}" alt="{{ $game->name }}" class="w-8 h-8 object-contain flex-shrink-0">
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $game->name }} {{ __('messages.accounts') }}</h1>
                        <p class="text-gray-400 text-sm mt-0.5">{{ str_replace(':game', $game->name, __('messages.cheap_accounts_with_instant_delivery')) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
        <!-- Filters Section - Single Row -->
        <div class="rounded-xl p-5 mb-6" style="background-color: #0e1015; border: 1px solid #2d2c31;" x-data="filterComponent()">
            <!-- Filters Row -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search Bar -->
                <div class="flex-[0.8] min-w-[200px] sm:min-w-[300px]">
                    <div class="w-full">
                        <div class="relative">
                            <input 
                                type="search" 
                                x-model="searchQuery"
                                placeholder="{{ __('messages.search') }}" 
                                class="h-10 block w-full border-0 rounded-md shadow-sm sm:text-sm disabled:opacity-50 disabled:pointer-events-none text-white placeholder-gray-500 pl-9 py-2 px-4 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none h-9 mt-0.5">
                                <i class="text-sm fa-solid fa-magnifying-glass" style="color: rgba(255, 255, 255, 0.5);"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collection Filter -->
                <div class="relative min-w-[125px] sm:block hidden flex-grow md:flex-grow-0" x-data="{
                    collectionOpen: false,
                    selectedCollection: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const collectionParam = urlParams.get('filter[collection]');
                        if (collectionParam) {
                            const collectionMap = {
                                'Expert Collector': '{{ __('messages.expert_collector') }}',
                                'Renowned Collector': '{{ __('messages.renowned_collector') }}',
                                'Exalted Collector': '{{ __('messages.exalted_collector') }}',
                                'Mega Collector': '{{ __('messages.mega_collector') }}',
                                'World Collector': '{{ __('messages.world_collector') }}'
                            };
                            return collectionMap[collectionParam] || collectionParam;
                        }
                        return '';
                    })()
                }" @click.away="collectionOpen = false">
                    <button 
                        type="button" 
                        @click="collectionOpen = !collectionOpen"
                        class="custom-dropdown-button items-center focus:outline focus:outline-offset-2 focus-visible:outline disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 text-sm outline-none flex justify-between w-full h-[42px] px-4 group"
                        :class="collectionOpen ? 'active' : ''"
                    >
                        <div class="flex items-center pr-2 truncate gap-x-2">
                            <i class="text-base fa-solid fa-gem custom-dropdown-icon"></i>
                            <span x-text="selectedCollection || '{{ __('messages.collection') }}'" class="font-medium"></span>
                        </div>
                        <i class="text-xs fa-solid fa-caret-down" style="color: rgba(255, 255, 255, 0.7); transition: transform 0.2s;" :style="collectionOpen ? 'transform: rotate(180deg);' : ''"></i>
                    </button>
                    <div 
                        x-show="collectionOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="custom-dropdown-menu absolute z-50 mt-1 w-full"
                        x-init="$watch('collectionOpen', (value) => {
                            if (value) {
                                $nextTick(() => {
                                    setTimeout(() => {
                                        const dropdown = $el;
                                        // Reset styles first
                                        dropdown.style.left = '0';
                                        dropdown.style.right = 'auto';
                                        // Get position after reset
                                        const rect = dropdown.getBoundingClientRect();
                                        const viewportWidth = window.innerWidth;
                                        const dropdownWidth = rect.width;
                                        const leftPosition = rect.left;

                                        // Check if it would overflow on the right
                                        if (leftPosition + dropdownWidth > viewportWidth) {
                                            dropdown.style.right = '0';
                                            dropdown.style.left = 'auto';
                                        } else {
                                            dropdown.style.left = '0';
                                            dropdown.style.right = 'auto';
                                        }
                                    }, 10);
                                });
                            }
                        })"
                        x-cloak
                    >
                        <div class="py-1">
                            <button
                                @click="selectedCollection = ''; collectionOpen = false; $dispatch('collection-changed', '')"
                                class="custom-dropdown-item block w-full text-left px-4 py-2.5 text-sm"
                                :class="selectedCollection === '' ? 'active' : ''"
                            >
                                {{ __('messages.all_collections') }}
                            </button>
                            <button
                                @click="selectedCollection = '{{ __('messages.expert_collector') }}'; collectionOpen = false; $dispatch('collection-changed', 'Expert Collector')"
                                class="custom-dropdown-item block w-full text-left px-4 py-2.5 text-sm"
                                :class="selectedCollection === '{{ __('messages.expert_collector') }}' ? 'active' : ''"
                            >
                                {{ __('messages.expert_collector') }}
                            </button>
                            <button
                                @click="selectedCollection = '{{ __('messages.renowned_collector') }}'; collectionOpen = false; $dispatch('collection-changed', 'Renowned Collector')"
                                class="custom-dropdown-item block w-full text-left px-4 py-2.5 text-sm"
                                :class="selectedCollection === '{{ __('messages.renowned_collector') }}' ? 'active' : ''"
                            >
                                {{ __('messages.renowned_collector') }}
                            </button>
                            <button
                                @click="selectedCollection = '{{ __('messages.exalted_collector') }}'; collectionOpen = false; $dispatch('collection-changed', 'Exalted Collector')"
                                class="custom-dropdown-item block w-full text-left px-4 py-2.5 text-sm"
                                :class="selectedCollection === '{{ __('messages.exalted_collector') }}' ? 'active' : ''"
                            >
                                {{ __('messages.exalted_collector') }}
                            </button>
                            <button
                                @click="selectedCollection = '{{ __('messages.mega_collector') }}'; collectionOpen = false; $dispatch('collection-changed', 'Mega Collector')"
                                class="custom-dropdown-item block w-full text-left px-4 py-2.5 text-sm"
                                :class="selectedCollection === '{{ __('messages.mega_collector') }}' ? 'active' : ''"
                            >
                                {{ __('messages.mega_collector') }}
                            </button>
                            <button
                                @click="selectedCollection = '{{ __('messages.world_collector') }}'; collectionOpen = false; $dispatch('collection-changed', 'World Collector')"
                                class="custom-dropdown-item block w-full text-left px-4 py-2.5 text-sm"
                                :class="selectedCollection === '{{ __('messages.world_collector') }}' ? 'active' : ''"
                            >
                                {{ __('messages.world_collector') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Price Range Filter -->
                <div class="relative min-w-[200px] flex-grow md:flex-grow-0" x-data="{ 
                    priceOpen: false, 
                    priceFrom: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const priceParam = urlParams.get('filter[price]');
                        if (priceParam && priceParam.includes('-')) {
                            return priceParam.split('-')[0];
                        }
                        return '';
                    })(),
                    priceTo: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const priceParam = urlParams.get('filter[price]');
                        if (priceParam && priceParam.includes('-')) {
                            return priceParam.split('-')[1];
                        }
                        if (priceParam && priceParam.includes('+')) {
                            return priceParam.replace('+', '');
                        }
                        return '';
                    })(),
                    minPrice: 0,
                    maxPrice: 100000,
                    sliderMin: 0,
                    sliderMax: 100000,
                    init() {
                        // Initialize slider when dropdown opens
                        this.$watch('priceOpen', (value) => {
                            if (value) {
                                this.$nextTick(() => {
                                    const priceSlider = document.getElementById('price-range-slider');
                                    if (priceSlider && !priceSlider.noUiSlider) {
                                        window.priceRangeSlider = noUiSlider.create(priceSlider, {
                                            start: [parseFloat(this.priceFrom) || 0, parseFloat(this.priceTo) || 100000],
                                            connect: true,
                                            range: {
                                                'min': 0,
                                                'max': 100000
                                            },
                                            step: 100,
                                            format: {
                                                to: function(value) {
                                                    return Math.round(value);
                                                },
                                                from: function(value) {
                                                    return Number(value);
                                                }
                                            }
                                        });
                                        
                                        // Apply custom handle size after slider is created
                                        setTimeout(() => {
                                            const handles = priceSlider.querySelectorAll('.noUi-handle');
                                            handles.forEach(handle => {
                                                handle.style.width = '13.6px';
                                                handle.style.height = '13.6px';
                                                handle.style.right = '-6.8px';
                                                handle.style.top = '-3.8px';
                                            });
                                        }, 10);
                                        
                                        // Update inputs when slider changes
                                        window.priceRangeSlider.on('update', (values) => {
                                            this.priceFrom = values[0];
                                            this.priceTo = values[1];
                                        });
                                    } else if (window.priceRangeSlider) {
                                        // Update slider if it already exists
                                        const from = parseFloat(this.priceFrom) || 0;
                                        const to = parseFloat(this.priceTo) || 100000;
                                        window.priceRangeSlider.set([from, to]);
                                    }
                                });
                            }
                        });
                    },
                    updatePriceFromSlider(value) {
                        if (window.priceRangeSlider && value) {
                            const from = parseFloat(value) || 0;
                            const to = parseFloat(this.priceTo) || 100000;
                            window.priceRangeSlider.set([from, to], false);
                        }
                    },
                    updatePriceToSlider(value) {
                        if (window.priceRangeSlider && value) {
                            const from = parseFloat(this.priceFrom) || 0;
                            const to = parseFloat(value) || 100000;
                            window.priceRangeSlider.set([from, to], false);
                        }
                    },
                    applyPriceFilter() {
                        let priceValue = '';
                        if (this.priceFrom && this.priceTo) {
                            priceValue = this.priceFrom + '-' + this.priceTo;
                        } else if (this.priceFrom) {
                            priceValue = this.priceFrom + '+';
                        }
                        this.$dispatch('price-changed', priceValue);
                        this.priceOpen = false;
                    },
                    clearPriceFilter() {
                        this.priceFrom = '';
                        this.priceTo = '';
                        if (window.priceRangeSlider) {
                            window.priceRangeSlider.set([0, 100000]);
                        }
                        this.$dispatch('price-changed', '');
                        this.priceOpen = false;
                    }
                }" @click.away="priceOpen = false">
                    <button 
                        type="button" 
                        @click="priceOpen = !priceOpen"
                        class="custom-dropdown-button items-center focus:outline focus:outline-offset-2 focus-visible:outline disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 text-sm outline-none flex justify-between w-full h-[42px] px-4 group"
                        :class="priceOpen ? 'active' : ''"
                    >
                        <div class="flex items-center pr-2 truncate gap-x-2">
                            <i class="text-base fa-solid fa-coins custom-dropdown-icon"></i>
                            <span class="font-medium" x-text="priceFrom && priceTo ? priceFrom + ' - ' + priceTo + ' DZD' : (priceFrom ? priceFrom + '+ DZD' : '{{ __('messages.price_range') }}')"></span>
                        </div>
                        <i class="text-xs fa-solid fa-caret-down" style="color: rgba(255, 255, 255, 0.7); transition: transform 0.2s;" :style="priceOpen ? 'transform: rotate(180deg);' : ''"></i>
                    </button>
                    <div 
                        x-show="priceOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="custom-dropdown-menu absolute z-50 mt-1 w-full"
                        style="min-width: 300px;"
                        x-init="$watch('priceOpen', (value) => {
                            if (value) {
                                $nextTick(() => {
                                    setTimeout(() => {
                                        const dropdown = $el;
                                        // Reset styles first
                                        dropdown.style.left = '0';
                                        dropdown.style.right = 'auto';
                                        // Get position after reset
                                        const rect = dropdown.getBoundingClientRect();
                                        const viewportWidth = window.innerWidth;
                                        const dropdownWidth = rect.width;
                                        const leftPosition = rect.left;
                                        
                                        // Check if it would overflow on the right
                                        if (leftPosition + dropdownWidth > viewportWidth) {
                                            dropdown.style.right = '0';
                                            dropdown.style.left = 'auto';
                                        } else {
                                            dropdown.style.left = '0';
                                            dropdown.style.right = 'auto';
                                        }
                                    }, 10);
                                });
                            }
                        })"
                        x-cloak
                    >
                        <div class="p-4">
                            <!-- From and To Input Fields -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ __('messages.from') }}</label>
                                    <input 
                                        type="number" 
                                        x-model="priceFrom"
                                        :min="sliderMin"
                                        :max="sliderMax"
                                        placeholder="{{ __('messages.min') }}"
                                        class="w-full px-3 py-2 rounded-md text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        @input="updatePriceFromSlider($event.target.value)"
                                    >
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ __('messages.to') }}</label>
                                    <input 
                                        type="number" 
                                        x-model="priceTo"
                                        :min="sliderMin"
                                        :max="sliderMax"
                                        placeholder="{{ __('messages.max') }}"
                                        class="w-full px-3 py-2 rounded-md text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                        @input="updatePriceToSlider($event.target.value)"
                                    >
                                </div>
                            </div>
                            
                            <!-- Price Range Slider -->
                            <div class="mb-4">
                                <div id="price-range-slider" class="price-slider"></div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <button 
                                    @click="applyPriceFilter()"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-white rounded-md transition-colors"
                                    style="background-color: #ef4444; hover:background-color: #dc2626;"
                                >
                                    {{ __('messages.apply') }}
                                </button>
                                <button 
                                    @click="clearPriceFilter()"
                                    class="px-4 py-2 text-sm font-medium text-gray-300 rounded-md transition-colors hover:text-white"
                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                >
                                    {{ __('messages.clear') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skins Filter (ID-based) -->
                <div class="relative min-w-[140px] flex-grow md:flex-grow-0" x-data="{ 
                    skinsOpen: false,
                    selectedSkinIds: [],
                    expandedRoles: {},
                    expandedHeroes: {},
                    categories: [],
                    loading: false,
                    searchQuery: '',
                    async init() {
                        await this.loadSkinsData();
                        this.prefillFromUrl();
                    },
                    async loadSkinsData() {
                        this.loading = true;
                        try {
                            const response = await fetch('/api/mlbb/skins');
                            if (!response.ok) throw new Error('Failed to load skins data');
                            const data = await response.json();
                            let categories = (data.categories || []).sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }));
                            categories = categories.map(category => {
                                const sortedHeroes = (category.heroes || []).sort((a, b) => (a.hero || '').trim().toLowerCase().localeCompare((b.hero || '').trim().toLowerCase(), undefined, { sensitivity: 'base' }));
                                const heroesWithSkins = sortedHeroes.map(hero => {
                                    const sortedSkins = (hero.skins_with_ids || []).slice().sort((a, b) => (a.name || '').trim().toLowerCase().localeCompare((b.name || '').trim().toLowerCase(), undefined, { sensitivity: 'base' }));
                                    return { ...hero, skins_with_ids: sortedSkins };
                                });
                                return { ...category, heroes: heroesWithSkins };
                            });
                            this.categories = categories;
                        } catch (e) {
                            console.error('Error loading skins data:', e);
                            this.categories = [];
                        } finally { this.loading = false; }
                    },
                    prefillFromUrl() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const skinsParam = urlParams.get('filter[skins]');
                        if (!skinsParam) return;
                        const parts = skinsParam.split(',').map(p => p.trim()).filter(p => p);
                        parts.forEach(p => {
                            if (/^[0-9]+$/.test(p)) {
                                const id = Number(p);
                                if (!this.selectedSkinIds.includes(id)) this.selectedSkinIds.push(id);
                            } else {
                                // legacy slug role-hero-skin -> attempt map
                                const id = this.mapLegacySlugToId(p);
                                if (id && !this.selectedSkinIds.includes(id)) this.selectedSkinIds.push(id);
                            }
                        });
                        this.applySkinsFilter();
                    },
                    mapLegacySlugToId(slug) {
                        const parts = slug.split('-');
                        if (parts.length < 3) return null;
                        const role = parts[0];
                        const hero = parts[1];
                        const skin = parts.slice(2).join('-');
                        const heroNorm = hero.replace(/-/g, ' ').toLowerCase();
                        const skinNorm = skin.replace(/-/g, ' ').toLowerCase();
                        for (const category of this.categories) {
                            for (const h of category.heroes) {
                                if (h.hero.trim().toLowerCase().replace(/\s+/g, ' ') === heroNorm) {
                                    for (const s of (h.skins_with_ids || [])) {
                                        if ((s.name || '').trim().toLowerCase().replace(/\s+/g, ' ') === skinNorm) return Number(s.id);
                                    }
                                }
                            }
                        }
                        return null;
                    },
                    toggleRole(i) { this.expandedRoles[i] = !this.expandedRoles[i]; },
                    toggleHero(r, h) { const key = `${r}-${h}`; this.expandedHeroes[key] = !this.expandedHeroes[key]; },
                    toggleSkinId(id) {
                        id = Number(id);
                        const idx = this.selectedSkinIds.indexOf(id);
                        if (idx > -1) this.selectedSkinIds.splice(idx, 1); else this.selectedSkinIds.push(id);
                        this.applySkinsFilter();
                    },
                    isSkinSelectedId(id) { return this.selectedSkinIds.includes(Number(id)); },
                    getSelectedCount() { return this.selectedSkinIds.length; },
                    getSelectedSkinsList() {
                        const out = [];
                        for (const id of this.selectedSkinIds) {
                            const info = this.findSkinById(id);
                            if (info) out.push({ id: Number(id), text: `${info.hero} - ${info.name}` });
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
                    clearSkins() { this.selectedSkinIds = []; this.expandedRoles = {}; this.expandedHeroes = {}; this.applySkinsFilter(); },
                    applySkinsFilter() { this.$dispatch('skins-changed', this.selectedSkinIds.join(',')); },
                    filteredCategories() {
                        if (!this.searchQuery) return this.categories;
                        const q = this.searchQuery.toLowerCase();
                        return this.categories.map(category => {
                            const heroes = category.heroes.map(hero => {
                                const skins = (hero.skins_with_ids || []).filter(s => (s.name||'').toLowerCase().includes(q) || hero.hero.toLowerCase().includes(q) || category.name.toLowerCase().includes(q));
                                if (skins.length) return { ...hero, skins_with_ids: skins };
                                return null;
                            }).filter(Boolean);
                            if (heroes.length) return { ...category, heroes };
                            return null;
                        }).filter(Boolean);
                    }
                }" @click.away="skinsOpen = false">
                    <button 
                        type="button" 
                        @click="skinsOpen = !skinsOpen"
                        class="custom-dropdown-button items-center focus:outline focus:outline-offset-2 focus-visible:outline disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 text-sm outline-none flex justify-between w-full h-[42px] px-4 group"
                        :class="skinsOpen ? 'active' : ''"
                    >
                        <div class="flex items-center pr-2 truncate gap-x-2">
                            <i class="text-base fa-solid fa-mask custom-dropdown-icon"></i>
                            <span class="font-medium" x-text="(() => { const sel = getSelectedSkinsList(); return sel.length === 1 ? sel[0].text : (sel.length > 1 ? sel.length + ' selected' : '{{ __('messages.skins') }}'); })()"></span>
                        </div>
                        <i class="text-xs fa-solid fa-caret-down" style="color: rgba(255, 255, 255, 0.7); transition: transform 0.2s;" :style="skinsOpen ? 'transform: rotate(180deg);' : ''"></i>
                    </button>
                    <div 
                        x-show="skinsOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="custom-dropdown-menu absolute z-50 mt-1"
                        style="min-width: 400px; max-width: 500px; max-height: 600px; overflow-y: auto;"
                        x-init="$watch('skinsOpen', (value) => {
                            if (value) {
                                $nextTick(() => {
                                    setTimeout(() => {
                                        const dropdown = $el;
                                        // Reset styles first
                                        dropdown.style.left = '0';
                                        dropdown.style.right = 'auto';
                                        // Get position after reset
                                        const rect = dropdown.getBoundingClientRect();
                                        const viewportWidth = window.innerWidth;
                                        const dropdownWidth = rect.width;
                                        const leftPosition = rect.left;
                                        
                                        // Check if it would overflow on the right
                                        if (leftPosition + dropdownWidth > viewportWidth) {
                                            dropdown.style.right = '0';
                                            dropdown.style.left = 'auto';
                                        } else {
                                            dropdown.style.left = '0';
                                            dropdown.style.right = 'auto';
                                        }
                                    }, 10);
                                });
                            }
                        })"
                        x-cloak
                    >
                        <div class="p-4">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold text-white">{{ __('messages.filter_by_skins') }}</h3>
                                <button 
                                    @click="clearSkins()"
                                    class="text-xs text-gray-400 hover:text-white transition-colors"
                                    x-show="getSelectedCount() > 0"
                                >
                                    {{ __('messages.clear_all') }}
                                </button>
                            </div>
                            
                            <!-- Search Bar -->
                            <div class="mb-4">
                                <div class="relative">
                                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                    <input 
                                        type="text" 
                                        x-model="searchQuery"
                                        placeholder="Search by hero name, skin name, or role..."
                                        class="w-full pl-9 pr-4 py-2 rounded-md text-sm text-white placeholder:text-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                    >
                                </div>
                            </div>
                            
                            <!-- Selected Skins Display -->
                            <div x-show="getSelectedCount() > 0" class="mb-4 p-3 rounded-lg" style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-white">Selected (<span x-text="getSelectedCount()"></span>)</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 max-h-20 overflow-y-auto">
                                    <template x-for="(sel, index) in getSelectedSkinsList()" :key="sel.id">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs text-white" style="background-color: rgba(239, 68, 68, 0.2);">
                                            <span x-text="sel.text" class="truncate max-w-[120px]"></span>
                                            <button 
                                                type="button"
                                                @click="selectedSkinIds = selectedSkinIds.filter(id => Number(id) !== Number(sel.id)); applySkinsFilter();"
                                                class="hover:text-red-400 transition-colors ml-0.5"
                                            >
                                                <i class="fa-solid fa-times text-xs"></i>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Loading State -->
                            <div x-show="loading" class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
                                <p class="text-gray-400 text-sm mt-2">Loading skins...</p>
                            </div>
                            
                            <!-- Roles Accordion -->
                            <div class="space-y-2" x-show="!loading && filteredCategories().length > 0">
                                <template x-for="(category, roleIndex) in filteredCategories()" :key="roleIndex">
                                    <div class="border rounded-md" style="border-color: #2d2c31; background-color: #1b1a1e;">
                                        <!-- Role Header -->
                                        <button 
                                            @click="toggleRole(roleIndex)"
                                            class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-white hover:bg-opacity-50 transition-colors"
                                            style="background-color: rgba(27, 26, 30, 0.5);"
                                        >
                                            <span x-text="category.name"></span>
                                            <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="expandedRoles[roleIndex] ? 'rotate-180' : ''"></i>
                                        </button>
                                        
                                        <!-- Heroes List -->
                                        <div 
                                            x-show="expandedRoles[roleIndex]"
                                            x-collapse
                                            class="px-4 pb-3 space-y-3"
                                        >
                                            <template x-for="(hero, heroIndex) in category.heroes" :key="heroIndex">
                                                <div>
                                                    <!-- Hero Header (Clickable to expand/collapse skins) -->
                                                    <button
                                                        @click="toggleHero(roleIndex, heroIndex)"
                                                        class="w-full flex items-center justify-between text-xs font-medium text-gray-300 mb-2 hover:text-white transition-colors"
                                                    >
                                                        <span x-text="hero.hero" class="capitalize"></span>
                                                        <i class="fa-solid fa-chevron-down text-xs transition-transform ml-2" :class="expandedHeroes[`${roleIndex}-${heroIndex}`] ? 'rotate-180' : ''"></i>
                                                    </button>
                                                    
                                                    <!-- Skins List -->
                                                    <div 
                                                        x-show="expandedHeroes[`${roleIndex}-${heroIndex}`]"
                                                        x-collapse
                                                        class="flex flex-wrap gap-2 ml-2"
                                                    >
                                                            <template x-for="(skinObj, skinIndex) in (hero.skins_with_ids || [])" :key="skinObj.id">
                                                                <button
                                                                    @click="toggleSkinId(skinObj.id)"
                                                                    class="px-3 py-1.5 text-xs rounded-md transition-all border"
                                                                    :class="isSkinSelectedId(skinObj.id) ? 'bg-red-600 border-red-600 text-white' : 'bg-transparent border-gray-600 text-gray-300 hover:border-red-500 hover:text-white'"
                                                                >
                                                                    <i class="fa-solid" :class="isSkinSelectedId(skinObj.id) ? 'fa-check-circle' : 'fa-circle'"></i>
                                                                    <span class="ml-1.5" x-text="skinObj.name"></span>
                                                                </button>
                                                            </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Empty State -->
                            <div x-show="!loading && filteredCategories().length === 0" class="text-center py-8">
                                <i class="fa-solid fa-search text-4xl text-gray-600 mb-3"></i>
                                <p class="text-gray-400 text-sm">No skins found matching your search</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Filters -->
                <div class="relative min-w-[200px] flex-grow md:flex-grow-0" x-data="{ 
                    additionalOpen: false,
                    activeFilter: null, // 'winRate' or 'level'
                    winRateFrom: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const param = urlParams.get('filter[win_rate]');
                        if (param && param.includes('-')) {
                            return param.split('-')[0];
                        }
                        return '';
                    })(),
                    winRateTo: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const param = urlParams.get('filter[win_rate]');
                        if (param && param.includes('-')) {
                            return param.split('-')[1];
                        }
                        return '';
                    })(),
                    levelFrom: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const param = urlParams.get('filter[level]');
                        if (param && param.includes('-')) {
                            return param.split('-')[0];
                        }
                        return '';
                    })(),
                    levelTo: (() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const param = urlParams.get('filter[level]');
                        if (param && param.includes('-')) {
                            return param.split('-')[1];
                        }
                        return '';
                    })(),
                    updateWinRateFromSlider(value) {
                        if (window.winRateSlider && value) {
                            const from = parseFloat(value) || 0;
                            const to = parseFloat(this.winRateTo) || 100;
                            window.winRateSlider.set([from, to], false);
                        }
                    },
                    updateWinRateToSlider(value) {
                        if (window.winRateSlider && value) {
                            const from = parseFloat(this.winRateFrom) || 0;
                            const to = parseFloat(value) || 100;
                            window.winRateSlider.set([from, to], false);
                        }
                    },
                    updateLevelFromSlider(value) {
                        if (window.levelSlider && value) {
                            const from = parseFloat(value) || 20;
                            const to = parseFloat(this.levelTo) || 130;
                            window.levelSlider.set([from, to], false);
                        }
                    },
                    updateLevelToSlider(value) {
                        if (window.levelSlider && value) {
                            const from = parseFloat(this.levelFrom) || 20;
                            const to = parseFloat(value) || 130;
                            window.levelSlider.set([from, to], false);
                        }
                    },
                    applyAdditionalFilters() {
                        let winRateValue = '';
                        if (this.winRateFrom && this.winRateTo) {
                            winRateValue = this.winRateFrom + '-' + this.winRateTo;
                        }
                        
                        let levelValue = '';
                        if (this.levelFrom && this.levelTo) {
                            levelValue = this.levelFrom + '-' + this.levelTo;
                        }
                        
                        this.$dispatch('additional-filters-changed', {
                            winRate: winRateValue,
                            level: levelValue
                        });
                        this.additionalOpen = false;
                    },
                    clearAdditionalFilters() {
                        this.winRateFrom = '';
                        this.winRateTo = '';
                        this.levelFrom = '';
                        this.levelTo = '';
                        this.activeFilter = null;
                        if (window.winRateSlider) {
                            window.winRateSlider.set([0, 100]);
                        }
                        if (window.levelSlider) {
                            window.levelSlider.set([20, 130]);
                        }
                        this.$dispatch('additional-filters-changed', {
                            winRate: '',
                            level: ''
                        });
                        this.additionalOpen = false;
                    },
                    selectFilter(filterType) {
                        this.activeFilter = this.activeFilter === filterType ? null : filterType;
                        if (this.activeFilter === 'winRate') {
                            this.$nextTick(() => {
                                const winRateSlider = document.getElementById('win-rate-slider');
                                if (winRateSlider && !winRateSlider.noUiSlider) {
                                    window.winRateSlider = noUiSlider.create(winRateSlider, {
                                        start: [parseFloat(this.winRateFrom) || 0, parseFloat(this.winRateTo) || 100],
                                        connect: true,
                                        range: {
                                            'min': 0,
                                            'max': 100
                                        },
                                        step: 1,
                                        format: {
                                            to: function(value) {
                                                return Math.round(value);
                                            },
                                            from: function(value) {
                                                return Number(value);
                                            }
                                        }
                                    });
                                    
                                    window.winRateSlider.on('update', (values) => {
                                        this.winRateFrom = values[0];
                                        this.winRateTo = values[1];
                                    });
                                } else if (window.winRateSlider) {
                                    window.winRateSlider.set([parseFloat(this.winRateFrom) || 0, parseFloat(this.winRateTo) || 100]);
                                }
                            });
                        } else if (this.activeFilter === 'level') {
                            this.$nextTick(() => {
                                const levelSlider = document.getElementById('level-slider');
                                if (levelSlider && !levelSlider.noUiSlider) {
                                    window.levelSlider = noUiSlider.create(levelSlider, {
                                        start: [parseFloat(this.levelFrom) || 20, parseFloat(this.levelTo) || 130],
                                        connect: true,
                                        range: {
                                            'min': 20,
                                            'max': 130
                                        },
                                        step: 1,
                                        format: {
                                            to: function(value) {
                                                return Math.round(value);
                                            },
                                            from: function(value) {
                                                return Number(value);
                                            }
                                        }
                                    });
                                    
                                    window.levelSlider.on('update', (values) => {
                                        this.levelFrom = values[0];
                                        this.levelTo = values[1];
                                    });
                                } else if (window.levelSlider) {
                                    window.levelSlider.set([parseFloat(this.levelFrom) || 20, parseFloat(this.levelTo) || 130]);
                                }
                            });
                        }
                    }
                }" @click.away="additionalOpen = false">
                    <button 
                        type="button" 
                        @click="additionalOpen = !additionalOpen"
                        class="custom-dropdown-button items-center focus:outline focus:outline-offset-2 focus-visible:outline disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap py-3 sm:py-2.5 text-sm outline-none flex justify-between w-full h-[42px] px-4 group"
                        :class="additionalOpen ? 'active' : ''"
                    >
                        <div class="flex items-center pr-2 truncate gap-x-2">
                            <i class="text-base fa-solid fa-filter custom-dropdown-icon"></i>
                            <span class="font-medium">{{ __('messages.more_filters') }}</span>
                        </div>
                        <i class="text-xs fa-solid fa-caret-down" style="color: rgba(255, 255, 255, 0.7); transition: transform 0.2s;" :style="additionalOpen ? 'transform: rotate(180deg);' : ''"></i>
                    </button>
                    <div 
                        x-show="additionalOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="custom-dropdown-menu absolute z-50 mt-1"
                        style="min-width: 400px;"
                        x-init="$watch('additionalOpen', (value) => {
                            if (value) {
                                $nextTick(() => {
                                    setTimeout(() => {
                                        const dropdown = $el;
                                        // Reset styles first
                                        dropdown.style.left = '0';
                                        dropdown.style.right = 'auto';
                                        // Get position after reset
                                        const rect = dropdown.getBoundingClientRect();
                                        const viewportWidth = window.innerWidth;
                                        const dropdownWidth = rect.width;
                                        const leftPosition = rect.left;
                                        
                                        // Check if it would overflow on the right
                                        if (leftPosition + dropdownWidth > viewportWidth) {
                                            dropdown.style.right = '0';
                                            dropdown.style.left = 'auto';
                                        } else {
                                            dropdown.style.left = '0';
                                            dropdown.style.right = 'auto';
                                        }
                                    }, 10);
                                });
                            }
                        })"
                        x-cloak
                    >
                        <div class="p-4">
                            <!-- Win Rate Filter Title -->
                            <div class="mb-2 border rounded-md" style="border-color: #2d2c31; background-color: #1b1a1e;">
                                <button 
                                    @click="selectFilter('winRate')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-white hover:bg-opacity-50 transition-colors"
                                    style="background-color: rgba(27, 26, 30, 0.5);"
                                >
                                    <span>{{ __('messages.win_rate_percent') }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="activeFilter === 'winRate' ? 'rotate-180' : ''"></i>
                                </button>
                                
                                <!-- Win Rate Range Slider -->
                                <div 
                                    x-show="activeFilter === 'winRate'"
                                    x-collapse
                                    class="px-4 pb-4"
                                >
                                    <div class="flex items-center gap-3 mb-4 mt-3">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ __('messages.from') }}</label>
                                            <input 
                                                type="number" 
                                                x-model="winRateFrom"
                                                min="0"
                                                max="100"
                                                placeholder="{{ __('messages.min') }}"
                                                class="w-full px-3 py-2 rounded-md text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                @input="updateWinRateFromSlider($event.target.value)"
                                            >
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ __('messages.to') }}</label>
                                            <input 
                                                type="number" 
                                                x-model="winRateTo"
                                                min="0"
                                                max="100"
                                                placeholder="{{ __('messages.max') }}"
                                                class="w-full px-3 py-2 rounded-md text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                @input="updateWinRateToSlider($event.target.value)"
                                            >
                                        </div>
                                    </div>
                                    <div id="win-rate-slider" class="price-slider"></div>
                                </div>
                            </div>
                            
                            <!-- Level Filter Title -->
                            <div class="mb-4 border rounded-md" style="border-color: #2d2c31; background-color: #1b1a1e;">
                                <button 
                                    @click="selectFilter('level')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-white hover:bg-opacity-50 transition-colors"
                                    style="background-color: rgba(27, 26, 30, 0.5);"
                                >
                                    <span>{{ __('messages.level') }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="activeFilter === 'level' ? 'rotate-180' : ''"></i>
                                </button>
                                
                                <!-- Level Range Slider -->
                                <div 
                                    x-show="activeFilter === 'level'"
                                    x-collapse
                                    class="px-4 pb-4"
                                >
                                    <div class="flex items-center gap-3 mb-4 mt-3">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ __('messages.from') }}</label>
                                            <input 
                                                type="number" 
                                                x-model="levelFrom"
                                                min="20"
                                                max="130"
                                                placeholder="{{ __('messages.min') }}"
                                                class="w-full px-3 py-2 rounded-md text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                @input="updateLevelFromSlider($event.target.value)"
                                            >
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ __('messages.to') }}</label>
                                            <input 
                                                type="number" 
                                                x-model="levelTo"
                                                min="0"
                                                max="130"
                                                placeholder="{{ __('messages.max') }}"
                                                class="w-full px-3 py-2 rounded-md text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                                                style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                                @input="updateLevelToSlider($event.target.value)"
                                            >
                                        </div>
                                    </div>
                                    <div id="level-slider" class="price-slider"></div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2 pt-4 border-t" style="border-color: #2d2c31;">
                                <button 
                                    @click="applyAdditionalFilters()"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-white rounded-md transition-colors"
                                    style="background-color: #ef4444; hover:background-color: #dc2626;"
                                >
                                    {{ __('messages.apply') }}
                                </button>
                                <button 
                                    @click="clearAdditionalFilters()"
                                    class="px-4 py-2 text-sm font-medium text-gray-300 rounded-md transition-colors hover:text-white"
                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                >
                                    {{ __('messages.clear') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applied Filters Display -->
            <div class="mt-5 pt-5 border-t" style="border-color: #2d2c31;" x-show="hasAppliedFilters()" x-transition>
                <p class="text-xs font-semibold text-gray-400 mb-2.5 uppercase tracking-wide">{{ __('messages.applied_filters') ?? 'Active Filters' }}</p>
                <div class="flex flex-wrap gap-2">
                    <!-- Search Filter Tag -->
                    <template x-if="searchQuery">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium text-white" style="background-color: #ef4444; border: 1px solid #dc2626;">
                            <span x-text="'Search: ' + searchQuery"></span>
                            <button @click="clearSearchFilter()" class="ml-1 hover:opacity-80 transition-opacity" title="Remove filter">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Collection Filter Tag -->
                    <template x-if="filters.collection">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium text-white" style="background-color: #f59e0b; border: 1px solid #d97706;">
                            <span x-text="'Collection: ' + filters.collection"></span>
                            <button @click="clearCollectionFilter()" class="ml-1 hover:opacity-80 transition-opacity" title="Remove filter">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Price Filter Tag -->
                    <template x-if="filters.price">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium text-white" style="background-color: #10b981; border: 1px solid #059669;">
                            <span x-text="'Price: ' + filters.price"></span>
                            <button @click="clearPriceFilter()" class="ml-1 hover:opacity-80 transition-opacity" title="Remove filter">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Win Rate Filter Tag -->
                    <template x-if="filters.winRate">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium text-white" style="background-color: #8b5cf6; border: 1px solid #7c3aed;">
                            <span x-text="'Win Rate: ' + filters.winRate + '%'"></span>
                            <button @click="clearWinRateFilter()" class="ml-1 hover:opacity-80 transition-opacity" title="Remove filter">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Level Filter Tag -->
                    <template x-if="filters.level">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium text-white" style="background-color: #8b5cf6; border: 1px solid #7c3aed;">
                            <span x-text="'Level: ' + filters.level"></span>
                            <button @click="clearLevelFilter()" class="ml-1 hover:opacity-80 transition-opacity" title="Remove filter">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Skins Filter Tag -->
                    <template x-if="filters.skins">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-medium text-white" style="background-color: #ec4899; border: 1px solid #db2777;">
                            <span x-text="(() => { const ids = (filters.skins || '').split(',').filter(Boolean).map(v => Number(v)); const names = []; ids.forEach(id => { const info = (function(){ const comp = document.querySelector('[x-data*=filterComponent]'); if (!comp) return null; const skinsDropdown = comp.querySelector('[x-data*=skinsOpen]'); if (!skinsDropdown) return null; const data = Alpine.$data(skinsDropdown); return data ? data.findSkinById(id) : null; })(); if (info) names.push(info.hero + ' - ' + info.name); }); if (names.length === 1) return 'Skin: ' + names[0]; if (names.length > 1) return 'Skins: ' + names.join(', '); return 'Skins'; })()"></span>
                            <button @click="clearSkinsFilter()" class="ml-1 hover:opacity-80 transition-opacity" title="Remove filter">
                                <i class="fa-solid fa-times text-xs"></i>
                            </button>
                        </div>
                    </template>
                    
                    <!-- Clear All Button -->
                    <button @click="clearAllFilters()" class="px-3 py-1.5 rounded-md text-xs text-gray-300 transition-colors hover:bg-red-600 hover:text-white font-medium" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">
                        <i class="fa-solid fa-trash-alt mr-1"></i>{{ __('messages.clear_all') ?? 'Clear All' }}
                    </button>
                </div>
            </div>

            <!-- Popular Searches -->
            <div class="mt-5 pt-5 border-t" style="border-color: #2d2c31;">
                <p class="text-xs font-semibold text-gray-400 mb-2.5 uppercase tracking-wide">{{ __('messages.popular_searches') }}</p>
                <div class="flex flex-wrap gap-2">
                    <button @click="applySearch('collector')" class="px-3 py-1.5 rounded-md text-xs text-gray-300 transition-colors hover:bg-red-600 hover:text-white font-medium" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">collector</button>
                    <button @click="applySearch('ling collector')" class="px-3 py-1.5 rounded-md text-xs text-gray-300 transition-colors hover:bg-red-600 hover:text-white font-medium" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">ling collector</button>
                    <button @click="applySearch('epic')" class="px-3 py-1.5 rounded-md text-xs text-gray-300 transition-colors hover:bg-red-600 hover:text-white font-medium" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">epic</button>
                    <button @click="applySearch('kof')" class="px-3 py-1.5 rounded-md text-xs text-gray-300 transition-colors hover:bg-red-600 hover:text-white font-medium" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">kof</button>
                    <button @click="applySearch('gojo')" class="px-3 py-1.5 rounded-md text-xs text-gray-300 transition-colors hover:bg-red-600 hover:text-white font-medium" style="background-color: #1b1a1e; border: 1px solid #2d2c31;">gojo</button>
                </div>
            </div>
        </div>

        <!-- Accounts Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4" style="gap: 15px;" data-accounts-grid>
            @forelse($accounts as $account)
                @include('components.account-card', ['account' => $account])
            @empty
                <div class="col-span-full rounded-xl p-12 text-center" style="background-color: #252429; border: 1px solid #2d2c31;">
                    <p class="text-gray-400 text-lg">{{ __('messages.no_accounts_available') }}</p>
                </div>
            @endforelse
        </div>
    </div>
    </div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css">
<style>
    [x-cloak] { display: none !important; }
    
    /* Reusable Custom Dropdown Styles */
    .custom-dropdown-button {
        background-color: #1b1a1e;
        border: 1px solid #2d2c31;
        border-radius: 0.5rem;
        color: #ffffff;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .custom-dropdown-button:hover {
        border-color: #3d3c41;
        background-color: #1f1e22;
    }
    
    .custom-dropdown-button:focus {
        outline: none;
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .custom-dropdown-button.active {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    
    .custom-dropdown-menu {
        background-color: #252429;
        border: 1px solid #2d2c31;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.05);
        overflow: hidden;
    }
    
    .custom-dropdown-item {
        color: #ffffff;
        transition: all 0.15s ease-in-out;
        border-left: 3px solid transparent;
    }
    
    .custom-dropdown-item:hover {
        background-color: #2d2c31;
        border-left-color: #ef4444;
    }
    
    .custom-dropdown-item.active {
        background-color: #ef4444;
        border-left-color: #dc2626;
    }
    
    .custom-dropdown-icon {
        color: rgba(255, 255, 255, 0.5);
        transition: color 0.2s ease-in-out;
    }
    
    .custom-dropdown-button:hover .custom-dropdown-icon,
    .custom-dropdown-button.active .custom-dropdown-icon {
        color: rgba(255, 255, 255, 0.9);
    }
    
    /* Price Range Slider Styles */
    .price-slider {
        margin: 17px 0;
        width: 85%;
        margin-left: auto;
        margin-right: auto;
    }
    
    .noUi-target {
        background-color: #1b1a1e;
        border: 1px solid #2d2c31;
        border-radius: 4px;
        box-shadow: none;
        height: 6px;
    }
    
    .noUi-connect {
        background: linear-gradient(to right, #ef4444, #dc2626);
    }
    
    .noUi-handle {
        background-color: #ef4444 !important;
        border: 2px solid #ffffff !important;
        border-radius: 50% !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        cursor: pointer !important;
        width: 13.6px !important;
        height: 13.6px !important;
        right: -6.8px !important;
        top: -3.8px !important;
    }
    
    .noUi-handle:hover {
        background-color: #dc2626 !important;
    }
    
    .noUi-handle:before,
    .noUi-handle:after {
        display: none !important;
    }
    
    .noUi-handle-lower,
    .noUi-handle-upper {
        width: 13.6px !important;
        height: 13.6px !important;
    }
    
    .noUi-tooltip {
        background-color: #252429;
        border: 1px solid #2d2c31;
        color: #ffffff;
        font-size: 10px;
    }
    
    .ts-wrapper.single .ts-control,
    .ts-wrapper.multi .ts-control {
        background-color: #1b1a1e !important;
        border: 1px solid #2d2c31 !important;
        border-radius: 0.5rem !important;
        padding: 0.625rem 1rem !important;
        min-height: 42px !important;
        font-size: 0.875rem !important;
    }
    
    .ts-wrapper.single .ts-control input,
    .ts-wrapper.multi .ts-control input {
        color: #ffffff !important;
    }
    
    .ts-wrapper.single .ts-control .item,
    .ts-wrapper.multi .ts-control .item {
        color: #ffffff !important;
    }
    
    .ts-wrapper.single .ts-control::after,
    .ts-wrapper.multi .ts-control::after {
        border-color: #ffffff transparent transparent !important;
    }
    
    .ts-wrapper.single.focus .ts-control,
    .ts-wrapper.multi.focus .ts-control {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2) !important;
    }
    
    .ts-dropdown {
        background-color: #252429 !important;
        border: 1px solid #2d2c31 !important;
        border-radius: 0.5rem !important;
    }
    
    .ts-dropdown .option {
        color: #ffffff !important;
        background-color: #252429 !important;
    }
    
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background-color: #1b1a1e !important;
        color: #ffffff !important;
    }
    
    .ts-dropdown .option.selected {
        background-color: #dc2626 !important;
        color: #ffffff !important;
    }
    
    .ts-wrapper.multi .ts-control [data-value] {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border: none !important;
    }
    
    .ts-wrapper.multi .ts-control [data-value] .remove {
        border-color: #ffffff !important;
        color: #ffffff !important;
    }
    
    /* Skins Filter Accordion Styles */
    [x-collapse] {
        transition: all 0.3s ease-in-out;
    }
    
    .skins-filter-role-header {
        transition: background-color 0.2s ease;
    }
    
    .skins-filter-role-header:hover {
        background-color: rgba(239, 68, 68, 0.1) !important;
    }
    
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js"></script>
<script>
    // Inject i18n for collection tiers from Blade translations
    window.COLLECTION_TIER_I18N = {
        'Expert Collector': @json(__('messages.expert_collector')),
        'Renowned Collector': @json(__('messages.renowned_collector')),
        'Exalted Collector': @json(__('messages.exalted_collector')),
        'Mega Collector': @json(__('messages.mega_collector')),
        'World Collector': @json(__('messages.world_collector'))
    };
    function translateCollectionTier(tier) {
        if (!tier) return '';
        return window.COLLECTION_TIER_I18N[tier] || tier;
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Tom Select for remaining selects
        // Store select instances for Alpine.js access (if needed in future)
        window.filterSelects = {};
        
        // Initialize price range slider (fallback init)
        function initPriceSlider() {
            const priceSlider = document.getElementById('price-range-slider');
            if (priceSlider && !priceSlider.noUiSlider) {
                window.priceRangeSlider = noUiSlider.create(priceSlider, {
                    start: [0, 100000],
                    connect: true,
                    range: {
                        'min': 0,
                        'max': 100000
                    },
                    step: 100,
                    format: {
                        to: function(value) {
                            return Math.round(value);
                        },
                        from: function(value) {
                            return Number(value);
                        }
                    }
                });
                
                // Apply custom handle size after slider is created
                setTimeout(() => {
                    const handles = priceSlider.querySelectorAll('.noUi-handle');
                    handles.forEach(handle => {
                        handle.style.width = '13.6px';
                        handle.style.height = '13.6px';
                        handle.style.right = '-6.8px';
                        handle.style.top = '-3.8px';
                    });
                }, 10);
                
                // Update Alpine.js values when slider changes
                window.priceRangeSlider.on('update', function(values) {
                    const priceDropdown = document.querySelector('[x-data*="priceOpen"]');
                    if (priceDropdown) {
                        const alpineData = Alpine.$data(priceDropdown);
                        if (alpineData) {
                            alpineData.priceFrom = values[0];
                            alpineData.priceTo = values[1];
                        }
                    }
                });
            }
        }
        
        // Initialize slider after a short delay to ensure DOM is ready
        setTimeout(initPriceSlider, 500);
    });
    
    function filterComponent() {
        return {
            searchQuery: '',
            filters: {
                collection: '',
                price: '',
                skins: '',
                winRate: '',
                level: ''
            },
            isLoading: false,
            gameSlug: '{{ $game->slug }}',
            
            applySearch(term) {
                this.searchQuery = term;
                this.applyFilters();
            },
            
            hasAppliedFilters() {
                return this.searchQuery ||
                       this.filters.collection ||
                       this.filters.price ||
                       this.filters.winRate ||
                       this.filters.level ||
                       this.filters.skins;
            },
            
            clearSearchFilter() {
                this.searchQuery = '';
                this.applyFilters();
            },
            
            clearRankFilter() {
                this.filters.rank = '';
                this.applyFilters();
            },
            
            clearCollectionFilter() {
                this.filters.collection = '';
                this.applyFilters();
            },
            
            clearPriceFilter() {
                this.filters.price = '';
                this.applyFilters();
            },
            
            clearWinRateFilter() {
                this.filters.winRate = '';
                this.applyFilters();
            },
            
            clearLevelFilter() {
                this.filters.level = '';
                this.applyFilters();
            },
            
            clearSkinsFilter() {
                this.filters.skins = '';
                this.applyFilters();
            },
            
            clearAllFilters() {
                this.searchQuery = '';
                this.filters.collection = '';
                this.filters.price = '';
                this.filters.winRate = '';
                this.filters.level = '';
                this.filters.skins = '';
                this.applyFilters();
            },
            
            async applyFilters() {
                // Build query parameters for API request
                const params = new URLSearchParams();
                
                // Add search filter
                if (this.searchQuery) {
                    params.append('filter[search]', this.searchQuery);
                }
                
                // Add collection filter
                if (this.filters.collection) {
                    params.append('filter[collection]', this.filters.collection);
                }
                
                // Add price filter
                if (this.filters.price) {
                    params.append('filter[price]', this.filters.price);
                }
                
                // Add win rate filter
                if (this.filters.winRate) {
                    params.append('filter[win_rate]', this.filters.winRate);
                }
                
                // Add level filter
                if (this.filters.level) {
                    params.append('filter[level]', this.filters.level);
                }
                
                // Add skins filter
                if (this.filters.skins) {
                    params.append('filter[skins]', this.filters.skins);
                }
                
                this.isLoading = true;
                const queryString = params.toString();
                const url = `/api/games/${this.gameSlug}/accounts${queryString ? '?' + queryString : ''}`;
                
                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Failed to fetch accounts');
                    
                    const data = await response.json();
                    this.updateAccountsGrid(data.data);
                } catch (error) {
                    console.error('Filter error:', error);
                } finally {
                    this.isLoading = false;
                }
            },
            
            
            updateAccountsGrid(accounts) {
                const gridContainer = document.querySelector('[data-accounts-grid]');
                if (!gridContainer) return;
                
                if (accounts.length === 0) {
                    gridContainer.innerHTML = `
                        <div class="col-span-full rounded-xl p-12 text-center" style="background-color: #252429; border: 1px solid #2d2c31;">
                            <p class="text-gray-400 text-lg">${'{{ __("messages.no_accounts_available") }}'}</p>
                        </div>
                    `;
                    return;
                }
                
                // Build HTML for account cards
                let html = '';
                accounts.forEach(account => {
                    const mainImage = account.images && account.images.length > 0 
                        ? '/storage/' + account.images[0].url 
                        : '{{ asset("storage/default-account.png") }}';
                    
                    const seller = account.seller;
                    const user = seller ? seller.user : null;
                    const sellerName = user ? user.name : 'Unknown';
                    
                    // Calculate sold count from seller's orders_count field
                    const soldCount = account.seller ? account.seller.orders_count || 0 : 0;
                    
                    // Calculate rating percentage
                    const ratingPercentage = seller && seller.rating > 0 
                        ? Math.round((seller.rating / 5) * 100)
                        : 0;
                    
                    // Price formatting: stored as cents -> show whole DZD with no decimals/separators
                    const priceCents = Number(account.price_dzd || 0);
                    const formattedPrice = Math.round(priceCents / 100).toString();
                    
                    // Build attributes display
                    const attributes = {};
                    if (account.attributes && Array.isArray(account.attributes)) {
                        account.attributes.forEach(attr => {
                            attributes[attr.attribute_key] = attr.attribute_value;
                        });
                    }
                    
                    const collectionTier = attributes['collection_tier'] || '';
                    const collectionTierLabel = translateCollectionTier(collectionTier);
                    const skinsCount = attributes['skins_count'] || '';
                    
                    let tierDisplay = '';
                    if (collectionTier || skinsCount) {
                        tierDisplay = collectionTierLabel;
                        if (collectionTier && skinsCount) {
                            tierDisplay += ' · ';
                        }
                        tierDisplay += skinsCount ? skinsCount + ' Skins' : '';
                    } else {
                        tierDisplay = 'Account Details';
                    }
                    
                    // Build tier image URL
                    let tierImageHtml = '';
                    if (collectionTier) {
                        tierImageHtml = `<img src="/storage/mlbb_skins_rank/${collectionTier}.webp" alt="${collectionTier}" class="object-contain" style="width: 33.6px; height: 33.6px;" onerror="this.style.display='none';">`;
                    }
                    
                    // Build attributes list - no formatting with commas
                    const attributesList = [];
                    if (attributes['skins_count']) {
                        attributesList.push(attributes['skins_count'] + ' Skins');
                    }
                    if (attributes['heroes_count']) {
                        attributesList.push(attributes['heroes_count'] + ' Heroes');
                    }
                    if (attributes['diamonds']) {
                        attributesList.push(attributes['diamonds'] + ' Diamonds');
                    }
                    if (attributes['bp']) {
                        attributesList.push(attributes['bp'] + ' BP');
                    }
                    if (attributes['level']) {
                        attributesList.push('Level ' + attributes['level']);
                    }
                    if (attributes['collection_tier']) {
                        attributesList.push(collectionTierLabel);
                    }
                    
                    const attributesHtml = attributesList.map(attr => 
                        `<span class="inline-block px-2 py-0.5 text-xs whitespace-nowrap" style="color: rgba(255, 255, 255, 0.7); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 6px;">${attr}</span>`
                    ).join('');
                    
                    const sellerPfp = seller && seller.pfp ? '/storage/' + seller.pfp : '{{ asset("storage/examplepfp.webp") }}';
                    const sellerDisplayName = user ? user.name.substring(0, 8).toUpperCase() + (user.name.length > 8 ? '..' : '') : 'Unknown';
                    
                    html += `
                        <a href="/mobile-legends/accounts/${account.id}" class="account-card-hover account-card flex relative flex-col justify-between overflow-hidden rounded-xl h-full hover:shadow-xl transition-all duration-300 group" style="background-color: #0e1015; border: 1px solid #2d2c31;">
                            <!-- Flash Sale Badge (Top Right) -->
                            <div class="absolute z-10" style="top: 0.5rem; right: 0.5rem;">
                                <div class="flex justify-center items-center py-1 w-7 h-7 text-xs font-semibold tracking-wide text-center uppercase rounded-lg" style="color: #fbbf24;">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="flex flex-col flex-1 justify-between px-4 py-4 space-y-4 sm:px-5">
                                <!-- Collection Tier/Skins Section -->
                                <div class="pt-1.5">
                                    <div class="flex items-center gap-x-2">
                                        ${tierImageHtml}
                                        <div class="truncate">
                                            <p class="font-semibold leading-6 truncate text-white" style="font-size: 0.85rem;">
                                                ${tierDisplay}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description (Fixed Height) -->
                                <div class="text-sm line-clamp-2 break-all" style="min-height: 40px; color: rgba(255, 255, 255, 0.8); margin-top: 5px; margin-bottom: 10px;">
                                    ${account.title.substring(0, 100)}${account.title.length > 100 ? '...' : ''}
                                </div>

                                <!-- Account Image -->
                                <div style="margin-bottom: 15px;">
                                    <div class="relative overflow-hidden rounded-lg account-image-hover" style="height: 140px; border: 1px solid #2d2c31;">
                                        <img src="${mainImage}" alt="Account Image" class="object-cover w-full h-full">
                                        ${account.images && account.images.length > 1 ? `
                                            <div type="button" class="inline-flex items-center justify-center transition-colors overflow-hidden font-medium whitespace-nowrap py-1.5 px-2 text-xs rounded-md absolute right-2 bottom-2 backdrop-blur-md" style="background-color: rgba(27, 26, 30, 0.8); color: #ffffff; border: 1px solid #2d2c31;">
                                                <i class="mr-2 fas fa-images"></i> ${account.images.length}+
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>

                                <!-- Account Attributes -->
                                <div class="attributes-scroll overflow-y-auto overflow-x-hidden rounded-md flex flex-wrap gap-1.5" style="height: 60px; margin-left: 5px; margin-right: 5px; padding: 0.5rem; background-color: rgba(27, 26, 30, 0.5); border: 1px solid rgba(255, 255, 255, 0.05);">
                                    ${attributesHtml}
                                </div>

                                <!-- Small Divider -->
                                <div class="h-px w-full" style="background: linear-gradient(90deg, rgba(45, 44, 49, 0.1), #2d2c31, rgba(45, 44, 49, 0.1)); margin-top: 0.5rem; margin-bottom: 0.5rem;"></div>

                                <!-- Price and Buy Button -->
                                <div class="flex relative gap-1 justify-between items-center pt-1">
                                    <div class="flex gap-x-1 items-baseline truncate">
                                        <span class="text-3xl font-bold tracking-tight text-transparent bg-clip-text" style="background: linear-gradient(to left, #ffffff, rgba(255, 255, 255, 0.6)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                            ${formattedPrice}
                                        </span>
                                        <span class="text-sm font-semibold leading-6" style="color: rgba(255, 255, 255, 0.6);">DZD</span>
                                    </div>
                                    <button type="button" class="account-buy-btn inline-flex items-center justify-center transition-colors focus:outline focus:outline-offset-2 focus-visible:outline outline-none disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden font-medium active:translate-y-px whitespace-nowrap bg-red-600 hover:bg-red-700 text-white shadow-sm focus:outline-red-600 py-2 px-4 text-sm rounded-full shrink-0" data-account-id="${account.id}">
                                        <span class="buy-btn-text truncate">Buy Now</span>
                                        <i class="buy-btn-loading ml-1 hidden" style="display: none;">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </i>
                                        <i class="ml-1 fa-solid fa-chevron-right buy-btn-icon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="h-px w-full" style="background: linear-gradient(90deg, rgba(45, 44, 49, 0.1), #2d2c31, rgba(45, 44, 49, 0.1));"></div>

                            <!-- Seller Info (Bottom Section) -->
                            <button class="flex gap-x-2 justify-between items-center px-5 py-3 rounded-b-xl border-t group-hover:bg-opacity-50" style="background-color: rgba(27, 26, 30, 0.5); border-color: #2d2c31; margin-bottom: 15px;">
                                <div class="flex items-center truncate cursor-pointer">
                                    <div class="relative block shrink-0 rounded-full border flex items-center justify-center" style="height: 36px; width: 36px; border-color: #252429; margin-bottom: 5px; margin-right: 5px;">
                                        <img class="object-cover w-full h-full rounded-full" src="${sellerPfp}" alt="${sellerDisplayName}" onerror="this.src='{{ asset("storage/examplepfp.webp") }}';">
                                    </div>
                                    <div class="cursor-default flex items-center truncate gap-x-1.5" data-state="closed">
                                        <div class="truncate text-sm font-medium text-white">${sellerDisplayName}</div>
                                    </div>
                                </div>
                                <div class="flex items-center shrink-0">
                                    <div class="flex items-center text-sm gap-x-2 text-xs" style="color: rgba(255, 255, 255, 0.6);">
                                        <span style="color: rgba(255, 255, 255, 0.6);">${soldCount} Sold</span>
                                        <div data-orientation="horizontal" role="separator" class="shrink-0 w-px" style="height: 1rem; background-color: rgba(255, 255, 255, 0.3);"></div>
                                        <div class="flex items-center" style="color: #10b981; margin-left: 5px;">
                                            <i class="fa-solid fa-thumbs-up" style="color: #10b981; margin-right: 2px;"></i>
                                            <span style="color: #10b981;">${ratingPercentage}%</span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </a>
                    `;
                });
                
                gridContainer.innerHTML = html;
            },
            
            init() {
                // Read filter values from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const searchParam = urlParams.get('filter[search]');
                const collectionParam = urlParams.get('filter[collection]');
                const priceParam = urlParams.get('filter[price]');
                const winRateParam = urlParams.get('filter[win_rate]');
                const levelParam = urlParams.get('filter[level]');
                const skinsParam = urlParams.get('filter[skins]');

                if (searchParam) {
                    this.searchQuery = searchParam;
                }
                if (collectionParam) {
                    this.filters.collection = collectionParam;
                }
                if (priceParam) {
                    this.filters.price = priceParam;
                }
                if (winRateParam) {
                    this.filters.winRate = winRateParam;
                }
                if (levelParam) {
                    this.filters.level = levelParam;
                }
                if (skinsParam) { this.filters.skins = skinsParam; }
                
                // Watch for filter changes
                this.$watch('searchQuery', () => {
                    // Debounce search to avoid too many requests
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => this.applyFilters(), 500);
                });
                
                // Listen for collection dropdown changes
                this.$el.addEventListener('collection-changed', (e) => {
                    this.filters.collection = e.detail || '';
                    this.applyFilters();
                });
                
                // Listen for price filter changes
                this.$el.addEventListener('price-changed', (e) => {
                    this.filters.price = e.detail || '';
                    this.applyFilters();
                });
                
                // Listen for skins filter changes
                this.$el.addEventListener('skins-changed', (e) => {
                    this.filters.skins = e.detail || '';
                    this.applyFilters();
                });
                
                // Listen for additional filters changes
                this.$el.addEventListener('additional-filters-changed', (e) => {
                    if (e.detail) {
                        this.filters.winRate = e.detail.winRate || '';
                        this.filters.level = e.detail.level || '';
                    }
                    this.applyFilters();
                });
            }
        }
    }
</script>
@endpush
@endsection

