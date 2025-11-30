<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('chargily_checkout_id')->nullable()->after('status');
            $table->string('chargily_payment_id')->nullable()->after('chargily_checkout_id');
            $table->timestamp('paid_at')->nullable()->after('chargily_payment_id');
            $table->json('metadata')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['chargily_checkout_id', 'chargily_payment_id', 'paid_at', 'metadata']);
        });
    }
};
