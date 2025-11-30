<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'account_id',
        'amount_dzd',
        'status',
    ];

    /**
     * Get the buyer (user) for this order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller for this order.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Get the account for this order.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountForSale::class, 'account_id');
    }
}
