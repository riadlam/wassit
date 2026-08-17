<?php

namespace App\Support;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Unread chat totals for the navigation badge, covering both sides of the
 * marketplace: the same user can be a buyer in one thread and the seller in
 * another.
 */
class ChatUnreadSummary
{
    public function __construct(
        public readonly int $count = 0,
        public readonly ?int $conversationId = null,
    ) {}

    public static function forUser(?User $user): self
    {
        if (! $user) {
            return new self();
        }

        $sellerId = $user->seller?->id;

        $conversations = Conversation::query()
            ->where(function (Builder $query) use ($user, $sellerId) {
                $query->where(function (Builder $asBuyer) use ($user) {
                    $asBuyer->where('buyer_id', $user->id)
                        ->where('buyer_unread_count', '>', 0);
                });

                if ($sellerId) {
                    $query->orWhere(function (Builder $asSeller) use ($sellerId) {
                        $asSeller->where('seller_id', $sellerId)
                            ->where('seller_unread_count', '>', 0);
                    });
                }
            })
            ->orderByDesc('last_message_at')
            ->get(['id', 'buyer_id', 'seller_id', 'buyer_unread_count', 'seller_unread_count']);

        $total = $conversations->sum(function (Conversation $conversation) use ($user, $sellerId) {
            if ($conversation->buyer_id === $user->id) {
                return (int) $conversation->buyer_unread_count;
            }

            return $sellerId && $conversation->seller_id === $sellerId
                ? (int) $conversation->seller_unread_count
                : 0;
        });

        return new self($total, $conversations->first()?->id);
    }

    public function hasUnread(): bool
    {
        return $this->count > 0;
    }

    /** Keeps the badge narrow when a thread has been ignored for a while. */
    public function badgeLabel(): string
    {
        return $this->count > 9 ? '9+' : (string) $this->count;
    }
}
