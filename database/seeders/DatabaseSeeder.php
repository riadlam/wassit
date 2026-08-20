<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GameSeeder::class,
            GameStaticAttributeSeeder::class,
            UserSeeder::class,
        ]);

        // Missing heroes (Suyou, Lukas, Kalea, Zhuxin) are not in Moonton's list API.
        // After deploy, run once (downloads skin images):
        //   php artisan db:seed --class=MlbbMissingHeroesSkinSeeder
        // Or:
        //   php artisan mlbb:sync-skins --heroes=Suyou --force
    }
}
