<?php

use App\Models\SuperDiscountOffer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('super_discount_offers', function (Blueprint $table) {
            $table->unsignedInteger('compare_at_price')->default(0)->after('account_id');
        });

        // Convert old "% off listing price" into:
        // - compare_at_price = previous listing price (shown struck through)
        // - listing price_dzd = previous discounted sale price
        SuperDiscountOffer::query()
            ->with('account')
            ->orderBy('id')
            ->each(function (SuperDiscountOffer $offer): void {
                $account = $offer->account;
                if (! $account) {
                    return;
                }

                $oldListPrice = (int) $account->price_dzd;
                $pct = max(1, min(99, (int) ($offer->getAttributes()['discount_percentage'] ?? $offer->discount_percentage ?? 10)));
                $oldSalePrice = (int) round($oldListPrice * (100 - $pct) / 100);
                if ($oldListPrice > 1) {
                    $oldSalePrice = max(1, min($oldListPrice - 1, $oldSalePrice));
                } else {
                    $oldSalePrice = max(1, $oldListPrice);
                }

                DB::table('super_discount_offers')
                    ->where('id', $offer->id)
                    ->update(['compare_at_price' => max($oldListPrice, $oldSalePrice + 1)]);

                if ((int) $account->price_dzd !== $oldSalePrice) {
                    DB::table('accounts_for_sale')
                        ->where('id', $account->id)
                        ->update(['price_dzd' => $oldSalePrice]);
                }
            });

        Schema::table('super_discount_offers', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('super_discount_offers', function (Blueprint $table) {
            $table->unsignedTinyInteger('discount_percentage')->default(10)->after('account_id');
        });

        SuperDiscountOffer::query()
            ->with('account')
            ->orderBy('id')
            ->each(function (SuperDiscountOffer $offer): void {
                $account = $offer->account;
                if (! $account) {
                    return;
                }

                $sale = (int) $account->price_dzd;
                $compare = max((int) $offer->compare_at_price, $sale + 1);
                $pct = $compare > 0
                    ? (int) max(1, min(99, round((($compare - $sale) / $compare) * 100)))
                    : 10;

                DB::table('super_discount_offers')
                    ->where('id', $offer->id)
                    ->update(['discount_percentage' => $pct]);

                DB::table('accounts_for_sale')
                    ->where('id', $account->id)
                    ->update(['price_dzd' => $compare]);
            });

        Schema::table('super_discount_offers', function (Blueprint $table) {
            $table->dropColumn('compare_at_price');
        });
    }
};
