@php
    use App\Support\ListingPosterHelper;

    $frames = $poster['frames'] ?? [];
    $stats = $poster['stats'] ?? [];
    $featuredSkins = $poster['featured_skins'] ?? [];
    $gallerySkins = $poster['gallery_skins'] ?? [];
    $emotes = $poster['emotes'] ?? [];
    $recalls = $poster['recalls'] ?? [];
    $galleryLayout = $poster['gallery_layout'] ?? ['cols' => 6, 'rows' => 1, 'count' => count($gallerySkins)];
    $collectionBadge = $poster['collection_badge_url'] ?? '';
    $primaryImage = $poster['primary_image'] ?? '';
    $posterBg = $poster['poster_bg'] ?? ListingPosterHelper::posterBackgroundUrl($premium);
    $price = $poster['price'] ?? '0';

    $posterWidth = (int) config('listing_poster.width', 681);
    $posterHeight = (int) config('listing_poster.height', 1024);
    $exportWidth = (int) config('listing_poster.export_width', 1080);
    $exportScale = $exportWidth / max($posterWidth, 1);
    $exportHeight = (int) round($posterHeight * $exportScale);

    $absUrl = static function (?string $url): string {
        if (! $url) {
            return '';
        }
        if (str_starts_with($url, 'data:') || str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    };

    $winRate = ($stats['win_rate'] ?? '—') === '—' ? '—' : ($stats['win_rate'].'%');
    $priceTransform = sprintf(
        'rotate(%sdeg) translate(%spx, %spx)',
        $priceConfig['rotate'] ?? -10,
        $priceConfig['translate_x'] ?? 0,
        $priceConfig['translate_y'] ?? 0,
    );
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=681">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        @include('listing.partials.poster-export-styles')
        @if ($premium)
        .lp-price-slot {
            left: {{ $priceConfig['left'] }}px;
            top: {{ $priceConfig['top'] }}px;
            width: {{ $priceConfig['width'] }}px;
            height: {{ $priceConfig['height'] }}px;
        }
        .lp-price-value {
            font-size: {{ $priceConfig['font_size'] }}px;
            transform: {{ $priceTransform }};
            color: #dc2626;
            -webkit-text-fill-color: #dc2626;
            background: none;
            filter: none;
        }
        @else
        .listing-poster.is-basic .lp-price-slot {
            left: {{ $priceConfig['left'] }}px;
            top: {{ $priceConfig['top'] }}px;
            width: {{ $priceConfig['width'] }}px;
            height: {{ $priceConfig['height'] }}px;
        }
        .listing-poster.is-basic .lp-price-value {
            font-size: {{ $priceConfig['font_size'] }}px;
            transform: {{ $priceTransform }};
            color: #dc2626;
            -webkit-text-fill-color: #dc2626;
            background: none;
            filter: none;
        }
        @endif
    </style>
</head>
<body style="margin:0;padding:0;background:#c80000;width:{{ $exportWidth }}px;height:{{ $exportHeight }}px;overflow:hidden;">
<div style="transform:scale({{ $exportScale }});transform-origin:top left;width:{{ $posterWidth }}px;height:{{ $posterHeight }}px;">
<div class="listing-poster {{ $premium ? 'is-premium' : 'is-basic' }}">
    <img class="lp-bg" src="{{ $absUrl($posterBg) }}" alt="">

    @if ($premium)
        <div class="lp-featured">
            @foreach ($featuredSkins as $idx => $skin)
                <div class="lp-skin">
                    <div class="lp-frame-viewport">
                        <img src="{{ $absUrl($skin['image_url'] ?? '') }}" alt="{{ $skin['name'] ?? '' }}" style="{{ ListingPosterHelper::frameStyle($frames, 'feat-'.$idx) }}">
                    </div>
                    <div class="lp-skin-tags">
                        @if (! empty($skin['painted']))
                            <span class="lp-tag-painted">Painted</span>
                        @endif
                        @foreach (($skin['tags'] ?? []) as $tag)
                            @if (! empty($tag['image_url']))
                                <img src="{{ $absUrl($tag['image_url']) }}" alt="{{ $tag['name'] ?? '' }}" class="lp-tag-img">
                            @endif
                        @endforeach
                        @if (empty($skin['painted']) && empty(collect($skin['tags'] ?? [])->firstWhere('image_url')) && ! empty($skin['rarity']))
                            <span class="lp-rarity {{ ListingPosterHelper::rarityClass($skin['rarity'] ?? '') }}">{{ $skin['rarity'] }}</span>
                        @endif
                    </div>
                    <div class="lp-skin-meta">
                        <p class="lp-skin-name">{{ $skin['name'] ?? '' }}</p>
                        <p class="lp-hero-name">{{ $skin['hero'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="lp-primary">
        <div class="lp-frame-viewport">
            <img src="{{ $primaryImage }}" alt="Primary account screenshot" style="{{ ListingPosterHelper::frameStyle($frames, 'primary') }}">
        </div>
        <span class="lp-primary-watermark">Wassitmarket</span>
        @if ($collectionBadge !== '')
            <div class="lp-collection-badge">
                <img class="lp-collection-badge-icon" src="{{ $absUrl($collectionBadge) }}" alt="Collection badge">
            </div>
        @endif
    </div>

    <div class="lp-effects">
        <p class="lp-effects-title">{{ $premium ? 'BATTLE EFFECTS' : 'EMOTES' }}</p>
        <div class="lp-effects-grid">
            @foreach ($emotes as $item)
                <div class="lp-effect">
                    <img src="{{ $absUrl($item['image_url'] ?? '') }}" alt="{{ $item['name'] ?? '' }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="lp-stats">
        <div class="lp-stat">
            <span class="lp-stat-val">{{ $winRate }}</span>
            <span class="lp-stat-lbl">WIN RATE</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-val">{{ $stats['heroes_count'] ?? '—' }}</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-val">{{ $stats['level'] ?? '—' }}</span>
            <span class="lp-stat-lbl">LEVEL</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-val">{{ $stats['skins_count'] ?? '—' }}</span>
        </div>
        <div class="lp-stat">
            <span class="lp-stat-val">{{ $stats['rank'] ?? '—' }}</span>
            <span class="lp-stat-lbl">HIGHEST RANK</span>
        </div>
    </div>

    <div class="lp-recalls">
        <p class="lp-recalls-title">RECALLS</p>
        <div class="lp-recalls-row">
            @foreach ($recalls as $recall)
                <div class="lp-recall">
                    <img src="{{ $absUrl($recall['image_url'] ?? '') }}" alt="{{ $recall['name'] ?? '' }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="lp-gallery" @if (! $premium) style="display:flex;flex-wrap:wrap;align-content:flex-start;gap:4px;" @endif>
        @foreach ($gallerySkins as $idx => $skin)
            @php
                $tileClass = '';
                if (! $premium) {
                    $cols = (int) ($galleryLayout['cols'] ?? 1);
                    if ($cols <= 3) {
                        $tileClass = 'is-large-tile';
                    } elseif ($cols <= 5) {
                        $tileClass = 'is-medium-tile';
                    }
                }
                $tileStyle = $premium ? '' : ListingPosterHelper::gallerySkinStyle($idx, $galleryLayout);
            @endphp
            <div class="lp-skin {{ $tileClass }}" @if ($tileStyle) style="{{ collect($tileStyle)->map(fn ($v, $k) => "$k:$v")->implode(';') }}" @endif>
                <div class="lp-frame-viewport">
                    <img src="{{ $absUrl($skin['image_url'] ?? '') }}" alt="{{ $skin['name'] ?? '' }}" style="{{ ListingPosterHelper::frameStyle($frames, 'bot-'.$idx) }}">
                </div>
                <div class="lp-skin-tags">
                    @if (! empty($skin['painted']))
                        <span class="lp-tag-painted">Painted</span>
                    @endif
                    @foreach (($skin['tags'] ?? []) as $tag)
                        @if (! empty($tag['image_url']))
                            <img src="{{ $absUrl($tag['image_url']) }}" alt="{{ $tag['name'] ?? '' }}" class="lp-tag-img">
                        @endif
                    @endforeach
                    @if (empty($skin['painted']) && empty(collect($skin['tags'] ?? [])->firstWhere('image_url')) && ! empty($skin['rarity']))
                        <span class="lp-rarity {{ ListingPosterHelper::rarityClass($skin['rarity'] ?? '') }}">{{ $skin['rarity'] }}</span>
                    @endif
                </div>
                <div class="lp-skin-meta">
                    <p class="lp-skin-name">{{ $skin['name'] ?? '' }}</p>
                    <p class="lp-hero-name">{{ $skin['hero'] ?? '' }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lp-price-slot">
        <span class="lp-price-value">{{ $price }}</span>
    </div>
</div>
</div>
</body>
</html>
