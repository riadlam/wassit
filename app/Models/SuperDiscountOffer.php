<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperDiscountOffer extends Model
{
    protected $fillable = [
        'account_id',
        'discount_percentage',
        'image_path',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'discount_percentage' => 'integer',
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

    public function originalPrice(?int $originalPrice = null): int
    {
        return $originalPrice ?? (int) ($this->account?->price_dzd ?? 0);
    }

    public function discountedPrice(?int $originalPrice = null): int
    {
        $original = $this->originalPrice($originalPrice);
        $percentage = max(1, min(99, (int) $this->discount_percentage));
        $discounted = (int) round($original * (100 - $percentage) / 100);

        if ($original <= 1) {
            return max(1, $original);
        }

        return max(1, min($original - 1, $discounted));
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
        $cover = $this->account?->images?->firstWhere('is_cover', true)
            ?? $this->account?->images?->first();

        if ($cover) {
            return asset('storage/'.ltrim(str_replace('\\', '/', (string) $cover->url), '/'));
        }

        return asset('images/listing-poster-bg-basic.jpg');
    }
}
