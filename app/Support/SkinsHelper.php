<?php

namespace App\Support;

use App\Models\MlbbSkin;

class SkinsHelper
{
    public static function findByHeroSkin(string $hero, string $skin): ?array
    {
        $heroSlug = str_replace(' ', '-', strtolower(trim($hero)));
        $skinSlug = str_replace(' ', '-', strtolower(trim($skin)));
        $model = MlbbSkin::query()
            ->where('hero_slug', $heroSlug)
            ->where('skin_slug', $skinSlug)
            ->first();
        if (!$model) return null;
        return [
            'id' => (int)$model->id,
            'role' => strtolower($model->role),
            'hero' => strtolower($model->hero),
            'skin' => strtolower($model->skin),
        ];
    }

    public static function normalizeHighlightedSkins(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') return '';

        // Already IDs: digits and commas only
        if (preg_match('/^\s*\d+(\s*,\s*\d+)*\s*$/', $raw)) {
            $ids = array_filter(array_map('trim', explode(',', $raw)), fn($v) => $v !== '');
            return implode(',', $ids);
        }

        // Legacy: hero - skin entries separated by | or ,
        $delim = str_contains($raw, '|') ? '|' : (str_contains($raw, ',') ? ',' : '|');
        $parts = array_filter(array_map('trim', explode($delim, $raw)), fn($v) => $v !== '');
        $ids = [];
        foreach ($parts as $pair) {
            $bits = array_map('trim', preg_split('/\s*-\s*/', $pair));
            if (count($bits) >= 2) {
                $hero = strtolower($bits[0]);
                $skin = strtolower(implode(' - ', array_slice($bits, 1)));
                $found = self::findByHeroSkin($hero, $skin);
                if ($found) $ids[] = (string)$found['id'];
            }
        }
        return implode(',', array_unique($ids));
    }
}
