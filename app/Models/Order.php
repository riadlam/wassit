<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'account_id',
        'amount_dzd',
        'status',
        'chargily_checkout_id',
        'chargily_payment_id',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'buyer_id' => 'int',
        'seller_id' => 'int',
        'account_id' => 'int',
        'paid_at' => 'datetime',
        'metadata' => 'array',
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

    /**
     * Latest/primary Chargily payment linked to this order.
     */
    public function chargilyPayment(): BelongsTo
    {
        return $this->belongsTo(ChargilyPayment::class, 'chargily_payment_id');
    }

    /**
     * All Chargily payment webhook records for this order.
     */
    public function chargilyPayments(): HasMany
    {
        return $this->hasMany(ChargilyPayment::class);
    }
}
