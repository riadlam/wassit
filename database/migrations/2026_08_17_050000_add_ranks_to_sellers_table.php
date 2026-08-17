<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->json('ranks')->nullable()->after('verified');
        });

        DB::table('sellers')
            ->select(['id', 'verified'])
            ->orderBy('id')
            ->each(function (object $seller): void {
                $ranks = ['elite'];
                $completedSales = DB::table('orders')
                    ->where('seller_id', $seller->id)
                    ->where('status', 'completed')
                    ->count();

                if ((bool) $seller->verified) {
                    $ranks[] = 'verified';
                }

                if ($completedSales >= 20) {
                    $ranks[] = 'trusted';
                }

                if ($completedSales >= 50) {
                    $ranks[] = 'power';
                }

                DB::table('sellers')
                    ->where('id', $seller->id)
                    ->update(['ranks' => json_encode($ranks)]);
            });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('ranks');
        });
    }
};
