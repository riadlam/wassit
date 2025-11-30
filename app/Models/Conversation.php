<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'account_for_sale_id',
        'last_message_at',
        'buyer_unread_count',
        'seller_unread_count',
        'status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the buyer (user) in this conversation.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller in this conversation.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Get the account for sale being discussed.
     */
    public function accountForSale(): BelongsTo
    {
        return $this->belongsTo(AccountForSale::class, 'account_for_sale_id');
    }

    /**
     * Get all messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message in this conversation.
     */
    public function latestMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'id', 'conversation_id')
            ->latestOfMany();
    }
}
