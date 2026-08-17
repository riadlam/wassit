<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('icon_url');
        });

        DB::table('games')->update(['is_active' => false]);
        DB::table('games')->where('slug', 'mlbb')->update(['is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
