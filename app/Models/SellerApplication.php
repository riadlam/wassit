<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerApplication extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'phone',
        'country',
        'business_name',
        'website',
        'experience',
        'games',
        'preferred_location',
        'account_count',
        'status',
        'telegram_message',
        'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
