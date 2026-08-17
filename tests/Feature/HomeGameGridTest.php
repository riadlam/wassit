<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomeGameGridTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accounts_for_sale', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('game_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('price_dzd');
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('super_discount_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->unique();
            $table->unsignedTinyInteger('discount_percentage');
            $table->string('image_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('super_discount_offers');
        Schema::dropIfExists('accounts_for_sale');
        Schema::dropIfExists('games');

        parent::tearDown();
    }

    public function test_inactive_games_stay_on_the_homepage_as_coming_soon(): void
    {
        Game::create(['name' => 'Mobile Legends', 'slug' => 'mlbb', 'is_active' => true]);
        Game::create(['name' => 'Valorant', 'slug' => 'valorant', 'is_active' => false]);
        Game::create(['name' => 'Fortnite', 'slug' => 'fortnite', 'is_active' => false]);

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('Valorant');
        $response->assertSee('Fortnite');
        $response->assertSee('Coming Soon');
        $response->assertSee('href="/games/mlbb"', false);
        $response->assertDontSee('href="/games/valorant"', false);
    }
}
