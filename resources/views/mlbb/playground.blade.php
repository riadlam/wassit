<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'MLBB Playground' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#121116] text-zinc-100 antialiased" x-data="mlbbPlayground()" x-init="init()">
    <div class="border-b border-white/10 bg-[#1b1a1e]/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-red-400">Wasit Dev Tool</p>
                <h1 class="text-xl font-bold sm:text-2xl">MLBB Playground</h1>
                <p class="mt-1 text-sm text-zinc-400">Skins, battle emotes, and recall effects from the Mobile Legends Fandom wiki.</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs text-zinc-400">
                Route: <code class="text-zinc-200">/mlbb/playground</code>
            </div>
        </div>
    </div>

    <div class="mx-auto grid max-w-7xl gap-4 px-4 py-4 sm:px-6 lg:grid-cols-[320px_minmax(0,1fr)] lg:py-6">
        <aside class="flex flex-col gap-4 rounded-2xl border border-white/10 bg-[#1b1a1e] p-4 shadow-xl shadow-black/20">
            <div class="flex flex-wrap rounded-xl border border-white/10 bg-[#121116] p-1 text-xs">
                <button type="button" @click="setTab('skins')" class="flex-1 rounded-lg px-2 py-2 transition" :class="tab === 'skins' ? 'bg-red-500/20 text-white' : 'text-zinc-400 hover:text-white'">Skins</button>
                <button type="button" @click="setTab('hero-emotes')" class="flex-1 rounded-lg px-2 py-2 transition" :class="tab === 'hero-emotes' ? 'bg-red-500/20 text-white' : 'text-zinc-400 hover:text-white'">Hero Emotes</button>
                <button type="button" @click="setTab('all-emotes')" class="flex-1 rounded-lg px-2 py-2 transition" :class="tab === 'all-emotes' ? 'bg-red-500/20 text-white' : 'text-zinc-400 hover:text-white'">All Emotes</button>
                <button type="button" @click="setTab('recalls')" class="flex-1 rounded-lg px-2 py-2 transition" :class="tab === 'recalls' ? 'bg-red-500/20 text-white' : 'text-zinc-400 hover:text-white'">Recalls</button>
            </div>

            <div x-show="!isCatalogTab">
                <label for="search" class="mb-2 block text-sm font-medium text-zinc-300">Search heroes</label>
                <input
                    id="search"
                    type="search"
                    x-model.debounce.300ms="query"
                    placeholder="Miya, Gusion, Layla..."
                    class="w-full rounded-xl border border-white/10 bg-[#121116] px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                >
            </div>

            <div x-show="tab === 'all-emotes'" class="rounded-xl border border-white/10 bg-[#121116] p-4 text-sm text-zinc-400">
                Global catalog scraped from the
                <a href="https://mobile-legends.fandom.com/wiki/Battle_emotes" target="_blank" rel="noopener noreferrer" class="text-red-300 hover:underline">Battle emotes</a>
                wiki page, grouped by year.
            </div>

            <div x-show="tab === 'recalls'" class="rounded-xl border border-white/10 bg-[#121116] p-4 text-sm text-zinc-400">
                Recall effects scraped from the
                <a href="https://mobile-legends.fandom.com/wiki/Battle_effects#Recall_Effects" target="_blank" rel="noopener noreferrer" class="text-red-300 hover:underline">Battle effects</a>
                wiki page — Fire Crown, Seal of Eternal Flower, and more.
            </div>

            <div x-show="!isCatalogTab" class="flex items-center justify-between text-xs text-zinc-500">
                <span x-text="`${filteredHeroes.length} hero(es)`"></span>
                <button type="button" @click="loadHeroes()" class="rounded-lg px-2 py-1 text-zinc-300 transition hover:bg-white/5 hover:text-white">Refresh</button>
            </div>

            <div x-show="listError" x-cloak class="rounded-xl border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-200" x-text="listError"></div>

            <div x-show="!isCatalogTab" class="min-h-[420px] flex-1 overflow-y-auto rounded-xl border border-white/5 bg-[#121116]">
                <div x-show="loadingList" x-cloak class="flex items-center justify-center p-8">
                    <span class="h-6 w-6 animate-spin rounded-full border-2 border-red-500 border-t-transparent"></span>
                </div>

                <template x-if="filteredHeroes.length === 0 && !loadingList">
                    <div class="p-6 text-center text-sm text-zinc-500">No heroes found.</div>
                </template>

                <ul class="divide-y divide-white/5">
                    <template x-for="heroItem in filteredHeroes" :key="heroItem.id + '-' + heroItem.name">
                        <li>
                            <button
                                type="button"
                                @click="selectHero(heroItem.name)"
                                class="flex w-full items-center gap-3 px-3 py-3 text-left transition hover:bg-white/5"
                                :class="selectedHero === heroItem.name ? 'bg-red-500/10 ring-1 ring-inset ring-red-500/40' : ''"
                            >
                                <img
                                    :src="heroItem.avatar_url || placeholderAvatar"
                                    :alt="heroItem.name"
                                    class="h-11 w-11 rounded-xl border border-white/10 bg-[#1b1a1e] object-cover"
                                    loading="lazy"
                                    @@error="$event.target.src = placeholderAvatar"
                                >
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-medium text-white" x-text="heroItem.name"></p>
                                    <p class="truncate text-xs text-zinc-500">ID <span x-text="heroItem.id"></span></p>
                                </div>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
        </aside>

        <section class="rounded-2xl border border-white/10 bg-[#1b1a1e] p-4 shadow-xl shadow-black/20 sm:p-6 min-h-[520px]">
            <div x-show="needsHeroPick && !loadingDetail" x-cloak class="flex min-h-[520px] flex-col items-center justify-center text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-red-500/10 text-2xl" x-text="tab === 'hero-emotes' ? '😄' : '🎨'"></div>
                <h2 class="text-lg font-semibold text-white">Pick a hero</h2>
                <p class="mt-2 max-w-md text-sm text-zinc-400" x-text="tab === 'hero-emotes' ? 'Select a hero to load their battle emotes from Fandom.' : 'Select a hero to load splash art skins from Fandom.'"></p>
            </div>

            <div x-show="loadingDetail" x-cloak class="flex min-h-[520px] flex-col items-center justify-center text-center">
                <span class="mb-3 h-8 w-8 animate-spin rounded-full border-2 border-red-500 border-t-transparent"></span>
                <p class="text-sm text-zinc-400" x-text="loadingLabel"></p>
                <p class="mt-1 text-xs text-zinc-500" x-show="selectedHero" x-text="selectedHero"></p>
            </div>

            <div x-show="detailError && !loadingDetail" x-cloak class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200" x-text="detailError"></div>

            <div x-show="heroDetail && !loadingDetail && !isCatalogTab" x-cloak class="space-y-6">
                <div class="flex flex-col gap-4 border-b border-white/10 pb-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-red-400" x-text="heroDetail.section"></p>
                        <h2 class="mt-1 text-2xl font-bold text-white" x-text="heroDetail.name"></h2>
                        <p class="mt-2 text-sm text-zinc-400" x-text="heroSummary"></p>
                    </div>
                    <a :href="heroDetail.wiki_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-zinc-200 transition hover:bg-white/10">Open wiki page</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <template x-for="(item, index) in heroItems" :key="selectedHero + '-' + tab + '-' + index + '-' + item.name">
                        <article class="overflow-hidden rounded-2xl border border-white/10 bg-[#121116]">
                            <div class="relative flex aspect-square items-center justify-center overflow-hidden bg-black/20 p-3">
                                <img :src="item.image_url || item.thumbnail_url" :alt="item.name" class="max-h-full max-w-full object-contain" loading="lazy" @@error="$event.target.src = item.thumbnail_url">
                                <div class="absolute left-2 top-2 flex flex-col items-start gap-1" x-show="tab === 'skins' && ((item.tags && item.tags.length) || item.rarity)">
                                    <span
                                        x-show="item.painted"
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 bg-rose-500/20 text-rose-200 ring-rose-400/40"
                                    >Painted</span>
                                    <template x-for="tag in (item.tags || [])" :key="tag.name">
                                        <img x-show="tag.image_url" :src="tag.image_url" :alt="tag.name" class="h-6 max-w-[110px] object-contain drop-shadow" loading="lazy">
                                    </template>
                                    <span
                                        x-show="!item.painted && (!item.tags || !item.tags.some(tag => tag.image_url)) && item.rarity"
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1"
                                        :class="rarityBadgeClass(item.rarity)"
                                        x-text="item.rarity"
                                    ></span>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-semibold text-white" x-text="item.name"></h3>
                                    <span
                                        x-show="tab === 'skins' && item.rarity"
                                        class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1"
                                        :class="rarityBadgeClass(item.rarity)"
                                        x-text="item.rarity"
                                    ></span>
                                </div>
                                <p class="mt-1 text-xs text-zinc-500" x-show="item.description" x-text="item.description"></p>
                            </div>
                        </article>
                    </template>
                </div>

                <details class="rounded-xl border border-white/10 bg-[#121116] p-4">
                    <summary class="cursor-pointer text-sm font-medium text-zinc-300">Raw JSON response</summary>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-black/30 p-3 text-xs text-zinc-300" x-text="JSON.stringify(heroDetail, null, 2)"></pre>
                </details>
            </div>

            <div x-show="catalogDetail && !loadingDetail && isCatalogTab" x-cloak class="space-y-6">
                <div class="flex flex-col gap-4 border-b border-white/10 pb-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-red-400">Fandom Wiki</p>
                        <h2 class="mt-1 text-2xl font-bold text-white" x-text="catalogTitle"></h2>
                        <p class="mt-2 text-sm text-zinc-400">
                            <span x-text="catalogCount"></span>
                            <span x-text="catalogCountLabel"></span> across
                            <span x-text="catalogDetail.group_count"></span> groups
                        </p>
                    </div>
                    <a :href="catalogDetail.wiki_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-zinc-200 transition hover:bg-white/10">Open wiki page</a>
                </div>

                <template x-for="group in catalogDetail.groups || []" :key="group.group">
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-400" x-text="`${group.group} (${group.count})`"></h3>
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <template x-for="(item, index) in group[catalogItemsKey] || []" :key="group.group + '-' + index + '-' + item.name">
                                <article class="overflow-hidden rounded-2xl border border-white/10 bg-[#121116]">
                                    <div class="flex aspect-square items-center justify-center overflow-hidden bg-black/20 p-3">
                                        <img :src="item.image_url || item.thumbnail_url" :alt="item.name" class="max-h-full max-w-full object-contain" loading="lazy" @@error="$event.target.src = item.thumbnail_url">
                                    </div>
                                    <div class="p-3">
                                        <h4 class="text-sm font-semibold text-white" x-text="item.name"></h4>
                                        <p class="mt-1 text-xs text-zinc-500" x-show="item.description" x-text="item.description"></p>
                                        <p class="mt-1 text-xs text-zinc-600" x-show="item.heroes && item.heroes.length" x-text="item.heroes.join(', ')"></p>
                                    </div>
                                </article>
                            </template>
                        </div>
                    </div>
                </template>

                <details class="rounded-xl border border-white/10 bg-[#121116] p-4">
                    <summary class="cursor-pointer text-sm font-medium text-zinc-300">Raw JSON response</summary>
                    <pre class="mt-3 overflow-x-auto rounded-lg bg-black/30 p-3 text-xs text-zinc-300" x-text="JSON.stringify(catalogDetail, null, 2)"></pre>
                </details>
            </div>
        </section>
    </div>

    <script>
        function mlbbPlayground() {
            return {
                tab: 'skins',
                heroes: [],
                query: '',
                selectedHero: null,
                heroDetail: null,
                catalogDetail: null,
                detailRequestId: 0,
                loadingList: false,
                loadingDetail: false,
                listError: '',
                detailError: '',
                placeholderAvatar: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96"><rect width="96" height="96" fill="%231b1a1e"/><text x="48" y="54" text-anchor="middle" fill="%2371717a" font-size="14">MLBB</text></svg>',
                endpoints: {
                    heroes: '/mlbb/playground/heroes',
                    hero: '/mlbb/playground/heroes',
                    emotes: '/mlbb/playground/emotes',
                    recalls: '/mlbb/playground/recalls',
                },
                get isCatalogTab() {
                    return this.tab === 'all-emotes' || this.tab === 'recalls';
                },
                get catalogItemsKey() {
                    return this.tab === 'recalls' ? 'recalls' : 'emotes';
                },
                get catalogTitle() {
                    return this.tab === 'recalls' ? 'All Recall Effects' : 'All Battle Emotes';
                },
                get catalogCount() {
                    if (!this.catalogDetail) return 0;
                    return this.catalogDetail.recall_count ?? this.catalogDetail.emote_count ?? 0;
                },
                get catalogCountLabel() {
                    return this.tab === 'recalls' ? ' recall effect(s)' : ' emote(s)';
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
                    if (name.includes('grand')) return 'bg-orange-400/15 text-orange-200 ring-orange-400/40';
                    if (name.includes('elite')) return 'bg-blue-500/15 text-blue-300 ring-blue-400/40';
                    if (name.includes('normal') || name === 'basic' || name.includes('common')) return 'bg-zinc-500/15 text-zinc-300 ring-zinc-400/30';
                    if (name.includes('special') || name.includes('exceptional')) return 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/40';
                    if (name.includes('exquisite')) return 'bg-violet-500/15 text-violet-300 ring-violet-400/40';
                    return 'bg-zinc-500/15 text-zinc-300 ring-zinc-400/30';
                },
                get filteredHeroes() {
                    const needle = this.query.trim().toLowerCase();
                    if (!needle) return this.heroes;
                    return this.heroes.filter((item) => item.name.toLowerCase().includes(needle));
                },
                get needsHeroPick() {
                    return !this.isCatalogTab && !this.selectedHero;
                },
                get loadingLabel() {
                    if (this.tab === 'recalls') return 'Loading recall effects from Fandom wiki...';
                    if (this.tab === 'all-emotes') return 'Loading all battle emotes from Fandom wiki...';
                    if (this.tab === 'hero-emotes') return 'Fetching hero battle emotes...';
                    return 'Fetching splash art skins...';
                },
                get heroItems() {
                    if (!this.heroDetail) return [];
                    return this.tab === 'hero-emotes'
                        ? (this.heroDetail.emotes || [])
                        : (this.heroDetail.skins || []);
                },
                get heroSummary() {
                    if (!this.heroDetail) return '';
                    if (this.tab === 'hero-emotes') {
                        return `${this.heroDetail.emotes_count || 0} battle emote(s)`;
                    }
                    return `${this.heroDetail.skins_count || 0} splash art skin(s)`;
                },
                async init() {
                    await this.loadHeroes();
                },
                setTab(nextTab) {
                    this.tab = nextTab;
                    this.detailError = '';
                    this.heroDetail = null;

                    if (nextTab === 'all-emotes') {
                        this.loadCatalog(this.endpoints.emotes, 'emotes');
                        return;
                    }

                    if (nextTab === 'recalls') {
                        this.loadCatalog(this.endpoints.recalls, 'recalls');
                        return;
                    }

                    if (this.selectedHero) {
                        this.loadHeroContent(this.selectedHero);
                    }
                },
                async loadHeroes() {
                    this.loadingList = true;
                    this.listError = '';

                    try {
                        const response = await fetch(this.endpoints.heroes);
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Failed to load heroes.');
                        this.heroes = payload.heroes || [];
                    } catch (error) {
                        this.listError = error.message;
                    } finally {
                        this.loadingList = false;
                    }
                },
                selectHero(name) {
                    this.selectedHero = name;
                    this.loadHeroContent(name);
                },
                async loadHeroContent(name) {
                    const requestId = ++this.detailRequestId;
                    this.loadingDetail = true;
                    this.detailError = '';
                    this.heroDetail = null;
                    this.catalogDetail = null;

                    const suffix = this.tab === 'hero-emotes' ? '/emotes' : '';

                    try {
                        const response = await fetch(`${this.endpoints.hero}/${encodeURIComponent(name)}${suffix}`);
                        if (requestId !== this.detailRequestId) return;

                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Failed to load hero data.');

                        this.heroDetail = payload.hero ?? null;
                        if (!this.heroDetail) throw new Error('Empty response from server.');
                    } catch (error) {
                        if (requestId === this.detailRequestId) {
                            this.detailError = error.message;
                            this.heroDetail = null;
                        }
                    } finally {
                        if (requestId === this.detailRequestId) {
                            this.loadingDetail = false;
                        }
                    }
                },
                async loadCatalog(url, itemsKey) {
                    const requestId = ++this.detailRequestId;
                    this.loadingDetail = true;
                    this.detailError = '';
                    this.heroDetail = null;
                    this.catalogDetail = null;

                    try {
                        const response = await fetch(url);
                        if (requestId !== this.detailRequestId) return;

                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Failed to load catalog data.');

                        this.catalogDetail = payload;
                        if (!this.catalogDetail?.[itemsKey]?.length) {
                            throw new Error(`No ${itemsKey} returned.`);
                        }
                    } catch (error) {
                        if (requestId === this.detailRequestId) {
                            this.detailError = error.message;
                            this.catalogDetail = null;
                        }
                    } finally {
                        if (requestId === this.detailRequestId) {
                            this.loadingDetail = false;
                        }
                    }
                },
            };
        }
    </script>
</body>
</html>
