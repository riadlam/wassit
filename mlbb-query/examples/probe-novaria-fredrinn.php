<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function wikiGet(array $query): array
{
    return Illuminate\Support\Facades\Http::timeout(25)
        ->withHeaders(['User-Agent' => 'WasitMlbbProbe/1.0'])
        ->get('https://mobile-legends.fandom.com/api.php', $query)
        ->json() ?? [];
}

function captions(string $hero, array $anchors): array
{
    $sections = wikiGet(['action' => 'parse', 'page' => $hero, 'prop' => 'sections', 'format' => 'json'])['parse']['sections'] ?? [];
    $out = [];
    foreach ($sections as $section) {
        if (! in_array($section['anchor'] ?? '', $anchors, true)) {
            continue;
        }
        $html = wikiGet(['action' => 'parse', 'page' => $hero, 'section' => (int) $section['index'], 'prop' => 'text', 'format' => 'json'])['parse']['text']['*'] ?? '';
        preg_match_all('/data-caption="([^"]+)"/', $html, $m);
        $out[$section['line']] = array_values(array_unique(array_map('html_entity_decode', $m[1] ?? [])));
    }

    return $out;
}

function cosmetics(string $hero): array
{
    $html = wikiGet(['action' => 'parse', 'page' => $hero.'/Cosmetics', 'prop' => 'text', 'format' => 'json'])['parse']['text']['*'] ?? '';
    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html);
    $xpath = new DOMXPath($doc);
    $rows = [];
    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " skin-box ")]') as $box) {
        $name = trim($xpath->query('.//*[contains(@class,"skin-box-name")]', $box)->item(0)?->textContent ?? '');
        $heading = '';
        $current = $box;
        while ($current) {
            $sib = $current->previousSibling;
            while ($sib) {
                if ($sib instanceof DOMElement && in_array(strtolower($sib->nodeName), ['h1', 'h2', 'h3'], true)) {
                    $heading = trim($sib->textContent);
                    break 2;
                }
                $sib = $sib->previousSibling;
            }
            $current = $current->parentNode instanceof DOMElement ? $current->parentNode : null;
        }
        $tags = [];
        foreach ($xpath->query('.//*[contains(@class,"skin-box-tag")]//img', $box) as $img) {
            $tags[] = $img->getAttribute('data-image-name') ?: $img->getAttribute('alt');
        }
        $border = $xpath->query('.//*[contains(@class,"skin-box-border")]//img', $box)->item(0)?->getAttribute('data-image-name');
        $rows[] = compact('heading', 'name', 'border', 'tags');
    }

    return $rows;
}

$service = app(App\Services\MlbbFandomService::class);

foreach (['Novaria', 'Fredrinn'] as $hero) {
    echo "======== {$hero} SPLASH ========\n";
    foreach (captions($hero, ['Splash_art', 'Splash_arts', 'Painted_skins']) as $section => $names) {
        echo "{$section}:\n";
        foreach ($names as $n) echo "  splash: {$n}\n";
    }

    echo "\n======== {$hero} COSMETICS ========\n";
    foreach (cosmetics($hero) as $row) {
        echo "  [{$row['heading']}] {$row['name']} | {$row['border']} | ".implode(',', $row['tags'])."\n";
    }

    echo "\n======== {$hero} PARSED ========\n";
    $data = $service->getHeroSkins($hero);
    foreach ($data['skins'] as $skin) {
        $tags = implode(',', array_column($skin['tags'] ?? [], 'name'));
        echo sprintf("%-30s rarity=%-16s painted=%s tags=%s\n", $skin['name'], $skin['rarity'], !empty($skin['painted']) ? 'Y' : 'n', $tags);
    }
    echo "\n";
}
