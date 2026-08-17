<?php

namespace App\Support;

final class SellerRanks
{
    /**
     * Rank order also controls which rank is used as the seller subtitle.
     *
     * @return array<string, array<string, string>>
     */
    public static function definitions(): array
    {
        return [
            'power' => [
                'label_key' => 'messages.seller_rank_power',
                'icon' => 'fa-crown',
                'color' => '#fbbf24',
                'gradient' => 'linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%)',
                'shadow' => '0 2px 4px rgba(251, 191, 36, 0.4)',
            ],
            'trusted' => [
                'label_key' => 'messages.seller_rank_trusted',
                'icon' => 'fa-shield-halved',
                'color' => '#60a5fa',
                'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%)',
                'shadow' => '0 2px 4px rgba(59, 130, 246, 0.4)',
            ],
            'verified' => [
                'label_key' => 'messages.seller_rank_verified',
                'icon' => 'fa-check',
                'color' => '#3b82f6',
                'gradient' => 'linear-gradient(135deg, #60a5fa 0%, #3b82f6 30%, #2563eb 70%, #1d4ed8 100%)',
                'shadow' => '0 2px 4px rgba(59, 130, 246, 0.4)',
                'border' => '1.5px solid rgba(96, 165, 250, 0.4)',
            ],
            'elite' => [
                'label_key' => 'messages.elite_seller',
                'icon' => 'fa-gem',
                'color' => '#c4b5fd',
                'gradient' => 'linear-gradient(135deg, #a78bfa 0%, #7c3aed 55%, #5b21b6 100%)',
                'shadow' => '0 2px 4px rgba(124, 58, 237, 0.4)',
            ],
        ];
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::definitions())
            ->mapWithKeys(fn (array $rank, string $key): array => [
                $key => __($rank['label_key']),
            ])
            ->all();
    }

    /**
     * @param  array<int, string>|null  $assigned
     * @return array<int, string>
     */
    public static function normalize(?array $assigned): array
    {
        $assigned = $assigned ?: ['elite'];

        return collect(array_keys(self::definitions()))
            ->filter(fn (string $rank): bool => in_array($rank, $assigned, true))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>|null  $assigned
     * @return array<int, array<string, string>>
     */
    public static function badges(?array $assigned): array
    {
        $definitions = self::definitions();

        return collect(self::normalize($assigned))
            ->map(function (string $type) use ($definitions): array {
                $rank = $definitions[$type];

                return [
                    'type' => $type,
                    'label' => __($rank['label_key']),
                    'icon' => $rank['icon'],
                    'color' => $rank['color'],
                    'gradient' => $rank['gradient'],
                    'shadow' => $rank['shadow'],
                    'border' => $rank['border'] ?? '1.5px solid rgba(255, 255, 255, 0.3)',
                ];
            })
            ->all();
    }
}
