<?php

namespace Tests\Feature;

use App\Models\AccountForSale;
use App\Models\Game;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SuperDiscountOffer;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperDiscountOfferTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('buyer');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sellers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('pfp')->nullable();
            $table->float('rating')->default(0);
            $table->integer('total_sales')->default(0);
            $table->text('bio')->nullable();
            $table->boolean('verified')->default(false);
            $table->decimal('wallet', 10, 2)->default(0);
            $table->timestamps();
        });

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

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('account_id');
            $table->integer('amount_dzd');
            $table->string('status')->default('pending');
            $table->string('delivery_status')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('super_discount_offers');
        Schema::dropIfExists('accounts_for_sale');
        Schema::dropIfExists('games');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_discounted_price_uses_integer_da_rounding(): void
    {
        $offer = new SuperDiscountOffer(['discount_percentage' => 30]);

        $this->assertSame(7000, $offer->discountedPrice(10000));
        $this->assertSame(7, $offer->discountedPrice(10));
        $this->assertSame(1, $offer->discountedPrice(1));
    }

    public function test_homepage_shows_active_offers_in_sort_order(): void
    {
        [$buyer, $seller, $game] = $this->seedMarketplace();
        $first = $this->createAccount($seller, $game, 'Alpha Account', 10000);
        $second = $this->createAccount($seller, $game, 'Beta Account', 8000);
        $sold = $this->createAccount($seller, $game, 'Sold Account', 5000, 'sold');
        $future = $this->createAccount($seller, $game, 'Future Account', 4000);

        $this->createOffer($first, 20, 2);
        $this->createOffer($second, 50, 1);
        $this->createOffer($sold, 10, 0);
        $this->createOffer($future, 15, 3, [
            'starts_at' => now()->addDay(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Super Discounts')
            ->assertSee('Beta Account')
            ->assertSee('Alpha Account')
            ->assertSee('4000')
            ->assertSee('8000')
            ->assertDontSee('Sold Account')
            ->assertDontSee('Future Account')
            ->assertSeeInOrder(['Beta Account', 'Alpha Account']);
    }

    public function test_order_creation_snapshots_the_discounted_price(): void
    {
        [$buyer, $seller, $game] = $this->seedMarketplace();
        $account = $this->createAccount($seller, $game, 'Deal Account', 10000);
        $this->createOffer($account, 25, 1);

        $this->actingAs($buyer)
            ->postJson(route('orders.create', $account->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'account_id' => $account->id,
            'amount_dzd' => 7500,
            'status' => 'pending',
        ]);
    }

    public function test_existing_pending_order_is_repriced_down_but_never_up(): void
    {
        [$buyer, $seller, $game] = $this->seedMarketplace();
        $account = $this->createAccount($seller, $game, 'Deal Account', 10000);
        $offer = $this->createOffer($account, 10, 1);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'account_id' => $account->id,
            'amount_dzd' => 10000,
            'status' => 'pending',
        ]);

        $this->actingAs($buyer)
            ->postJson(route('orders.create', $account->id))
            ->assertOk();

        $this->assertSame(9000, (int) $order->fresh()->amount_dzd);

        $offer->update([
            'is_active' => false,
        ]);

        $this->actingAs($buyer)
            ->postJson(route('orders.create', $account->id))
            ->assertOk();

        $this->assertSame(9000, (int) $order->fresh()->amount_dzd);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_expired_offer_does_not_change_an_existing_snapshot(): void
    {
        [$buyer, $seller, $game] = $this->seedMarketplace();
        $account = $this->createAccount($seller, $game, 'Deal Account', 10000);
        $this->createOffer($account, 40, 1, [
            'ends_at' => now()->subMinute(),
        ]);

        Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'account_id' => $account->id,
            'amount_dzd' => 6000,
            'status' => 'pending',
        ]);

        $this->actingAs($buyer)
            ->postJson(route('orders.create', $account->id))
            ->assertOk();

        $this->assertDatabaseHas('orders', [
            'account_id' => $account->id,
            'amount_dzd' => 6000,
        ]);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_an_account_cannot_have_two_offers(): void
    {
        [$buyer, $seller, $game] = $this->seedMarketplace();
        $account = $this->createAccount($seller, $game, 'Deal Account', 10000);
        $this->createOffer($account, 10, 1);

        $this->expectException(QueryException::class);

        SuperDiscountOffer::create([
            'account_id' => $account->id,
            'discount_percentage' => 20,
            'image_path' => 'super-discounts/other.webp',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    private function seedMarketplace(): array
    {
        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $sellerUser = User::create([
            'name' => 'Seller',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);

        $seller = Seller::create([
            'id' => $sellerUser->id,
            'rating' => 5,
            'total_sales' => 1,
            'verified' => true,
            'wallet' => 0,
        ]);

        $game = Game::create([
            'name' => 'Mobile Legends',
            'slug' => 'mlbb',
            'is_active' => true,
        ]);

        return [$buyer, $seller, $game];
    }

    private function createAccount(Seller $seller, Game $game, string $title, int $price, string $status = 'available'): AccountForSale
    {
        return AccountForSale::create([
            'seller_id' => $seller->id,
            'game_id' => $game->id,
            'title' => $title,
            'price_dzd' => $price,
            'status' => $status,
        ]);
    }

    private function createOffer(AccountForSale $account, int $percent, int $sort, array $overrides = []): SuperDiscountOffer
    {
        return SuperDiscountOffer::create(array_merge([
            'account_id' => $account->id,
            'discount_percentage' => $percent,
            'image_path' => 'super-discounts/'.$account->id.'.webp',
            'sort_order' => $sort,
            'is_active' => true,
        ], $overrides));
    }
}
