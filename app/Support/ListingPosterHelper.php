<?php

namespace App\Support;

use App\Models\User;

class ListingPosterHelper
{
    public static function userHasPremiumLayout(?User $user): bool
    {
        $email = strtolower(trim((string) ($user?->email ?? '')));

        return in_array($email, array_map('strtolower', config('listing_poster.premium_emails', [])), true);
    }

    public static function posterBackgroundUrl(bool $premium): string
    {
        $file = $premium ? 'images/listing-poster-bg.png' : 'images/listing-poster-bg-basic.jpg';

        return asset($file);
    }

    /**
     * @return array{cols: int, rows: int, count: int}
     */
    public static function galleryGridForCount(int $count): array
    {
        $n = max(0, min(48, $count));
        if ($n <= 0) {
            return ['cols' => 8, 'rows' => 6, 'count' => 0];
        }
        if ($n === 1) {
            return ['cols' => 1, 'rows' => 1, 'count' => 1];
        }

        $areaW = 661;
        $areaH = 568;
        $maxCols = min(8, $n);
        $maxRows = 6;
        $best = null;

        for ($cols = 1; $cols <= $maxCols; $cols++) {
            $rows = (int) ceil($n / $cols);
            if ($rows > $maxRows) {
                continue;
            }

            $cellW = ($areaW - ($cols - 1) * 4) / $cols;
            $cellH = ($areaH - ($rows - 1) * 4) / $rows;
            $aspect = $cellW / max($cellH, 1);
            $aspectPenalty = abs(log($aspect / 0.78));
            $emptyPenalty = ($cols * $rows - $n) * 0.4;
            $sizeBonus = log(max($cellW * $cellH, 1)) * 0.15;
            $score = $sizeBonus - $aspectPenalty - $emptyPenalty;

            if ($best === null || $score > $best['score']) {
                $best = ['cols' => $cols, 'rows' => $rows, 'score' => $score];
            }
        }

        if ($best === null) {
            $cols = min(8, $n);

            return [
                'cols' => $cols,
                'rows' => min(6, (int) ceil($n / $cols)),
                'count' => $n,
            ];
        }

        return ['cols' => $best['cols'], 'rows' => $best['rows'], 'count' => $n];
    }

    /**
     * @param  array<string, mixed>  $layout
     * @return array<string, string>
     */
    public static function gallerySkinStyle(int $idx, array $layout): array
    {
        $count = (int) ($layout['count'] ?? 0);
        $cols = max(1, (int) ($layout['cols'] ?? 1));
        $rows = max(1, (int) ($layout['rows'] ?? 1));
        if ($count <= 0) {
            return [];
        }

        $gap = 4;
        $row = (int) floor($idx / $cols);
        $lastRowCount = $count - $cols * ($rows - 1);
        $itemsInRow = $row === $rows - 1 ? $lastRowCount : $cols;
        $width = 'calc((100% - '.(($itemsInRow - 1) * $gap)."px) / {$itemsInRow})";
        $height = 'calc((100% - '.(($rows - 1) * $gap)."px) / {$rows})";

        return [
            'flex' => "0 0 {$width}",
            'width' => $width,
            'height' => $height,
        ];
    }

    public static function frameStyle(array $frames, string $key): string
    {
        $frame = $frames[$key] ?? ['x' => 0, 'y' => 0, 'scale' => 1];
        $x = (float) ($frame['x'] ?? 0);
        $y = (float) ($frame['y'] ?? 0);
        $scale = (float) ($frame['scale'] ?? 1);

        return "transform: translate({$x}px, {$y}px) scale({$scale});";
    }

    public static function rarityClass(?string $rarity): string
    {
        $value = strtolower(trim((string) $rarity));

        return match (true) {
            str_contains($value, 'prime') => 'is-prime',
            str_contains($value, 'collector') => 'is-collector',
            str_contains($value, 'special') => 'is-special',
            str_contains($value, 'star') => 'is-star',
            str_contains($value, 'elite') => 'is-elite',
            default => '',
        };
    }
}
