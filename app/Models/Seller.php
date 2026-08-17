<?php

namespace App\Models;

use App\Support\SellerRanks;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = [
        'id',
        'pfp',
        'rating',
        'total_sales',
        'bio',
        'verified',
        'ranks',
        'wallet',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'wallet' => 'decimal:2',
        'rating' => 'float',
    ];

    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * Get the user that owns the seller profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    /**
     * Get the accounts for sale by this seller.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(AccountForSale::class, 'seller_id');
    }

    /**
     * Get the orders for this seller.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * Get the reviews for this seller.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'seller_id');
    }

    /**
     * Get conversations where this seller is involved.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'seller_id');
    }

    protected function ranks(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                if (! is_string($value) || $value === '') {
                    return ['elite'];
                }

                $decoded = json_decode($value, true);

                return is_array($decoded) && $decoded !== [] ? $decoded : ['elite'];
            },
            set: fn (array $value): string => json_encode(SellerRanks::normalize($value)),
        );
    }

    /** @return array<int, array<string, string>> */
    public function rankBadges(): array
    {
        return SellerRanks::badges($this->ranks);
    }

    public function primaryRankLabel(): string
    {
        return $this->rankBadges()[0]['label'] ?? __('messages.elite_seller');
    }
}
