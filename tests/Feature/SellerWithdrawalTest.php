<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SellerWithdrawalTest extends TestCase
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

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_seller_can_submit_a_withdrawal_request(): void
    {
        [$user] = $this->createSeller(5000);

        $this->actingAs($user)
            ->post(route('account.wallet.withdrawals.store'), [
                'amount' => 1500,
                'payment_method' => 'ccp',
                'account_holder' => 'Seller Name',
                'account_number' => '1234567890-12',
            ])
            ->assertRedirect(route('account.wallet'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('withdrawals', [
            'seller_id' => $user->id,
            'amount' => 1500,
            'status' => 'pending',
            'payment_method' => 'ccp',
        ]);
    }

    public function test_pending_requests_cannot_exceed_available_wallet(): void
    {
        [$user, $seller] = $this->createSeller(5000);

        $seller->withdrawals()->create([
            'amount' => 4000,
            'status' => 'pending',
            'payment_method' => 'ccp',
        ]);

        $this->actingAs($user)
            ->from(route('account.wallet'))
            ->post(route('account.wallet.withdrawals.store'), [
                'amount' => 1500,
                'payment_method' => 'baridimob',
                'account_holder' => 'Seller Name',
                'account_number' => '1234567890',
            ])
            ->assertRedirect(route('account.wallet'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('withdrawals', 1);
    }

    public function test_buyer_cannot_submit_a_withdrawal_request(): void
    {
        $buyer = User::create([
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $this->actingAs($buyer)
            ->post(route('account.wallet.withdrawals.store'), [
                'amount' => 1500,
                'payment_method' => 'ccp',
                'account_holder' => 'Buyer',
                'account_number' => '1234567890',
            ])
            ->assertRedirect(route('account.index'));

        $this->assertDatabaseCount('withdrawals', 0);
    }

    public function test_security_headers_are_added_to_web_responses(): void
    {
        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_telegram_webhook_fails_closed_without_a_secret(): void
    {
        putenv('TELEGRAM_BOT_TOKEN=test-token');
        putenv('TELEGRAM_CHAT_ID=12345');
        putenv('TELEGRAM_WEBHOOK_SECRET');

        try {
            $this->postJson(route('telegram.webhook'), [
                'callback_query' => [
                    'from' => ['id' => 12345],
                    'data' => 'ap:1',
                ],
            ])->assertServiceUnavailable();
        } finally {
            putenv('TELEGRAM_BOT_TOKEN');
            putenv('TELEGRAM_CHAT_ID');
            putenv('TELEGRAM_WEBHOOK_SECRET');
        }
    }

    public function test_legacy_get_approval_routes_are_removed(): void
    {
        $this->assertFalse(Route::has('partner.application.approve'));
        $this->assertFalse(Route::has('partner.application.reject'));
    }

    private function createSeller(float $wallet): array
    {
        $user = User::create([
            'name' => 'Seller Name',
            'email' => uniqid('seller-', true) . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);

        $seller = Seller::create([
            'id' => $user->id,
            'rating' => 5,
            'total_sales' => 0,
            'verified' => true,
            'wallet' => $wallet,
        ]);

        return [$user, $seller];
    }
}
