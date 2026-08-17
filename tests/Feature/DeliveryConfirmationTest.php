<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Events\PaymentStatusUpdated;
use App\Models\AccountForSale;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeliveryConfirmationTest extends TestCase
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

        Schema::create('accounts_for_sale', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('game_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price_dzd', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('account_for_sale_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('buyer_unread_count')->default(0);
            $table->unsignedInteger('seller_unread_count')->default(0);
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_type')->nullable();
            $table->string('message_type')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount_dzd', 10, 2);
            $table->string('status')->default('pending');
            $table->string('delivery_status')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('accounts_for_sale');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_delivery_confirmation_credits_the_seller_wallet_once(): void
    {
        Event::fake([MessageSent::class, PaymentStatusUpdated::class]);

        [$buyer, $seller, $conversation] = $this->createPaidOrder(1000);

        $this->actingAs($buyer)
            ->postJson(route('account.chat.confirm-delivery', $conversation->id))
            ->assertOk()
            ->assertJson(['deliveryStatus' => 'delivered']);

        $this->actingAs($buyer)
            ->postJson(route('account.chat.confirm-delivery', $conversation->id))
            ->assertStatus(400);

        $this->assertEquals(961.0, (float) $seller->fresh()->wallet);
        $this->assertSame('delivered', Order::query()->first()->delivery_status);
    }

    public function test_non_buyer_cannot_confirm_delivery(): void
    {
        Event::fake([MessageSent::class, PaymentStatusUpdated::class]);

        [$buyer, $seller, $conversation] = $this->createPaidOrder(1000);
        $otherUser = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $this->actingAs($otherUser)
            ->postJson(route('account.chat.confirm-delivery', $conversation->id))
            ->assertForbidden();

        $this->assertEquals(0.0, (float) $seller->fresh()->wallet);
        $this->assertNotSame('delivered', Order::query()->first()->delivery_status);
    }

    private function createPaidOrder(float $amount): array
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

        $account = AccountForSale::create([
            'seller_id' => $seller->id,
            'title' => 'MLBB Account',
            'price_dzd' => $amount,
            'status' => 'sold',
        ]);

        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'account_for_sale_id' => $account->id,
            'last_message_at' => now(),
        ]);

        Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'account_id' => $account->id,
            'amount_dzd' => $amount,
            'status' => 'completed',
            'delivery_status' => 'pending',
        ]);

        return [$buyer, $seller, $conversation];
    }
}
