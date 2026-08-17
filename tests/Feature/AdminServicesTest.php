<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\SellerApplication;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Admin\SellerApplicationService;
use App\Services\Admin\WithdrawalService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminServicesTest extends TestCase
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

        Schema::create('seller_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('country');
            $table->string('business_name')->nullable();
            $table->string('website')->nullable();
            $table->string('experience');
            $table->string('games');
            $table->string('preferred_location')->nullable();
            $table->string('account_count');
            $table->string('status')->default('pending');
            $table->string('telegram_message')->nullable();
            $table->text('admin_notes')->nullable();
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
        Schema::dropIfExists('seller_applications');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_seller_application_service_approves_and_creates_seller(): void
    {
        $user = User::create([
            'name' => 'Applicant',
            'email' => 'applicant@example.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $application = SellerApplication::create([
            'user_id' => $user->id,
            'full_name' => 'Applicant',
            'email' => 'applicant@example.com',
            'phone' => '0555555555',
            'country' => 'DZ',
            'experience' => '1-3',
            'games' => 'mlbb',
            'account_count' => '1-10',
            'status' => 'pending',
        ]);

        app(SellerApplicationService::class)->approve($application, 'Looks good');

        $user->refresh();
        $application->refresh();

        $this->assertSame('seller', $user->role);
        $this->assertSame('approved', $application->status);
        $this->assertSame('Looks good', $application->admin_notes);
        $this->assertTrue(Seller::query()->whereKey($user->id)->exists());
    }

    public function test_withdrawal_service_approves_and_deducts_wallet(): void
    {
        $user = User::create([
            'name' => 'Seller',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);

        $seller = Seller::create([
            'id' => $user->id,
            'rating' => 5,
            'total_sales' => 0,
            'verified' => true,
            'wallet' => 5000,
        ]);

        $withdrawal = Withdrawal::create([
            'seller_id' => $seller->id,
            'amount' => 1500,
            'status' => 'pending',
            'payment_method' => 'CCP',
        ]);

        app(WithdrawalService::class)->approve($withdrawal, 'Paid out');

        $seller->refresh();
        $withdrawal->refresh();

        $this->assertSame('approved', $withdrawal->status);
        $this->assertEquals(3500.0, (float) $seller->wallet);
        $this->assertNotNull($withdrawal->processed_at);
    }
}
