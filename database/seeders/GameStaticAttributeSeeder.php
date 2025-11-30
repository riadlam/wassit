<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\GameStaticAttribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameStaticAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mobile Legends attributes
        $mlbb = Game::where('slug', 'mlbb')->first();
        if ($mlbb) {
            $mlbbAttributes = [
                ['attribute_key' => 'level', 'attribute_label' => 'Level', 'type' => 'number', 'options' => null],
                ['attribute_key' => 'rank', 'attribute_label' => 'Rank', 'type' => 'select', 'options' => null],
                [
                    'attribute_key' => 'collection_tier', 
                    'attribute_label' => 'Collection Tier', 
                    'type' => 'select',
                    'options' => [
                        'Expert Collector',
                        'Renowned Collector',
                        'Exalted Collector',
                        'Mega Collector',
                        'World Collector'
                    ]
                ],
                ['attribute_key' => 'skins_count', 'attribute_label' => 'Skins Count', 'type' => 'number', 'options' => null],
                ['attribute_key' => 'emblems_count', 'attribute_label' => 'Emblems Count', 'type' => 'number', 'options' => null],
                ['attribute_key' => 'heroes_count', 'attribute_label' => 'Heroes Count', 'type' => 'number', 'options' => null],
                ['attribute_key' => 'win_rate', 'attribute_label' => 'Win Rate (%)', 'type' => 'number', 'options' => null],
                ['attribute_key' => 'matches_played', 'attribute_label' => 'Matches Played', 'type' => 'number', 'options' => null],
            ];

            foreach ($mlbbAttributes as $attr) {
                GameStaticAttribute::firstOrCreate(
                    [
                        'game_id' => $mlbb->id,
                        'attribute_key' => $attr['attribute_key'],
                    ],
                    [
                        'game_id' => $mlbb->id,
                        'attribute_key' => $attr['attribute_key'],
                        'attribute_label' => $attr['attribute_label'],
                        'type' => $attr['type'],
                        'options' => $attr['options'] ? json_encode($attr['options']) : null,
                    ]
                );
            }
        }

        // PUBG Mobile attributes
        $pubg = Game::where('slug', 'pubg-mobile')->first();
        if ($pubg) {
            $pubgAttributes = [
                ['attribute_key' => 'level', 'attribute_label' => 'Level', 'type' => 'number'],
                ['attribute_key' => 'tier', 'attribute_label' => 'Tier', 'type' => 'select'],
                ['attribute_key' => 'skins_count', 'attribute_label' => 'Skins Count', 'type' => 'number'],
                ['attribute_key' => 'uc_balance', 'attribute_label' => 'UC Balance', 'type' => 'number'],
                ['attribute_key' => 'kd_ratio', 'attribute_label' => 'K/D Ratio', 'type' => 'number'],
                ['attribute_key' => 'matches_played', 'attribute_label' => 'Matches Played', 'type' => 'number'],
            ];

            foreach ($pubgAttributes as $attr) {
                GameStaticAttribute::firstOrCreate(
                    [
                        'game_id' => $pubg->id,
                        'attribute_key' => $attr['attribute_key'],
                    ],
                    [
                        'game_id' => $pubg->id,
                        'attribute_key' => $attr['attribute_key'],
                        'attribute_label' => $attr['attribute_label'],
                        'type' => $attr['type'],
                    ]
                );
            }
        }

        // Free Fire attributes
        $freefire = Game::where('slug', 'free-fire')->first();
        if ($freefire) {
            $freefireAttributes = [
                ['attribute_key' => 'level', 'attribute_label' => 'Level', 'type' => 'number'],
                ['attribute_key' => 'rank', 'attribute_label' => 'Rank', 'type' => 'select'],
                ['attribute_key' => 'diamonds', 'attribute_label' => 'Diamonds', 'type' => 'number'],
                ['attribute_key' => 'skins_count', 'attribute_label' => 'Skins Count', 'type' => 'number'],
                ['attribute_key' => 'characters_count', 'attribute_label' => 'Characters Count', 'type' => 'number'],
            ];

            foreach ($freefireAttributes as $attr) {
                GameStaticAttribute::firstOrCreate(
                    [
                        'game_id' => $freefire->id,
                        'attribute_key' => $attr['attribute_key'],
                    ],
                    [
                        'game_id' => $freefire->id,
                        'attribute_key' => $attr['attribute_key'],
                        'attribute_label' => $attr['attribute_label'],
                        'type' => $attr['type'],
                    ]
                );
            }
        }

        // Call of Duty Mobile attributes
        $cod = Game::where('slug', 'cod-mobile')->first();
        if ($cod) {
            $codAttributes = [
                ['attribute_key' => 'level', 'attribute_label' => 'Level', 'type' => 'number'],
                ['attribute_key' => 'rank', 'attribute_label' => 'Rank', 'type' => 'select'],
                ['attribute_key' => 'cp_balance', 'attribute_label' => 'CP Balance', 'type' => 'number'],
                ['attribute_key' => 'weapons_count', 'attribute_label' => 'Weapons Count', 'type' => 'number'],
                ['attribute_key' => 'kd_ratio', 'attribute_label' => 'K/D Ratio', 'type' => 'number'],
            ];

            foreach ($codAttributes as $attr) {
                GameStaticAttribute::firstOrCreate(
                    [
                        'game_id' => $cod->id,
                        'attribute_key' => $attr['attribute_key'],
                    ],
                    [
                        'game_id' => $cod->id,
                        'attribute_key' => $attr['attribute_key'],
                        'attribute_label' => $attr['attribute_label'],
                        'type' => $attr['type'],
                    ]
                );
            }
        }

        // Clash of Clans attributes
        $coc = Game::where('slug', 'coc')->first();
        if ($coc) {
            $cocAttributes = [
                ['attribute_key' => 'town_hall_level', 'attribute_label' => 'Town Hall Level', 'type' => 'number'],
                ['attribute_key' => 'trophies', 'attribute_label' => 'Trophies', 'type' => 'number'],
                ['attribute_key' => 'gems', 'attribute_label' => 'Gems', 'type' => 'number'],
                ['attribute_key' => 'builder_hall_level', 'attribute_label' => 'Builder Hall Level', 'type' => 'number'],
            ];

            foreach ($cocAttributes as $attr) {
                GameStaticAttribute::firstOrCreate(
                    [
                        'game_id' => $coc->id,
                        'attribute_key' => $attr['attribute_key'],
                    ],
                    [
                        'game_id' => $coc->id,
                        'attribute_key' => $attr['attribute_key'],
                        'attribute_label' => $attr['attribute_label'],
                        'type' => $attr['type'],
                    ]
                );
            }
        }
    }
}
