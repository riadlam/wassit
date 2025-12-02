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
        // Extend enum to include 'system'
        Schema::table('messages', function (Blueprint $table) {
            $table->enum('sender_type', ['buyer', 'seller', 'system'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        Schema::table('messages', function (Blueprint $table) {
            $table->enum('sender_type', ['buyer', 'seller'])->change();
        });
    }
};
