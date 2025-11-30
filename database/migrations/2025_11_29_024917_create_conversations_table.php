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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('account_for_sale_id')->nullable()->constrained('accounts_for_sale')->onDelete('set null');
            $table->timestamp('last_message_at')->nullable();
            $table->integer('buyer_unread_count')->default(0);
            $table->integer('seller_unread_count')->default(0);
            $table->enum('status', ['active', 'archived'])->nullable()->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('last_message_at');
            $table->index(['buyer_id', 'seller_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
