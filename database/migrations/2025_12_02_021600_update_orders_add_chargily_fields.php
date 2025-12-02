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
            if (!Schema::hasColumn('orders', 'chargily_checkout_id')) {
                $table->string('chargily_checkout_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'chargily_payment_id')) {
                $table->foreignId('chargily_payment_id')->nullable()->after('chargily_checkout_id')->constrained('chargily_payments')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('chargily_payment_id');
            }
            if (!Schema::hasColumn('orders', 'metadata')) {
                $table->json('metadata')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('orders', 'chargily_payment_id')) {
                $table->dropConstrainedForeignId('chargily_payment_id');
            }
            if (Schema::hasColumn('orders', 'chargily_checkout_id')) {
                $table->dropColumn('chargily_checkout_id');
            }
        });
    }
};
