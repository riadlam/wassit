<?php

namespace App\Models;

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
        'wallet',
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
}
