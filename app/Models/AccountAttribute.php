<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountAttribute extends Model
{
    protected $fillable = [
        'account_id',
        'attribute_key',
        'attribute_value',
    ];

    /**
     * Get the account that owns this attribute.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountForSale::class, 'account_id');
    }
}
