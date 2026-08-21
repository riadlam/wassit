<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperDiscountOffer extends Model
{
    protected $fillable = [
        'account_id',
        'compare_at_price',
        'image_path',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'compare_at_price' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountForSale::class, 'account_id');
    }

    public function scopeActiveNow(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $inner) use ($now) {
                $inner->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $inner) use ($now) {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeForHomepage(Builder $query): Builder
    {
        return $query
            ->activeNow()
            ->whereHas('account', function (Builder $accountQuery) {
                $accountQuery->where('status', 'available');
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }

    /**
     * Higher “was” price shown struck through in the UI.
     */
    public function compareAtPrice(): int
    {
        return max(0, (int) $this->compare_at_price);
    }

    /**
     * Current sale price buyers pay (the listing price).
     */
    public function salePrice(?int $listingPrice = null): int
    {
        return max(0, $listingPrice ?? (int) ($this->account?->price_dzd ?? 0));
    }

    /**
     * @deprecated Use compareAtPrice()
     */
    public function originalPrice(?int $originalPrice = null): int
    {
        $compare = $this->compareAtPrice();

        return $compare > 0 ? $compare : (int) ($originalPrice ?? $this->salePrice());
    }

    /**
     * @deprecated Use salePrice()
     */
    public function discountedPrice(?int $originalPrice = null): int
    {
        return $this->salePrice();
    }

    public function showsDiscount(): bool
    {
        return $this->compareAtPrice() > $this->salePrice();
    }

    public function imageUrl(): string
    {
        $path = ltrim(str_replace('\\', '/', (string) $this->image_path), '/');

        if ($path !== '') {
            return asset('storage/'.$path);
        }

        return $this->accountCoverImageUrl();
    }

    public function hasCustomImage(): bool
    {
        return trim((string) $this->image_path) !== '';
    }

    public function accountCoverImageUrl(): string
    {
        // Prefer the generated listing poster (is_cover / display index 0), else first gallery image.
        $cover = $this->account?->coverImage()
            ?? $this->account?->images?->first();

        if ($cover) {
            return asset('storage/'.ltrim(str_replace('\\', '/', (string) $cover->url), '/'));
        }

        return asset('images/listing-poster-bg-basic.jpg');
    }
}
