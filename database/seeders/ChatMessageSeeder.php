<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buyerId = 4;
        $sellerId = 8;
        
        // Check if buyer and seller exist
        $buyer = \App\Models\User::find($buyerId);
        $seller = \App\Models\Seller::find($sellerId);
        
        if (!$buyer) {
            $this->command->error("Buyer with ID {$buyerId} not found!");
            return;
        }
        
        if (!$seller) {
            $this->command->error("Seller with ID {$sellerId} not found!");
            return;
        }
        
        // Create or get existing conversation
        $conversation = Conversation::firstOrCreate(
            [
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
            ],
            [
                'status' => 'active',
                'buyer_unread_count' => 0,
                'seller_unread_count' => 0,
                'last_message_at' => now(),
            ]
        );
        
        $this->command->info("Conversation ID: {$conversation->id} created/found");
        
        // Create a message from the seller
        // sender_id should be the user_id (which is 8 for seller_id 8)
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sellerId, // Since user_id and seller_id share the same id
            'sender_type' => 'seller',
            'message_type' => 'text',
            'content' => 'Hello there! I saw you were interested in my account. How can I help you?',
        ]);
        
        // Update conversation's last_message_at
        $conversation->update([
            'last_message_at' => $message->created_at,
            'buyer_unread_count' => 1, // Buyer has 1 unread message
        ]);
        
        $this->command->info("Message ID: {$message->id} created from seller to buyer");
        
        // Optionally create a reply from buyer
        $buyerMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $buyerId,
            'sender_type' => 'buyer',
            'message_type' => 'text',
            'content' => 'Hi! Yes, I\'m interested. Can you tell me more about the account?',
        ]);
        
        // Update conversation's last_message_at
        $conversation->update([
            'last_message_at' => $buyerMessage->created_at,
            'seller_unread_count' => 1, // Seller has 1 unread message
        ]);
        
        $this->command->info("Message ID: {$buyerMessage->id} created from buyer to seller");
        $this->command->info("Chat seeder completed successfully!");
    }
}
