@if($superDiscountOffers->isNotEmpty())
    @php
        $soonestEnd = $superDiscountOffers
            ->filter(fn ($offer) => $offer->ends_at !== null)
            ->sortBy('ends_at')
            ->first()?->ends_at;
    @endphp

    @once
        <style>
            .sd-track {
                display: flex;
                gap: 1rem;
                overflow-x: auto;
                overflow-y: hidden;
                padding-top: 1.5rem;
                padding-bottom: 1.5rem;
                align-items: stretch;
                scroll-snap-type: x mandatory;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            .sd-track::-webkit-scrollbar { display: none; }
            .sd-slide {
                width: 260px;
                flex-shrink: 0;
                scroll-snap-align: start;
            }
            @media (min-width: 640px) { .sd-slide { width: 300px; } }
            @media (min-width: 768px) { .sd-slide { width: 340px; } }
            .sd-card {
                display: block;
                width: 100%;
                height: 100%;
                border-radius: 0.5rem;
                border: 1px solid #EC1F3E;
                background: rgba(255, 255, 255, 0.04);
                backdrop-filter: blur(6px);
                padding: 0.75rem;
                transition: background-color .25s ease, transform .25s ease;
            }
            @media (min-width: 640px) { .sd-card { padding: 1rem 1.25rem; } }
            .sd-card:hover {
                background: rgba(236, 31, 62, 0.12);
                transform: translateY(-2px);
            }
            .sd-thumb {
                position: relative;
                width: 100%;
                overflow: hidden;
                border-radius: 0.375rem;
                background: #0e1015;
            }
            .sd-thumb img {
                display: block;
                width: 100%;
                height: auto;
            }
            .sd-ribbon {
                position: relative;
                display: flex;
                align-items: center;
                width: 100%;
                height: 2.75rem;
                border-radius: 0.375rem;
                background: linear-gradient(90deg, rgba(236, 31, 62, 0.85) 0%, rgba(236, 31, 62, 0.45) 45%, rgba(236, 31, 62, 0) 100%);
            }
            @media (min-width: 1024px) { .sd-ribbon { height: 4rem; } }
            .sd-timer {
                height: 18px;
                font-size: 9px;
                background: linear-gradient(103.06deg, #F3491B -8.3%, #F3491B 19.29%, #FDB37F 51.87%, #EC1F3E 82.25%, #EC1F3E 116.43%);
            }
            @media (min-width: 1024px) {
                .sd-timer { height: 2rem; font-size: 1rem; }
            }
            .sd-arrow {
                display: none;
                align-items: center;
                justify-content: center;
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 9999px;
                border: 1px solid rgba(236, 31, 62, 0.5);
                background: rgba(8, 9, 14, 0.85);
                color: #fff;
                transition: background-color .2s ease;
            }
            .sd-arrow:hover { background: #EC1F3E; }
            @media (min-width: 1024px) { .sd-arrow { display: inline-flex; } }
        </style>
    @endonce

    <section class="relative z-10 w-full px-4 mx-auto max-w-7xl sm:px-6 xl:px-8 pt-6 pb-2"
             x-data="{
                 scrollTrack(direction) {
                     const track = this.$refs.track;
                     const rtl = getComputedStyle(track).direction === 'rtl';
                     track.scrollBy({ left: direction * (rtl ? -1 : 1) * track.clientWidth * 0.8, behavior: 'smooth' });
                 }
             }">
        <div class="sd-ribbon">
            <div class="flex items-center justify-center flex-shrink-0 w-8 h-8 ml-3 rounded-full lg:w-12 lg:h-12 lg:ml-4" style="background: rgba(8, 9, 14, 0.55);">
                <svg class="w-4 h-4 lg:w-6 lg:h-6" viewBox="0 0 24 24" fill="#FDB37F" aria-hidden="true">
                    <path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z"/>
                </svg>
            </div>

            <h2 class="ml-3 text-sm font-extrabold text-white lg:text-xl">{{ __('messages.super_discount_title') }}</h2>

            <div class="sd-timer flex items-center px-2 ml-4 font-semibold text-white rounded-sm lg:ml-7 lg:px-4"
                 x-data="{
                     label: @js($soonestEnd ? null : __('messages.super_discount_kicker')),
                     endsAt: @js($soonestEnd?->getTimestamp()),
                     init() {
                         if (! this.endsAt) return;
                         const pad = (value) => String(value).padStart(2, '0');
                         const tick = () => {
                             const seconds = Math.max(0, Math.floor((this.endsAt * 1000 - Date.now()) / 1000));
                             const days = Math.floor(seconds / 86400);
                             const hours = Math.floor((seconds % 86400) / 3600);
                             const label = pad(hours) + ' : ' + pad(Math.floor((seconds % 3600) / 60)) + ' : ' + pad(seconds % 60);
                             this.label = days > 0 ? days + 'd ' + label : label;
                         };
                         tick();
                         setInterval(tick, 1000);
                     }
                 }">
                <span x-text="label" class="whitespace-nowrap">{{ $soonestEnd ? '00 : 00 : 00' : __('messages.super_discount_kicker') }}</span>
            </div>

            <div class="items-center hidden gap-2 ml-auto mr-4 lg:flex">
                <button type="button" class="sd-arrow" aria-label="Previous" x-on:click="scrollTrack(-1)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button type="button" class="sd-arrow" aria-label="Next" x-on:click="scrollTrack(1)">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="sd-track" x-ref="track">
            @foreach($superDiscountOffers as $offer)
                @php
                    $account = $offer->account;
                    $game = $account?->game;
                    $originalPrice = (int) ($account->price_dzd ?? 0);
                    $salePrice = $offer->discountedPrice($originalPrice);
                    $accountUrl = $game && $account
                        ? route('accounts.show', ['slug' => $game->publicSlug(), 'id' => $account->id])
                        : '#';
                @endphp
                <div class="sd-slide">
                    <a href="{{ $accountUrl }}" class="sd-card group">
                        <div class="sd-thumb">
                            <img
                                src="{{ $offer->imageUrl() }}"
                                alt="{{ $account->title ?? __('messages.super_discount_title') }}"
                                loading="lazy"
                            >
                        </div>

                        <h3 class="mt-3 text-sm font-semibold text-white sm:text-base md:text-lg line-clamp-2">{{ $account->title }}</h3>

                        <p class="my-2 text-xs font-semibold line-through" style="color: #FF2F4E;">{{ number_format($originalPrice, 0, '.', '') }} DA</p>

                        <p class="text-sm font-semibold text-white sm:text-base md:text-lg">{{ number_format($salePrice, 0, '.', '') }} DA</p>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
