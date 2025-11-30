<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The conversation instance.
     */
    public Conversation $conversation;

    /**
     * The formatted message payload (as used by the frontend).
     */
    public array $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation, array $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversations.' . $this->conversation->id);
    }

    /**
     * Event name used on the frontend.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        // Load seller relationship for avatar logic on frontend
        $this->conversation->load('seller');
        
        return [
            'conversation' => $this->conversation,
            'message' => $this->message,
        ];
    }
}


