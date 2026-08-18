<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_images', function (Blueprint $table) {
            $table->boolean('is_cover')->default(false)->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('account_images', function (Blueprint $table) {
            $table->dropColumn('is_cover');
        });
    }
};
