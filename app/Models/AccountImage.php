<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountImage extends Model
{
    protected $fillable = [
        'account_id',
        'url',
    ];

    /**
     * Get the account that owns this image.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountForSale::class, 'account_id');
    }
}
