<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MlbbFandomService
{
    private const WIKI_BASE = 'https://mobile-legends.fandom.com';

    private const API_URL = self::WIKI_BASE.'/api.php';

    private const BATTLE_EMOTES_PAGE = 'Battle emotes';

    private const BATTLE_EFFECTS_PAGE = 'Battle effects';

    /**
     * @return array<string, mixed>
     */
    public function getHeroSkins(string $heroName): array
    {
        $heroName = trim($heroName);
        $wikiPageName = $this->resolveWikiPageName($heroName);
        $cacheKey = 'mlbb.fandom.skins.v11.'.md5(mb_strtolower($wikiPageName));

        return Cache::remember($cacheKey, now()->addHour(), function () use ($heroName, $wikiPageName) {
            $sectionIndex = $this->findSectionIndex(
                $wikiPageName,
                ['Splash_art', 'Splash_arts'],
                ['Splash art', 'Splash arts']
            );

            if ($sectionIndex === null) {
                throw new RuntimeException("No splash art section found for {$heroName} on the Fandom wiki.", 404);
            }

            $html = $this->fetchSectionHtml($wikiPageName, $sectionIndex);
            $items = $this->parseGalleryItems($html);

            $paintedIndex = $this->findSectionIndex(
                $wikiPageName,
                ['Painted_skins', 'Painted_skin'],
                ['Painted skins', 'Painted skin']
            );

            if ($paintedIndex !== null) {
                $paintedHtml = $this->fetchSectionHtml($wikiPageName, $paintedIndex);
                foreach ($this->parseGalleryItems($paintedHtml) as $paintedItem) {
                    $paintedItem['from_painted_section'] = true;
                    $items[] = $paintedItem;
                }
            }

            $items = $this->uniqueGalleryItems($items);

            if ($items === []) {
                throw new RuntimeException("No skins parsed for {$heroName}. The wiki page layout may have changed.", 404);
            }

            $rarities = $this->getHeroSkinRarities($wikiPageName);
            $items = $this->mergeSkinRarities($items, $rarities, $wikiPageName);

            return $this->buildHeroPayload($heroName, $wikiPageName, 'Splash art', 'skins', $items);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getHeroBattleEmotes(string $heroName): array
    {
        $heroName = trim($heroName);
        $wikiPageName = $this->resolveWikiPageName($heroName);
        $cacheKey = 'mlbb.fandom.hero-emotes.'.md5(mb_strtolower($wikiPageName));

        return Cache::remember($cacheKey, now()->addHour(), function () use ($heroName, $wikiPageName) {
            $sectionIndex = $this->findSectionIndex($wikiPageName, ['Battle_emotes'], ['Battle emotes']);

            if ($sectionIndex === null) {
                throw new RuntimeException("No battle emotes section found for {$heroName} on the Fandom wiki.", 404);
            }

            $html = $this->fetchSectionHtml($wikiPageName, $sectionIndex);
            $items = $this->parseGalleryItems($html);

            if ($items === []) {
                throw new RuntimeException("No battle emotes parsed for {$heroName}.", 404);
            }

            return $this->buildHeroPayload($heroName, $wikiPageName, 'Battle emotes', 'emotes', $items);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllBattleEmotes(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget('mlbb.fandom.all-battle-emotes');
        }

        return Cache::remember('mlbb.fandom.all-battle-emotes', now()->addHour(), function () {
            $sections = $this->fetchPageSections(self::BATTLE_EMOTES_PAGE);
            $groups = [];
            $all = [];
            $seen = [];

            foreach ($sections as $section) {
                $line = (string) ($section['line'] ?? '');
                $index = (int) ($section['index'] ?? 0);

                if ($index <= 0 || in_array($line, ['In-game'], true)) {
                    continue;
                }

                $html = $this->fetchSectionHtml(self::BATTLE_EMOTES_PAGE, $index);
                $items = $this->parseBattleEmoteTable($html, $line);

                if ($items === []) {
                    continue;
                }

                $groups[] = [
                    'group' => $line,
                    'count' => count($items),
                    'emotes' => $items,
                ];

                foreach ($items as $item) {
                    $key = mb_strtolower($item['name'].'|'.($item['image_url'] ?? ''));
                    if (isset($seen[$key])) {
                        continue;
                    }

                    $seen[$key] = true;
                    $all[] = $item;
                }
            }

            if ($all === []) {
                throw new RuntimeException('No battle emotes could be parsed from the Fandom wiki.', 404);
            }

            return [
                'source' => 'mobile-legends.fandom.com',
                'wiki_url' => $this->wikiUrl(self::BATTLE_EMOTES_PAGE),
                'section' => 'Battle emotes',
                'emote_count' => count($all),
                'group_count' => count($groups),
                'groups' => $groups,
                'emotes' => $all,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllRecallEffects(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget('mlbb.fandom.all-recall-effects');
        }

        return Cache::remember('mlbb.fandom.all-recall-effects', now()->addHour(), function () {
            $html = $this->fetchPageHtml(self::BATTLE_EFFECTS_PAGE);
            $sectionHtml = $this->extractHtmlBetweenHeadings($html, 'Recall Effects', 'Elimination Effects');
            $parsed = $this->parseRecallEffectsSection($sectionHtml);

            if ($parsed['recalls'] === []) {
                throw new RuntimeException('No recall effects could be parsed from the Fandom wiki.', 404);
            }

            return [
                'source' => 'mobile-legends.fandom.com',
                'wiki_url' => $this->wikiUrl(self::BATTLE_EFFECTS_PAGE).'#Recall_Effects',
                'section' => 'Recall effects',
                'recall_count' => count($parsed['recalls']),
                'group_count' => count($parsed['groups']),
                'groups' => $parsed['groups'],
                'recalls' => $parsed['recalls'],
            ];
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function buildHeroPayload(string $heroName, string $wikiPageName, string $section, string $itemsKey, array $items): array
    {
        return [
            'name' => $heroName,
            'wiki_page' => $wikiPageName,
            'wiki_url' => $this->wikiUrl($wikiPageName),
            'source' => 'mobile-legends.fandom.com',
            'section' => $section,
            $itemsKey => $items,
            $itemsKey.'_count' => count($items),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getHeroSkinRarities(string $wikiPageName): array
    {
        $cacheKey = 'mlbb.fandom.cosmetics.v5.'.md5(mb_strtolower($wikiPageName));

        return Cache::remember($cacheKey, now()->addHour(), function () use ($wikiPageName) {
            $rarities = $this->parseHeroSkinModuleData($wikiPageName);

            try {
                $htmlRarities = $this->parseCosmeticsSkinBoxes($this->fetchPageHtml($wikiPageName.'/Cosmetics'));
                $rarities = $this->overlaySkinRarities($rarities, $htmlRarities);
            } catch (RuntimeException) {
                // Cosmetics page can lag behind Module:Skin/data for upcoming skins.
            }

            $catalog = $this->getSkinCatalogRarities()[$this->normalizeSkinName($wikiPageName)] ?? [];

            return $this->overlaySkinRarities($rarities, $catalog);
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function parseHeroSkinModuleData(string $wikiPageName): array
    {
        $wikitext = Cache::remember('mlbb.fandom.skin-module.v2', now()->addHour(), function () {
            return $this->fetchPageWikitext('Module:Skin/data');
        });

        if (! preg_match('/\["'.preg_quote($wikiPageName, '/').'"\]\s*=\s*/', $wikitext, $match, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $table = $this->extractLuaBraceTable($wikitext, (int) $match[0][1] + strlen($match[0][0]));
        if ($table === null) {
            return [];
        }

        $skinsTable = $this->extractNamedLuaTable($table, 'skins') ?? $table;
        $paintedTable = $this->extractNamedLuaTable($table, 'painted-skins') ?? '';

        return array_merge(
            $this->parseLuaSkinEntries($skinsTable, false),
            $this->parseLuaSkinEntries($paintedTable, true),
        );
    }

    /**
     * @param  array<string, array<string, mixed>>  $base
     * @param  array<string, array<string, mixed>>  $overlay
     * @return array<string, array<string, mixed>>
     */
    private function overlaySkinRarities(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $data) {
            if (! isset($base[$key])) {
                $base[$key] = $data;
                continue;
            }

            $base[$key]['painted'] = (bool) ($base[$key]['painted'] ?? false) || (bool) ($data['painted'] ?? false);
            $base[$key]['border'] = $base[$key]['border'] ?: ($data['border'] ?? null);
            $base[$key]['tags'] = $this->uniqueTags(array_merge($base[$key]['tags'] ?? [], $data['tags'] ?? []));
            $base[$key]['rarity'] = $this->primarySkinRarity(
                $base[$key]['tags'],
                $base[$key]['border'] ?? null,
                $base[$key]['painted']
            );
        }

        return $base;
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function getSkinCatalogRarities(): array
    {
        return Cache::remember('mlbb.fandom.skin-catalog.v4', now()->addHour(), function () {
            $pages = [
                'Collector Skins' => ['tag' => 'Collector', 'tier' => 'Exquisite'],
                'StarLight' => ['tag' => 'Starlight', 'tier' => 'Exceptional'],
                'Exquisite skins' => ['tag' => null, 'tier' => 'Exquisite'],
                'Exceptional skins' => ['tag' => null, 'tier' => 'Exceptional'],
                'Deluxe skins' => ['tag' => null, 'tier' => 'Deluxe'],
                'Grand skins' => ['tag' => null, 'tier' => 'Grand'],
                'Common skins' => ['tag' => null, 'tier' => 'Common'],
                'Supreme skins' => ['tag' => 'Legend', 'tier' => 'Supreme'],
            ];

            $catalog = [];

            foreach ($pages as $page => $meta) {
                try {
                    $wikitext = $this->fetchPageWikitext($page);
                } catch (RuntimeException) {
                    continue;
                }

                preg_match_all('/\[\[([^\]]+)\]\]\s*-\s*\'\'\'([^\']+)\'\'\'/', $wikitext, $wikiLinks, PREG_SET_ORDER);
                preg_match_all('/\'\'\'\s*(.+?)\s+-\s+([^\']+)\s*\'\'\'/', $wikitext, $plainLinks, PREG_SET_ORDER);

                foreach (array_merge($wikiLinks, $plainLinks) as $match) {
                    $hero = $this->normalizeSkinName($match[1]);
                    $skin = $this->normalizeSkinName($match[2]);
                    if ($hero === '' || $skin === '') {
                        continue;
                    }

                    $tags = [];
                    if (! empty($meta['tag'])) {
                        $tags[] = [
                            'name' => $meta['tag'],
                            'image_url' => $this->wikiTagImageUrl($meta['tag']),
                        ];
                    }

                    $entry = [
                        'name' => trim($match[2]),
                        'rarity' => $this->primarySkinRarity($tags, $meta['tier'], false),
                        'border' => $meta['tier'],
                        'painted' => false,
                        'tags' => $tags,
                    ];

                    if (! isset($catalog[$hero][$skin])) {
                        $catalog[$hero][$skin] = $entry;
                        continue;
                    }

                    $existing = $catalog[$hero][$skin];
                    $existing['tags'] = $this->uniqueTags(array_merge($existing['tags'] ?? [], $tags));
                    $existing['border'] = $existing['border'] ?: ($meta['tier'] ?? null);
                    $existing['rarity'] = $this->primarySkinRarity(
                        $existing['tags'],
                        $existing['border'] ?? null,
                        false
                    );
                    $catalog[$hero][$skin] = $existing;
                }
            }

            return $catalog;
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function parseLuaSkinEntries(string $table, bool $painted): array
    {
        if (trim($table) === '') {
            return [];
        }

        $rarities = [];

        if (! preg_match_all('/\["\d+(?:-color\d+)?"\]\s*=\s*/', $table, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        foreach ($matches[0] as $match) {
            $offset = (int) $match[1] + strlen($match[0]);
            $entry = $this->extractLuaBraceTable($table, $offset);
            if ($entry === null || ! str_contains($entry, '["name"]')) {
                continue;
            }

            $fields = $this->parseLuaStringFields($entry);
            $name = trim((string) ($fields['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $tag = $this->normalizeModuleTag((string) ($fields['tag'] ?? ''));
            $tier = trim((string) ($fields['tier'] ?? '')) ?: null;
            $tags = [];

            if ($tag !== '') {
                $tags[] = [
                    'name' => $tag,
                    'image_url' => $this->wikiTagImageUrl($tag),
                ];
            }

            if ($painted) {
                $tags[] = [
                    'name' => 'Painted',
                    'image_url' => null,
                ];
            }

            $key = $this->normalizeSkinName($name);
            $rarities[$key] = [
                'name' => $name,
                'rarity' => $this->primarySkinRarity($tags, $tier, $painted),
                'border' => $tier,
                'painted' => $painted,
                'tags' => $tags,
            ];
        }

        return $rarities;
    }

    /**
     * @return array<string, string>
     */
    private function parseLuaStringFields(string $entry): array
    {
        $fields = [];
        if (preg_match_all('/\["([^"]+)"\]\s*=\s*"([^"]*)"/', $entry, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $fields[$match[1]] = $match[2];
            }
        }

        return $fields;
    }

    private function extractNamedLuaTable(string $source, string $name): ?string
    {
        if (! preg_match('/\["'.preg_quote($name, '/').'"\]\s*=\s*/', $source, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        return $this->extractLuaBraceTable($source, (int) $match[0][1] + strlen($match[0][0]));
    }

    private function extractLuaBraceTable(string $source, int $offset): ?string
    {
        $start = strpos($source, '{', $offset);
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $length = strlen($source);

        for ($i = $start; $i < $length; $i++) {
            $char = $source[$i];
            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }

    private function normalizeModuleTag(string $tag): string
    {
        $tag = trim($tag);
        if ($tag === '') {
            return '';
        }

        $aliases = [
            'starlight' => 'Starlight',
            'neobeast' => 'Neobeasts',
            'm7' => 'M7',
        ];

        return $aliases[mb_strtolower($tag)] ?? $tag;
    }

    public function resolveTagImageUrl(string $tag): string
    {
        $tag = trim($tag);
        if ($tag === '') {
            return '';
        }

        $local = app(MlbbSkinCatalogService::class)->localTagPublicUrl($tag);
        if ($local) {
            return $local;
        }

        $file = $tag.' Skin Tag.png';
        $cacheKey = 'mlbb.fandom.tag-image.v1.'.md5(mb_strtolower($file));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($file) {
            $response = $this->wikiRequest([
                'action' => 'query',
                'titles' => 'File:'.$file,
                'prop' => 'imageinfo',
                'iiprop' => 'url',
                'format' => 'json',
            ]);

            foreach ($response->json('query.pages') ?? [] as $page) {
                $url = $page['imageinfo'][0]['url'] ?? null;
                if (is_string($url) && $url !== '') {
                    return $this->toFullSizeImageUrl($url);
                }
            }

            return self::WIKI_BASE.'/wiki/Special:FilePath/'.rawurlencode($file);
        });
    }

    private function wikiTagImageUrl(string $tag): string
    {
        return $this->resolveTagImageUrl($tag);
    }

    private function fetchPageWikitext(string $pageName): string
    {
        $response = $this->wikiRequest([
            'action' => 'parse',
            'page' => $pageName,
            'prop' => 'wikitext',
            'format' => 'json',
        ]);

        if ($response->json('error.code') === 'missingtitle') {
            throw new RuntimeException("Wiki page \"{$pageName}\" was not found.", 404);
        }

        $text = $response->json('parse.wikitext');

        return is_array($text) ? (string) ($text['*'] ?? '') : (string) $text;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function parseCosmeticsSkinBoxes(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $xpath = $this->makeXPath($html);
        $boxes = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " skin-box ")]');

        if ($boxes === false) {
            return [];
        }

        $rarities = [];

        foreach ($boxes as $box) {
            if (! $box instanceof DOMElement) {
                continue;
            }

            $heading = mb_strtolower($this->nearestHeadingText($box));
            if (str_contains($heading, 'statue')) {
                continue;
            }

            $isPainted = str_contains($heading, 'painted');

            $nameNode = $xpath->query('.//*[contains(@class, "skin-box-name")]', $box)->item(0);
            $name = $this->cleanText($this->nodeText($nameNode));
            if ($name === '') {
                continue;
            }

            $borderImg = $xpath->query('.//*[contains(@class, "skin-box-border")]//img', $box)->item(0);
            $border = $this->parseSkinBorderLabel($borderImg instanceof DOMElement ? $borderImg : null);

            $tags = [];
            foreach ($xpath->query('.//*[contains(@class, "skin-box-tag")]//img', $box) ?: [] as $tagImage) {
                if (! $tagImage instanceof DOMElement) {
                    continue;
                }

                $label = $this->parseSkinTagLabel($tagImage);
                $thumb = $this->resolveImageUrl((string) ($tagImage->getAttribute('data-src') ?: $tagImage->getAttribute('src')));
                if ($label === '') {
                    continue;
                }

                $tags[] = [
                    'name' => $label,
                    'image_url' => $thumb ? $this->toFullSizeImageUrl($thumb) : null,
                ];
            }

            $rarity = $this->primarySkinRarity($tags, $border, $isPainted);
            $key = $this->normalizeSkinName($name);

            $rarities[$key] = [
                'name' => $name,
                'rarity' => $rarity,
                'border' => $border,
                'painted' => $isPainted,
                'tags' => $tags,
            ];
        }

        return $rarities;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, array<string, mixed>>  $rarities
     * @return array<int, array<string, mixed>>
     */
    private function mergeSkinRarities(array $items, array $rarities, string $wikiPageName = ''): array
    {
        foreach ($items as &$item) {
            $fromPaintedSection = (bool) ($item['from_painted_section'] ?? false);
            $match = $this->findSkinRarityMatch((string) ($item['name'] ?? ''), $rarities, $fromPaintedSection)
                ?? $this->skinModuleGap($wikiPageName, (string) ($item['name'] ?? ''))
                ?? [];

            $isPainted = $fromPaintedSection || (bool) ($match['painted'] ?? false);
            $tags = $match['tags'] ?? [];

            if ($isPainted) {
                array_unshift($tags, [
                    'name' => 'Painted',
                    'image_url' => null,
                ]);
                $tags = $this->uniqueTags($tags);
            }

            $item['rarity'] = $isPainted
                ? 'Painted'
                : $this->primarySkinRarity($tags, $match['border'] ?? null, false);
            $item['border'] = $match['border'] ?? null;
            $item['painted'] = $isPainted;
            $item['tags'] = $tags;
            unset($item['from_painted_section']);
        }
        unset($item);

        return $items;
    }

    /**
     * Wiki splash galleries sometimes include skins before Module:Skin/data is updated.
     *
     * @return array<string, mixed>|null
     */
    private function skinModuleGap(string $heroName, string $skinName): ?array
    {
        $gaps = [
            'novaria' => [
                'lunar scion' => ['tag' => 'Special', 'series' => 'M7', 'tier' => 'Exceptional'],
            ],
            'edith' => [
                'verdant knight' => ['tag' => 'Elite', 'tier' => 'Common'],
            ],
        ];

        $heroKey = $this->normalizeSkinName($heroName);
        $skinKey = $this->normalizeSkinName($skinName);
        $gap = $gaps[$heroKey][$skinKey] ?? null;
        if ($gap === null) {
            return null;
        }

        $tags = [];
        foreach (array_filter([$gap['tag'] ?? null, $gap['series'] ?? null]) as $label) {
            $tags[] = [
                'name' => $label,
                'image_url' => $this->wikiTagImageUrl($label),
            ];
        }

        return [
            'name' => $skinName,
            'rarity' => $this->primarySkinRarity($tags, $gap['tier'] ?? null, false),
            'border' => $gap['tier'] ?? null,
            'painted' => false,
            'tags' => $tags,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $rarities
     * @return array<string, mixed>|null
     */
    private function findSkinRarityMatch(string $name, array $rarities, bool $preferPainted): ?array
    {
        $key = $this->normalizeSkinName($name);
        if ($key === '') {
            return null;
        }

        if (isset($rarities[$key])) {
            return $rarities[$key];
        }

        $best = null;
        $bestScore = 0;

        foreach ($rarities as $candidateKey => $data) {
            $candidatePainted = (bool) ($data['painted'] ?? false);
            if ($preferPainted !== $candidatePainted) {
                continue;
            }

            $score = $this->skinNameSimilarity($key, $candidateKey);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $data;
            }
        }

        if ($bestScore >= 86) {
            return $best;
        }

        return $rarities[$key] ?? null;
    }

    private function skinNameSimilarity(string $left, string $right): int
    {
        if ($left === $right) {
            return 100;
        }

        similar_text($left, $right, $percent);

        $distance = levenshtein($left, $right);
        $maxLength = max(strlen($left), strlen($right));
        $levPercent = $maxLength > 0 ? (int) round((1 - ($distance / $maxLength)) * 100) : 0;

        return (int) max($percent, $levPercent);
    }

    /**
     * @param  array<int, array{name: string, image_url: string|null}>  $tags
     */
    private function primarySkinRarity(array $tags, ?string $border, bool $isPainted = false): string
    {
        if ($isPainted) {
            return 'Painted';
        }

        $priority = [
            'Legend', 'Collector', 'Prime', 'Luckybox', 'Annual Starlight',
            'Starlight', 'Epic', 'Special', 'Elite', 'Basic',
        ];

        $tagNames = array_values(array_filter(
            array_map(fn (array $tag) => $tag['name'], $tags),
            fn (string $name) => strcasecmp($name, 'Painted') !== 0
        ));

        foreach ($priority as $wanted) {
            foreach ($tagNames as $name) {
                if (strcasecmp($name, $wanted) === 0) {
                    return $name;
                }
            }
        }

        if ($tagNames !== []) {
            return $tagNames[0];
        }

        return match (mb_strtolower((string) $border)) {
            'supreme' => 'Supreme',
            'grand' => 'Grand',
            'deluxe' => 'Deluxe',
            'exquisite' => 'Exquisite',
            'exceptional' => 'Exceptional',
            'common' => 'Normal',
            default => 'Basic',
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function uniqueGalleryItems(array $items): array
    {
        $unique = [];
        $seen = [];

        foreach ($items as $item) {
            $key = $this->normalizeSkinName((string) ($item['name'] ?? ''));
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $item;
        }

        return $unique;
    }

    /**
     * @param  array<int, array{name: string, image_url: string|null}>  $tags
     * @return array<int, array{name: string, image_url: string|null}>
     */
    private function uniqueTags(array $tags): array
    {
        $unique = [];
        $seen = [];

        foreach ($tags as $tag) {
            $key = mb_strtolower($tag['name'] ?? '');
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $tag;
        }

        return $unique;
    }

    private function parseSkinTagLabel(DOMElement $image): string
    {
        $raw = (string) ($image->getAttribute('data-image-name') ?: $image->getAttribute('alt'));
        $raw = preg_replace('/\.(png|jpg|jpeg|gif|webp)$/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*skin\s*tag$/i', '', $raw) ?? $raw;
        $raw = str_replace('_', ' ', $raw);

        return trim($raw);
    }

    private function parseSkinBorderLabel(?DOMElement $image): ?string
    {
        if ($image === null) {
            return null;
        }

        $raw = (string) ($image->getAttribute('data-image-name') ?: $image->getAttribute('alt'));
        if (preg_match('/skin border\s*\(([^)]+)\)/i', $raw, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    private function nearestHeadingText(DOMElement $node): string
    {
        $current = $node;

        while ($current) {
            $sibling = $current->previousSibling;
            while ($sibling) {
                if ($sibling instanceof DOMElement && in_array(strtolower($sibling->nodeName), ['h1', 'h2', 'h3', 'h4'], true)) {
                    return $this->cleanText($this->nodeText($sibling));
                }
                $sibling = $sibling->previousSibling;
            }
            $current = $current->parentNode instanceof DOMElement ? $current->parentNode : null;
        }

        return '';
    }

    private function normalizeSkinName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name) ?? $name;

        return $name;
    }

    private function resolveWikiPageName(string $heroName): string
    {
        $aliases = [
            'Selina' => 'Selena',
        ];

        return $aliases[$heroName] ?? $heroName;
    }

    private function wikiUrl(string $pageName): string
    {
        return self::WIKI_BASE.'/wiki/'.rawurlencode(str_replace(' ', '_', $pageName));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchPageSections(string $pageName): array
    {
        $response = $this->wikiRequest([
            'action' => 'parse',
            'page' => $pageName,
            'prop' => 'sections',
            'format' => 'json',
        ]);

        if ($response->json('error.code') === 'missingtitle') {
            throw new RuntimeException("Wiki page \"{$pageName}\" was not found.", 404);
        }

        return $response->json('parse.sections', []);
    }

    /**
     * @param  string|array<int, string>  $anchors
     * @param  string|array<int, string>  $lines
     */
    private function findSectionIndex(string $pageName, string|array $anchors, string|array $lines): ?int
    {
        $anchors = (array) $anchors;
        $lines = (array) $lines;

        foreach ($this->fetchPageSections($pageName) as $section) {
            if (in_array($section['anchor'] ?? '', $anchors, true) || in_array($section['line'] ?? '', $lines, true)) {
                return (int) ($section['index'] ?? 0);
            }
        }

        return null;
    }

    private function fetchSectionHtml(string $pageName, int $sectionIndex): string
    {
        $response = $this->wikiRequest([
            'action' => 'parse',
            'page' => $pageName,
            'section' => $sectionIndex,
            'prop' => 'text',
            'format' => 'json',
        ]);

        $text = $response->json('parse.text');

        return is_array($text) ? (string) ($text['*'] ?? '') : (string) $text;
    }

    private function fetchPageHtml(string $pageName): string
    {
        $response = $this->wikiRequest([
            'action' => 'parse',
            'page' => $pageName,
            'prop' => 'text',
            'format' => 'json',
        ]);

        if ($response->json('error.code') === 'missingtitle') {
            throw new RuntimeException("Wiki page \"{$pageName}\" was not found.", 404);
        }

        $text = $response->json('parse.text');

        return is_array($text) ? (string) ($text['*'] ?? '') : (string) $text;
    }

    private function extractHtmlBetweenHeadings(string $html, string $startHeading, string $endHeading): string
    {
        $start = stripos($html, $startHeading);
        if ($start === false) {
            throw new RuntimeException("Could not find \"{$startHeading}\" section on the Fandom wiki.", 404);
        }

        $end = stripos($html, $endHeading, $start + strlen($startHeading));
        $length = $end === false ? null : $end - $start;

        return substr($html, $start, $length ?? strlen($html));
    }

    /**
     * @return array{groups: array<int, array{group: string, count: int, recalls: array<int, array<string, mixed>>}>, recalls: array<int, array<string, mixed>>}
     */
    private function parseRecallEffectsSection(string $html): array
    {
        if (trim($html) === '') {
            return ['groups' => [], 'recalls' => []];
        }

        $xpath = $this->makeXPath($html);
        $groups = [];
        $all = [];
        $seen = [];
        $tier = 'Common';

        foreach ($xpath->query('//*') as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if (in_array($node->nodeName, ['h3', 'h4'], true)) {
                $tier = trim(rtrim($this->nodeText($node), "[] \t\n\r"));
                continue;
            }

            if ($node->nodeName !== 'table' || ! str_contains($node->getAttribute('class'), 'article-table')) {
                continue;
            }

            $items = [];

            foreach ($xpath->query('.//tr', $node) as $row) {
                if (! $row instanceof DOMElement) {
                    continue;
                }

                $cells = $xpath->query('./td', $row);
                if ($cells === false || $cells->length < 2) {
                    continue;
                }

                $name = $this->cleanText($this->nodeText($cells->item(0)));
                if ($name === '' || mb_strtolower($name) === 'name') {
                    continue;
                }

                $image = $xpath->query('.//img', $cells->item(1))->item(0);
                $description = $this->cleanText($this->nodeText($cells->item(1)));

                $thumbnail = $image instanceof DOMElement
                    ? $this->resolveImageUrl((string) ($image->getAttribute('data-src') ?: $image->getAttribute('src')))
                    : null;

                if ($thumbnail === null) {
                    continue;
                }

                $key = mb_strtolower($name.'|'.$thumbnail);
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $item = [
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'group' => $tier,
                    'thumbnail_url' => $thumbnail,
                    'image_url' => $this->toFullSizeImageUrl($thumbnail),
                ];

                $items[] = $item;
                $all[] = $item;
            }

            if ($items !== []) {
                $groups[] = [
                    'group' => $tier,
                    'count' => count($items),
                    'recalls' => $items,
                ];
            }
        }

        return ['groups' => $groups, 'recalls' => $all];
    }

    /**
     * @return array<int, array{name: string, description: string|null, image_url: string, thumbnail_url: string}>
     */
    private function parseGalleryItems(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $xpath = $this->makeXPath($html);
        $images = $xpath->query('//img[contains(@class, "thumbimage") or contains(@class, "mw-file-element")]');

        if ($images === false) {
            return [];
        }

        $items = [];
        $seen = [];

        foreach ($images as $image) {
            if (! $image instanceof DOMElement) {
                continue;
            }

            [$name, $description] = $this->parseCaption(
                (string) ($image->getAttribute('data-caption') ?: $image->getAttribute('alt'))
            );

            if ($name === '') {
                $name = $this->humanizeFileName((string) $image->getAttribute('data-image-name'));
            }

            $thumbnail = $this->resolveImageUrl(
                (string) ($image->getAttribute('data-src') ?: $image->getAttribute('src'))
            );

            if ($name === '' || $thumbnail === null) {
                continue;
            }

            $key = mb_strtolower($name);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $items[] = [
                'name' => $name,
                'description' => $description,
                'thumbnail_url' => $thumbnail,
                'image_url' => $this->toFullSizeImageUrl($thumbnail),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{name: string, description: string|null, heroes: array<int, string>, image_url: string, thumbnail_url: string, group: string}>
     */
    private function parseBattleEmoteTable(string $html, string $group): array
    {
        if (trim($html) === '') {
            return [];
        }

        $xpath = $this->makeXPath($html);
        $rows = $xpath->query('//table[contains(@class, "wikitable")]//tr');

        if ($rows === false) {
            return [];
        }

        $items = [];
        $seen = [];

        foreach ($rows as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }

            $image = $xpath->query('.//img', $row)->item(0);
            if (! $image instanceof DOMElement) {
                continue;
            }

            $name = trim(html_entity_decode(strip_tags((string) $image->getAttribute('alt'))));
            if ($name === '') {
                $name = $this->humanizeFileName((string) $image->getAttribute('data-image-name'));
            }

            $name = preg_replace('/\s*-\s*Battle Emote$/i', '', $name) ?? $name;

            $thumbnail = $this->resolveImageUrl(
                (string) ($image->getAttribute('data-src') ?: $image->getAttribute('src'))
            );

            if ($name === '' || $thumbnail === null) {
                continue;
            }

            $cells = $xpath->query('./td', $row);
            $description = null;
            $heroes = [];

            if ($cells !== false) {
                if ($cells->length >= 2) {
                    $description = $this->cleanText($this->nodeText($cells->item(1)));
                }

                if ($cells->length >= 3) {
                    $heroes = $this->extractHeroNames($cells->item(2), $xpath);
                }
            }

            $key = mb_strtolower($name.'|'.$thumbnail);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $items[] = [
                'name' => $name,
                'description' => $description !== '' ? $description : null,
                'heroes' => $heroes,
                'group' => $group,
                'thumbnail_url' => $thumbnail,
                'image_url' => $this->toFullSizeImageUrl($thumbnail),
            ];
        }

        return $items;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function parseCaption(string $caption): array
    {
        $caption = html_entity_decode(trim($caption));
        if ($caption === '') {
            return ['', null];
        }

        if (str_contains($caption, '<')) {
            $caption = preg_replace('/<\s*br\s*\/?>/i', "\n", $caption) ?? $caption;
            $caption = strip_tags($caption);
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $caption) ?: [])));

        $name = $lines[0] ?? '';
        $description = $lines[1] ?? null;

        return [$name, $description !== '' ? $description : null];
    }

    private function humanizeFileName(string $fileName): string
    {
        $fileName = preg_replace('/\.(png|jpg|jpeg|gif|webp)$/i', '', $fileName) ?? $fileName;
        $fileName = str_replace(['Battle_Emote_-_', 'Battle Emote - '], '', $fileName);
        $fileName = str_replace('_', ' ', $fileName);

        return trim($fileName);
    }

    /**
     * @return array<int, string>
     */
    private function extractHeroNames(?DOMNode $cell, DOMXPath $xpath): array
    {
        if ($cell === null) {
            return [];
        }

        $heroes = [];
        $links = $xpath->query('.//a[@title]', $cell);

        if ($links !== false && $links->length > 0) {
            foreach ($links as $link) {
                $title = trim((string) $link->getAttribute('title'));
                if ($title !== '' && ! str_starts_with($title, 'File:')) {
                    $heroes[] = $title;
                }
            }
        }

        if ($heroes === []) {
            $text = $this->cleanText($this->nodeText($cell));
            if ($text !== '') {
                $heroes[] = $text;
            }
        }

        return array_values(array_unique($heroes));
    }

    private function nodeText(?DOMNode $node): string
    {
        if ($node === null) {
            return '';
        }

        return trim(html_entity_decode(strip_tags($node->textContent ?? '')));
    }

    private function cleanText(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($text))) ?? '');
    }

    private function makeXPath(string $html): DOMXPath
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        return new DOMXPath($document);
    }

    private function resolveImageUrl(string $url): ?string
    {
        $url = trim(html_entity_decode($url));

        if ($url === '' || str_starts_with($url, 'data:')) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return $url;
    }

    private function toFullSizeImageUrl(string $url): string
    {
        return preg_replace('#/revision/latest/scale-to-width-down/\d+#', '/revision/latest', $url) ?? $url;
    }

    private function wikiRequest(array $query): \Illuminate\Http\Client\Response
    {
        $response = Http::timeout(25)
            ->retry(2, 250)
            ->withHeaders([
                'User-Agent' => 'WasitMlbbPlayground/1.0 (Laravel; dev playground)',
            ])
            ->get(self::API_URL, $query);

        if ($response->failed()) {
            throw new RuntimeException('Failed to reach the Fandom wiki API.', 502);
        }

        return $response;
    }
}
