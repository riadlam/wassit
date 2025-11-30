<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Get the images for this account.
     */
    public function images(): HasMany
    {
        return $this->hasMany(AccountImage::class, 'account_id');
    }

    /**
     * Get the orders for this account.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'account_id');
    }
}
