<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('super_discount_offers')) {
            return;
        }

        // Avoid doctrine/dbal ->change(); works on MySQL/MariaDB directly.
        DB::statement('ALTER TABLE super_discount_offers MODIFY image_path VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('super_discount_offers')) {
            return;
        }

        DB::table('super_discount_offers')
            ->whereNull('image_path')
            ->update(['image_path' => '']);

        DB::statement('ALTER TABLE super_discount_offers MODIFY image_path VARCHAR(255) NOT NULL');
    }
};
