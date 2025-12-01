<?php

namespace Database\Seeders;

use App\Models\AccountForSale;
use App\Models\AccountAttribute;
use App\Models\AccountImage;
use App\Models\Game;
use App\Models\Seller;
use Illuminate\Database\Seeder;

class AccountForSaleSeeder extends Seeder
{
    public function run(): void
    {
        $mlbb = Game::where('slug', 'mlbb')->first();
        if (!$mlbb) {
            return;
        }

        $sellers = Seller::take(3)->get();
        if ($sellers->isEmpty()) {
            return;
        }

        $accounts = [
            [
                'title' => 'Flash sale💥skin 164💥max emblems💥CC aldous💥macth aldous 2414💥prime betrik💥cheapest💥full access & guaranteed💥💥💥💥💥',
                'description' => 'Mythical Honor II · 111 Heroes',
                'price_dzd' => 1739,
                'attributes' => [
                    'rank' => 'Mythical Honor II',
                    'heroes_count' => '111',
                    'level' => '74',
                    'skins_count' => '164',
                    'platform' => 'iOS,Android',
                    'bp' => '577662',
                    'win_rate' => '52',
                    'diamonds' => '300',
                ],
                'seller_index' => 0,
            ],
            [
                'title' => 'Big sale🔥skin 138🔥Sasuke suyou🔥neobest pharsa🔥sentrio floryn🔥Akai epic🔥cheapest🔥full access & guaranteed🔥🔥🔥🔥🔥',
                'description' => 'Legend IV · 63 Heroes',
                'price_dzd' => 2319,
                'attributes' => [
                    'rank' => 'Legend IV',
                    'heroes_count' => '63',
                    'level' => '138',
                    'skins_count' => '138',
                    'platform' => 'iOS,Android',
                    'bp' => '450000',
                    'win_rate' => '48',
                    'diamonds' => '150',
                ],
                'seller_index' => 0,
            ],
            [
                'title' => 'Renow Col V🔥182 Skins🔥Highest Rank 117🔥300 Diamond🔥Gold Saint seiya Valir🔥Floryn Sanrio🔥Natann MSC🔥Garaa🔥Karrie Luckybox🔥Annual Star Lesley🔥Max Emblem🔥Acces Full🔥Cheapest and Safe Account',
                'description' => 'Mythical Honor I · 112 Heroes',
                'price_dzd' => 3480,
                'attributes' => [
                    'rank' => 'Mythical Honor I',
                    'heroes_count' => '112',
                    'level' => '74',
                    'skins_count' => '182',
                    'bp' => '27448',
                    'diamonds' => '300',
                    'win_rate' => '53',
                    'platform' => 'iOS,Android',
                    'collection_tier' => 'Renowned Collector',
                ],
                'seller_index' => 1,
            ],
            [
                'title' => 'Best Offers Mega Colector V🔥467 Skins🔥Lunox Legend🔥Fanny Mikasaa🔥Lance Kishin🔥Valir Gold Saint Seiya🔥CHOU KOF🔥Colector Valir x Arlot🔥Acces Full🔥Cheapest and Safe Account',
                'description' => 'Mythical Glory I · 130 Heroes',
                'price_dzd' => 16239,
                'attributes' => [
                    'rank' => 'Mythical Glory I',
                    'heroes_count' => '130',
                    'level' => '127',
                    'skins_count' => '467',
                    'bp' => '14659',
                    'diamonds' => '31',
                    'win_rate' => '52',
                    'platform' => 'iOS,Android',
                    'collection_tier' => 'Mega Collector',
                ],
                'seller_index' => 1,
            ],
            [
                'title' => 'Cheap Exalted Col V🔥336 Skins🔥Gusion KOF🔥Prime Cladue🔥Annual Starlight Haya🔥Max Emblem and Heroo🔥TLPH Recaal🔥Acces Full🔥Cheapest and Safe Account',
                'description' => 'Legend V · 130 Heroes',
                'price_dzd' => 4524,
                'attributes' => [
                    'rank' => 'Legend V',
                    'heroes_count' => '130',
                    'level' => '97',
                    'skins_count' => '336',
                    'bp' => '577662',
                    'win_rate' => '52',
                    'platform' => 'iOS,Android',
                    'diamonds' => '250',
                    'collection_tier' => 'Exalted Collector',
                ],
                'seller_index' => 1,
            ],
        ];

        foreach ($accounts as $accountData) {
            $seller = $sellers[$accountData['seller_index'] % $sellers->count()];
            
            $account = AccountForSale::create([
                'seller_id' => $seller->id,
                'game_id' => $mlbb->id,
                'title' => $accountData['title'],
                'description' => $accountData['description'],
                'price_dzd' => $accountData['price_dzd'],
                'status' => 'available',
            ]);

            // Add attributes
            foreach ($accountData['attributes'] as $key => $value) {
                AccountAttribute::create([
                    'account_id' => $account->id,
                    'attribute_key' => $key,
                    'attribute_value' => $value,
                ]);
            }

            // Add placeholder image
            AccountImage::create([
                'account_id' => $account->id,
                'url' => 'home_games/ml.webp', // Using MLBB image as placeholder
            ]);
        }
    }
}

