<?php

namespace App\Support;

use App\Models\MlbbSkin;

class SkinsHelper
{
    public static function findByHeroSkin(string $hero, string $skin): ?array
    {
        $hero = trim($hero);
        $skin = trim($skin);
        if ($hero === '' || $skin === '') {
            return null;
        }

        $heroSlug = str_replace(' ', '-', strtolower($hero));
        $skinSlug = str_replace(' ', '-', strtolower($skin));
        $model = MlbbSkin::query()
            ->where('hero_slug', $heroSlug)
            ->where('skin_slug', $skinSlug)
            ->first();

        if (! $model) {
            $model = MlbbSkin::query()
                ->whereRaw('LOWER(hero) = ?', [mb_strtolower($hero)])
                ->whereRaw('LOWER(skin) = ?', [mb_strtolower($skin)])
                ->first();
        }

        if (! $model) {
            return null;
        }

        return [
            'id' => (int) $model->id,
            'role' => strtolower($model->role),
            'hero' => strtolower($model->hero),
            'skin' => strtolower($model->skin),
        ];
    }

    public static function mergeHighlightedSkinIds(string $idsRaw, string $legacyRaw = ''): string
    {
        $ids = [];

        foreach ([$idsRaw, $legacyRaw] as $raw) {
            $normalized = self::normalizeHighlightedSkins(trim($raw));
            if ($normalized === '') {
                continue;
            }
            foreach (explode(',', $normalized) as $id) {
                $id = trim($id);
                if ($id !== '' && ctype_digit($id)) {
                    $ids[] = (int) $id;
                }
            }
        }

        $ids = array_values(array_unique(array_filter($ids, fn (int $id) => $id > 0)));

        return $ids === [] ? '' : implode(',', $ids);
    }

    /**
     * @param  mixed  $query
     */
    public static function applyHighlightedSkinsFilter($query, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $vals = is_array($value) ? $value : explode(',', (string) $value);
        $ids = array_values(array_filter(array_map(function ($v) {
            $v = trim((string) $v);

            return ctype_digit($v) ? (int) $v : null;
        }, $vals), fn ($v) => $v !== null));

        if ($ids === []) {
            return;
        }

        $query->whereHas('attributes', function ($attr) use ($ids) {
            $attr->where('attribute_key', 'highlighted_skins')
                ->where(function ($w) use ($ids) {
                    foreach ($ids as $id) {
                        $w->orWhereRaw('FIND_IN_SET(?, REPLACE(attribute_value, " ", ""))', [(string) $id]);
                    }
                });
        });
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
