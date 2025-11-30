<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support ALTER ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE `accounts_for_sale` MODIFY COLUMN `status` ENUM('available', 'pending', 'sold', 'cancelled', 'disabled') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'disabled' from enum (but first update any disabled accounts to 'cancelled')
        DB::statement("UPDATE `accounts_for_sale` SET `status` = 'cancelled' WHERE `status` = 'disabled'");
        DB::statement("ALTER TABLE `accounts_for_sale` MODIFY COLUMN `status` ENUM('available', 'pending', 'sold', 'cancelled') DEFAULT 'available'");
    }
};
