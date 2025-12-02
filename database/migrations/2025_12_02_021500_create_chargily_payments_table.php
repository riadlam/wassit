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
        Schema::create('chargily_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('provider')->default('chargily');
            $table->string('checkout_id')->nullable()->index();
            $table->string('status')->index();
            $table->string('event')->nullable();
            $table->integer('amount_dzd')->nullable();
            $table->string('currency')->nullable();
            $table->string('signature')->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargily_payments');
    }
};
