<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private conversation channel: only the buyer or seller of the conversation can listen
Broadcast::channel('conversations.{conversationId}', function ($user, $conversationId) {
    \Log::info('Broadcast auth attempt - CALLBACK CALLED', [
        'user_id' => $user->id,
        'conversation_id' => $conversationId,
        'conversation_id_type' => gettype($conversationId),
    ]);
    
    $conversation = Conversation::find((int) $conversationId);
    
    if (!$conversation) {
        \Log::warning('Conversation not found', ['conversation_id' => $conversationId]);
        return false;
    }

    // Buyer side
    if ((int) $conversation->buyer_id === (int) $user->id) {
        \Log::info('Authorized as buyer');
        return true;
    }

    // Seller side (user has a seller profile with same id)
    $seller = \App\Models\Seller::find((int) $user->id);
    if ($seller && (int) $conversation->seller_id === (int) $seller->id) {
        \Log::info('Authorized as seller');
        return true;
    }

    \Log::warning('Authorization failed', [
        'conversation_buyer_id' => $conversation->buyer_id,
        'conversation_seller_id' => $conversation->seller_id,
        'user_id' => $user->id,
        'seller_found' => $seller ? 'yes' : 'no',
    ]);
    
    return false;
});
