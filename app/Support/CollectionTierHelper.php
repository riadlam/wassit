<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class CollectionTierHelper
{
    public static function imageUrl(?string $tier): ?string
    {
        $tier = trim((string) $tier);
        if ($tier === '') {
            return null;
        }

        $relative = 'mlbb_skins_rank/'.$tier.'.webp';
        if (! Storage::disk('public')->exists($relative)) {
            return null;
        }

        return '/storage/mlbb_skins_rank/'.rawurlencode($tier).'.webp';
    }

    /**
     * @return array<string, string>
     */
    public static function badgeMap(): array
    {
        $map = [];
        foreach (['Expert Collector', 'Renowned Collector', 'Exalted Collector', 'Mega Collector', 'World Collector'] as $tier) {
            $url = self::imageUrl($tier);
            if ($url) {
                $map[$tier] = $url;
            }
        }

        return $map;
    }
}
