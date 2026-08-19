<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccountForSale extends Model
{
    protected $table = 'accounts_for_sale';
    
    protected $fillable = [
        'seller_id',
        'game_id',
        'title',
        'description',
        'price_dzd',
        'status',
    ];

    /**
     * Get the seller that owns this account.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Get the game for this account.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Get the attributes for this account.
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(AccountAttribute::class, 'account_id');
    }

    /**
     * Get the images for this account (cover first).
     */
    public function images(): HasMany
    {
        return $this->hasMany(AccountImage::class, 'account_id')
            ->orderByDesc('is_cover')
            ->orderBy('id');
    }

    /**
     * Seller-uploaded gallery photos (excludes the generated listing poster).
     */
    public function galleryImages(): HasMany
    {
        return $this->hasMany(AccountImage::class, 'account_id')
            ->where('is_cover', false)
            ->orderBy('id');
    }

    /**
     * Get the orders for this account.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'account_id');
    }

    public function superDiscountOffer(): HasOne
    {
        return $this->hasOne(SuperDiscountOffer::class, 'account_id');
    }

    public function currentDiscountOffer(): ?SuperDiscountOffer
    {
        $offer = $this->superDiscountOffer;

        if (! $offer || ! $offer->isCurrentlyActive() || $this->status !== 'available') {
            return null;
        }

        return $offer;
    }

    public function effectivePrice(): int
    {
        $offer = $this->currentDiscountOffer();

        if ($offer) {
            return $offer->discountedPrice((int) $this->price_dzd);
        }

        return (int) $this->price_dzd;
    }

    public function coverImage(): ?AccountImage
    {
        return $this->images->firstWhere('is_cover', true) ?? $this->images->first();
    }

    /**
     * Gallery images with the generated listing poster first when present.
     *
     * @return \Illuminate\Support\Collection<int, AccountImage>
     */
    public function displayImages()
    {
        return $this->images->values();
    }
}
