<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $games = [
            [
                'name' => 'Mobile Legends',
                'slug' => 'mlbb',
                'icon_url' => '/storage/home_games/mobile-legends.png',
            ],
            [
                'name' => 'PUBG Mobile',
                'slug' => 'pubg-mobile',
                'icon_url' => '/storage/home_games/pubg-mobile.png',
            ],
            [
                'name' => 'Free Fire',
                'slug' => 'free-fire',
                'icon_url' => '/storage/home_games/free-fire.png',
            ],
            [
                'name' => 'Call of Duty Mobile',
                'slug' => 'cod-mobile',
                'icon_url' => '/storage/home_games/call-of-duty-mobile.png',
            ],
            [
                'name' => 'Clash of Clans',
                'slug' => 'coc',
                'icon_url' => '/storage/home_games/clash-of-clans.png',
            ],
            [
                'name' => 'Valorant',
                'slug' => 'valorant',
                'icon_url' => '/storage/home_games/valorant.png',
            ],
            [
                'name' => 'League of Legends',
                'slug' => 'lol',
                'icon_url' => '/storage/home_games/league-of-legends.png',
            ],
            [
                'name' => 'Genshin Impact',
                'slug' => 'genshin-impact',
                'icon_url' => '/storage/home_games/genshin-impact.png',
            ],
            [
                'name' => 'Fortnite',
                'slug' => 'fortnite',
                'icon_url' => '/storage/home_games/fortnite.png',
            ],
            [
                'name' => 'Apex Legends',
                'slug' => 'apex-legends',
                'icon_url' => '/storage/home_games/apex-legends.png',
            ],
            [
                'name' => 'Counter-Strike 2',
                'slug' => 'cs2',
                'icon_url' => '/storage/home_games/counter-strike-2.png',
            ],
            [
                'name' => 'Rocket League',
                'slug' => 'rocket-league',
                'icon_url' => '/storage/home_games/rocket-league.png',
            ],
            [
                'name' => 'FIFA Mobile',
                'slug' => 'fifa-mobile',
                'icon_url' => '/storage/home_games/ea-sports-fc-mobile.png',
            ],
            [
                'name' => 'Wild Rift',
                'slug' => 'wild-rift',
                'icon_url' => '/storage/home_games/lol-wild-rift.png',
            ],
            [
                'name' => 'Brawl Stars',
                'slug' => 'brawl-stars',
                'icon_url' => '/storage/home_games/brawl-stars.png',
            ],
        ];

        foreach ($games as $gameData) {
            $game = Game::updateOrCreate(
                ['slug' => $gameData['slug']],
                $gameData
            );
        }
    }
}
