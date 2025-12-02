<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargilyPayment extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'checkout_id',
        'status',
        'event',
        'amount_dzd',
        'currency',
        'signature',
        'headers',
        'payload',
    ];

    protected $casts = [
        'order_id' => 'int',
        'amount_dzd' => 'int',
        'headers' => 'array',
        'payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
