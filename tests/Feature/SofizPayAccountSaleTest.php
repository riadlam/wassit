<?php

namespace Tests\Feature;

use App\Models\AccountForSale;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SofizPayCibTransaction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SofizPayAccountSaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('buyer');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sellers', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->decimal('wallet', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('accounts_for_sale', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('game_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('price_dzd');
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('account_id');
            $table->integer('amount_dzd');
            $table->string('status')->default('pending');
            $table->string('delivery_status')->nullable();
            $table->unsignedBigInteger('sofizpay_cib_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sofizpay_cib_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->string('transaction_id')->nullable();
            $table->string('cib_order_number')->nullable();
            $table->string('cib_order_id')->nullable();
            $table->decimal('amount_expected', 12, 2);
            $table->string('status')->default('pending');
            $table->json('create_response')->nullable();
            $table->json('last_check_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('account_for_sale_id');
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('buyer_unread_count')->default(0);
            $table->unsignedInteger('seller_unread_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_type');
            $table->string('message_type');
            $table->text('content');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        config([
            'services.sofizpay.enabled' => true,
            'services.sofizpay.merchant_account' => 'TEST-MERCHANT',
            'services.sofizpay.base_url' => 'https://sofizpay.test',
        ]);
    }

    public function test_first_paid_order_sells_listing_and_cancels_competing_orders(): void
    {
        [$account, $firstOrder, $secondOrder, $firstBuyer] = $this->paymentScenario();

        $this->actingAs($firstBuyer)
            ->get(route('payment.sofizpay.cib.return', [
                'eid' => Crypt::encryptString((string) $firstOrder->id),
            ]))
            ->assertRedirect(route('account.chat'));

        $this->assertSame('sold', $account->fresh()->status);
        $this->assertSame('completed', $firstOrder->fresh()->status);
        $this->assertSame('cancelled', $secondOrder->fresh()->status);
        $this->assertDatabaseHas('conversations', [
            'buyer_id' => $firstBuyer->id,
            'seller_id' => $account->seller_id,
            'account_for_sale_id' => $account->id,
        ]);
        $this->assertDatabaseHas('messages', [
            'sender_type' => 'system',
            'content' => 'Payment confirmed for Order #' . $firstOrder->id . '. Seller, please proceed to deliver the account.',
        ]);
    }

    public function test_second_paid_callback_cannot_complete_an_order_for_the_sold_listing(): void
    {
        [$account, $firstOrder, $secondOrder, $firstBuyer, $secondBuyer] = $this->paymentScenario();

        $this->actingAs($firstBuyer)->get(route('payment.sofizpay.cib.return', [
            'eid' => Crypt::encryptString((string) $firstOrder->id),
        ]));

        $this->actingAs($secondBuyer)
            ->get(route('payment.sofizpay.cib.return', [
                'eid' => Crypt::encryptString((string) $secondOrder->id),
            ]))
            ->assertRedirect(route('account.orders'));

        $this->assertSame('sold', $account->fresh()->status);
        $this->assertSame('completed', $firstOrder->fresh()->status);
        $this->assertSame('cancelled', $secondOrder->fresh()->status);
        $this->assertSame(
            'paid_conflict',
            SofizPayCibTransaction::where('order_id', $secondOrder->id)->value('status')
        );
        $this->assertSame(
            1,
            Order::where('account_id', $account->id)->where('status', 'completed')->count()
        );
    }

    private function paymentScenario(): array
    {
        $sellerUser = User::create([
            'name' => 'Seller',
            'email' => 'seller@example.test',
            'password' => bcrypt('password'),
        ]);
        $firstBuyer = User::create([
            'name' => 'Buyer One',
            'email' => 'buyer-one@example.test',
            'password' => bcrypt('password'),
        ]);
        $secondBuyer = User::create([
            'name' => 'Buyer Two',
            'email' => 'buyer-two@example.test',
            'password' => bcrypt('password'),
        ]);

        $seller = Seller::create(['id' => $sellerUser->id]);
        $account = AccountForSale::create([
            'seller_id' => $seller->id,
            'title' => 'Unique game account',
            'price_dzd' => 4000,
            'status' => 'available',
        ]);

        $firstOrder = $this->createOrderWithTransaction($account, $firstBuyer, 'CIB-ONE');
        $secondOrder = $this->createOrderWithTransaction($account, $secondBuyer, 'CIB-TWO');

        Http::fake([
            'https://sofizpay.test/*' => Http::response([
                'order_number' => 'PAID',
                'orderStatus' => 2,
                'errorCode' => 0,
                'errorMessage' => 'Success',
                'respCode' => '00',
                'destination_account' => 'TEST-MERCHANT',
                'Amount' => '4040.00',
            ]),
        ]);

        return [$account, $firstOrder, $secondOrder, $firstBuyer, $secondBuyer];
    }

    private function createOrderWithTransaction(
        AccountForSale $account,
        User $buyer,
        string $orderNumber,
    ): Order {
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $account->seller_id,
            'account_id' => $account->id,
            'amount_dzd' => 4000,
            'status' => 'pending',
        ]);

        $transaction = SofizPayCibTransaction::create([
            'order_id' => $order->id,
            'cib_order_number' => $orderNumber,
            'amount_expected' => 4040,
            'status' => 'pending',
        ]);

        $order->update(['sofizpay_cib_transaction_id' => $transaction->id]);

        return $order->fresh();
    }
}
