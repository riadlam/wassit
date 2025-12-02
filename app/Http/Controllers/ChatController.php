<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get all conversations for the logged-in user
     */
    public function getConversations(Request $request)
    {
        $user = Auth::user();
        $user->load('seller'); // Ensure seller relationship is loaded
        
        // Get all messages (including soft-deleted) sent by this user
        $userMessages = Message::withTrashed()
            ->where('sender_id', $user->id)
            ->get();
        
        // Get conversations where user is buyer
        $buyerConversations = Conversation::with(['seller.user', 'accountForSale'])
            ->where('buyer_id', $user->id)
            ->orderBy('last_message_at', 'desc')
            ->get();
        
        // Get conversations where user is seller (if user is a seller)
        $sellerConversations = collect();
        if ($user->seller) {
            $sellerConversations = Conversation::with(['buyer', 'accountForSale', 'seller.user'])
                ->where('seller_id', $user->seller->id)
                ->orderBy('last_message_at', 'desc')
                ->get();
        }
        
        // Find conversations from messages where user has sent messages
        // Include soft-deleted messages to find all conversations
        $messageConversationIds = $userMessages
            ->pluck('conversation_id')
            ->filter()
            ->unique();
        
        // Get all conversations from message IDs (this catches everything)
        // Include trashed conversations in case they were soft-deleted
        $conversationsFromMessages = collect();
        if ($messageConversationIds->count() > 0) {
            $conversationsFromMessages = Conversation::withTrashed()
                ->with(['buyer', 'seller.user', 'accountForSale'])
                ->whereIn('id', $messageConversationIds)
                ->orderBy('last_message_at', 'desc')
                ->get();

            // Filter to only include conversations where user is actually involved
            // and exclude soft-deleted ones (unless we decide to show them later)
            $conversationsFromMessages = $conversationsFromMessages->filter(function($conv) use ($user) {
                return is_null($conv->deleted_at) && 
                       ($conv->buyer_id === $user->id || 
                        ($user->seller && $conv->seller_id === $user->seller->id));
            });
        }
        
        // Merge all conversations and remove duplicates
        $allConversations = $buyerConversations->merge($sellerConversations)
            ->merge($conversationsFromMessages)
            ->unique('id')
            ->sortByDesc('last_message_at')
            ->values();
        
        
        $formattedConversations = $allConversations->map(function ($conversation) use ($user) {
            $isBuyer = $conversation->buyer_id === $user->id;
            
            // Get the other party's info
            $otherUserName = 'Unknown';
            $avatar = asset('storage/examplepfp.webp');
            
            if ($isBuyer) {
                // User is buyer, other party is seller
                $seller = $conversation->seller;
                if ($seller && $seller->user) {
                    $otherUserName = $seller->user->name;
                    if ($seller->pfp) {
                        if (filter_var($seller->pfp, FILTER_VALIDATE_URL)) {
                            $avatar = $seller->pfp;
                        } else {
                            // Check if file exists in storage
                            $pfpPath = $seller->pfp;
                            // Remove 'storage/' prefix if present
                            $pfpPath = str_replace('storage/', '', $pfpPath);
                            if (Storage::disk('public')->exists($pfpPath)) {
                                $avatar = asset('storage/' . $pfpPath);
                            } else {
                                // File doesn't exist, use example pfp
                                $avatar = asset('storage/examplepfp.webp');
                            }
                        }
                    } else {
                        // Fallback to example seller avatar when no pfp is set
                        $avatar = asset('storage/examplepfp.webp');
                    }
                }
            } else {
                // User is seller, other party is buyer
                $buyer = $conversation->buyer;
                if ($buyer) {
                    $otherUserName = $buyer->name;
                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($otherUserName) . '&background=ef4444&color=fff';
                }
            }
            
            // Get last message preview
            $lastMessage = Message::where('conversation_id', $conversation->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Format last message preview
            $lastMessageText = 'No messages yet';
            if ($lastMessage) {
                if ($lastMessage->attachments->count() > 0) {
                    $attachment = $lastMessage->attachments->first();
                    if (str_starts_with($attachment->file_type, 'image/')) {
                        $lastMessageText = '📷 Image';
                    } elseif (str_starts_with($attachment->file_type, 'video/')) {
                        $lastMessageText = '🎥 Video';
                    } else {
                        $lastMessageText = '📎 ' . $attachment->file_name;
                    }
                    // Add text content if exists
                    if ($lastMessage->content) {
                        $lastMessageText = $lastMessage->content . ' • ' . $lastMessageText;
                    }
                } else {
                    $lastMessageText = $this->formatMessageContent($lastMessage);
                }
            }

            // Account info (if linked)
            $account = $conversation->accountForSale;
            $accountData = null;
            if ($account) {
                $accountTitle = $account->title ?? null;
                $accountUrl = null;
                if ($account->relationLoaded('game') && $account->game) {
                    $slug = $account->game->slug === 'mlbb' ? 'mobile-legends' : $account->game->slug;
                    $accountUrl = route('accounts.show', ['slug' => $slug, 'id' => $account->id]);
                }
                $accountData = [
                    'id' => $account->id,
                    'title' => $accountTitle,
                    'url' => $accountUrl,
                ];
            }
            
            // Get unread count
            $unreadCount = $isBuyer ? $conversation->buyer_unread_count : $conversation->seller_unread_count;
            
            // Format timestamp
            $timestamp = $conversation->last_message_at 
                ? $this->formatTimestamp($conversation->last_message_at) 
                : 'Just now';
            
            return [
                'id' => $conversation->id,
                'name' => $otherUserName,
                'avatar' => $avatar,
                'lastMessage' => $lastMessageText,
                'account' => $accountData,
                'timestamp' => $timestamp,
                'unread' => $unreadCount > 0,
                'unreadCount' => $unreadCount,
                'status' => 'offline', // TODO: Implement online status
            ];
        });
        
        return response()->json([
            'conversations' => $formattedConversations
        ]);
    }
    
    /**
     * Get messages for a specific conversation
     */
    public function getMessages(Request $request, $conversationId)
    {
        $user = Auth::user();
        // Ensure seller relation is available for robust auth checks
        $user->loadMissing('seller');

        $conversation = Conversation::with(['buyer', 'seller.user', 'accountForSale.game'])
            ->findOrFail($conversationId);
        
        // Check if user is part of this conversation
        $isBuyer = (int)$conversation->buyer_id === (int)$user->id;
        // Allow seller either by matching seller.id or by seller.user_id
        $isSeller = (
            ($user->seller && (int)$conversation->seller_id === (int)$user->seller->id)
            || ($conversation->seller && (int)$conversation->seller->user_id === (int)$user->id)
        );
        
        if (!$isBuyer && !$isSeller) {
            Log::warning('ChatController::getMessages unauthorized access', [
                'conversation_id' => (int)$conversationId,
                'conv_buyer_id' => (int)$conversation->buyer_id,
                'conv_seller_id' => (int)$conversation->seller_id,
                'conv_seller_user_id' => $conversation->seller ? (int)$conversation->seller->user_id : null,
                'user_id' => (int)$user->id,
                'user_seller_id' => $user->seller ? (int)$user->seller->id : null,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Get messages
        $messages = Message::with(['sender', 'attachments'])
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Mark messages as read for the current user
        $unreadMessages = $messages->where('sender_id', '!=', $user->id)
            ->whereNull('read_at');
        
        if ($unreadMessages->count() > 0) {
            Message::whereIn('id', $unreadMessages->pluck('id'))
                ->update(['read_at' => now()]);
            
            // Update unread count
            if ($isBuyer) {
                $conversation->buyer_unread_count = 0;
            } else {
                $conversation->seller_unread_count = 0;
            }
            $conversation->save();
        }
        
        $formattedMessages = $messages->map(function ($message) use ($user, $conversation) {
            $isSender = $message->sender_id === $user->id;
            $senderType = $message->sender_type;
            
            // Get sender avatar
            // - For seller messages: use seller pfp or examplepfp.webp
            // - For buyer messages: use initials (ui-avatars)
            $avatar = asset('storage/examplepfp.webp');
            if ($message->sender) {
                if ($senderType === 'seller') {
                    // Seller side avatar
                    if ($conversation->seller) {
                        if ($conversation->seller->pfp) {
                            if (filter_var($conversation->seller->pfp, FILTER_VALIDATE_URL)) {
                                $avatar = $conversation->seller->pfp;
                            } else {
                                // Check if file exists in storage
                                $pfpPath = $conversation->seller->pfp;
                                // Remove 'storage/' prefix if present
                                $pfpPath = str_replace('storage/', '', $pfpPath);
                                if (Storage::disk('public')->exists($pfpPath)) {
                                    $avatar = asset('storage/' . $pfpPath);
                                } else {
                                    // File doesn't exist, use example pfp
                                    $avatar = asset('storage/examplepfp.webp');
                                }
                            }
                        } else {
                            // No pfp set, use example pfp
                            $avatar = asset('storage/examplepfp.webp');
                        }
                    } else {
                        // No seller found, use example pfp
                        $avatar = asset('storage/examplepfp.webp');
                    }
                } else {
                    // Buyer side avatar uses initials service
                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name) . '&background=ef4444&color=fff';
                }
            }
            
            // Frontend expects:
            // - type 'user'   => current logged-in user (right side bubble)
            // - type 'seller' => other party (left side bubble)
            // - type 'system' => system/info messages
            $formattedType = 'system';
            if ($message->message_type !== 'system') {
                $formattedType = $isSender ? 'user' : 'seller';
            }

            $formatted = [
                'id' => $message->id,
                'type' => $formattedType,
                'content' => $this->formatMessageContent($message),
                'timestamp' => $this->formatTimestamp($message->created_at),
                'read' => $message->read_at !== null,
            ];
            
            if ($message->message_type !== 'system') {
                $formatted['avatar'] = $avatar;
            }
            
            // Add attachments if any
            if ($message->attachments->count() > 0) {
                $formatted['attachments'] = $message->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        'file_url' => $attachment->file_url,
                        'file_type' => $attachment->file_type,
                        'thumbnail_url' => $attachment->thumbnail_url,
                    ];
                });
            }
            
            return $formatted;
        });

        // Prepend system messages describing the linked account (if any)
        $introMessages = [];
        $account = $conversation->accountForSale;
        if ($account) {
            $buyerName = $conversation->buyer ? $conversation->buyer->name : __('messages.buyer');
            $accountTitle = $account->title ?? __('messages.account');
            $accountId = $account->id;

            $productUrl = null;
            if ($account->game) {
                $slug = $account->game->slug === 'mlbb' ? 'mobile-legends' : $account->game->slug;
                $productUrl = route('accounts.show', ['slug' => $slug, 'id' => $account->id]);
            }

            $timestamp = $this->formatTimestamp($conversation->created_at ?? $conversation->last_message_at ?? now());

            // First system message: started chat for account
            $content1 = "<strong>{$buyerName}</strong> started a chat for: <strong>{$accountTitle} #";
            if ($productUrl) {
                $content1 .= "<a href=\"{$productUrl}\" class=\"text-blue-500 hover:underline\">{$accountId}</a>";
            } else {
                $content1 .= e($accountId);
            }
            $content1 .= "</strong>";

            $introMessages[] = [
                'type' => 'system',
                'content' => $content1,
                'timestamp' => $timestamp,
                'read' => true,
            ];

            // Second system message: product link
            if ($productUrl) {
                $content2 = "Product link: <a href=\"{$productUrl}\" class=\"text-blue-500 hover:underline\">{$productUrl}</a>";
                $introMessages[] = [
                    'type' => 'system',
                    'content' => $content2,
                    'timestamp' => $timestamp,
                    'read' => true,
                ];
            }
        }

        if (!empty($introMessages)) {
            $formattedMessages = collect($introMessages)->merge($formattedMessages)->values();
        }
        
        return response()->json([
            'messages' => $formattedMessages
        ]);
    }
    
    /**
     * Send a message
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $user = Auth::user();
        // Ensure seller relation is available for robust auth checks
        $user->loadMissing('seller');

        $conversation = Conversation::with(['seller'])->findOrFail($conversationId);
        
        // Check if user is part of this conversation
        $isBuyer = (int)$conversation->buyer_id === (int)$user->id;
        // Allow seller either by matching seller.id or by seller.user_id
        $isSeller = (
            ($user->seller && (int)$conversation->seller_id === (int)$user->seller->id)
            || ($conversation->seller && (int)$conversation->seller->user_id === (int)$user->id)
        );
        
        if (!$isBuyer && !$isSeller) {
            \Log::warning('ChatController::sendMessage unauthorized access', [
                'conversation_id' => (int)$conversationId,
                'conv_buyer_id' => (int)$conversation->buyer_id,
                'conv_seller_id' => (int)$conversation->seller_id,
                'conv_seller_user_id' => $conversation->seller ? (int)$conversation->seller->user_id : null,
                'user_id' => (int)$user->id,
                'user_seller_id' => $user->seller ? (int)$user->seller->id : null,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'content' => 'required_without:attachments|string|max:5000',
            'message_type' => 'required|in:text,file,video,image,system',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,webp,mp4,mov,avi,pdf,doc,docx|max:10240', // 10MB max
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Determine sender type
        $senderType = $isBuyer ? 'buyer' : 'seller';
        
        // Create message
        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'message_type' => $request->message_type ?? 'text',
            'content' => $request->content,
        ]);
        
        // Handle file attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filePath = $file->store('chat_attachments', 'public');
                $fileType = $file->getMimeType();
                
                // Generate thumbnail for images/videos if needed
                $thumbnailPath = null;
                if (str_starts_with($fileType, 'image/')) {
                    // For images, we can use the same file as thumbnail
                    $thumbnailPath = $filePath;
                } elseif (str_starts_with($fileType, 'video/')) {
                    // TODO: Generate video thumbnail using FFmpeg or similar
                    // For now, leave as null
                }
                
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $fileType,
                    'file_size' => $file->getSize(),
                    'thumbnail_path' => $thumbnailPath,
                ]);
            }
        }
        
        // Update conversation
        $conversation->last_message_at = now();
        
        // Update unread count for the other party
        if ($isBuyer) {
            $conversation->seller_unread_count++;
        } else {
            $conversation->buyer_unread_count++;
        }
        $conversation->save();
        
        // Load relationships for response
        $message->load(['sender', 'attachments']);
        
        // Format response
        // For the current logged-in user, frontend expects type 'user'
        // so the bubble appears on the right with the local avatar.
        $formattedType = 'user';

        // Avatar is mainly used when message.type === 'seller' in the UI.
        // We still include it here for consistency, but for 'user' messages
        // the Blade template uses Auth::user() avatar instead.
        $avatar = asset('storage/examplepfp.webp');
        if ($message->sender) {
            if ($message->sender_type === 'seller') {
                // Seller side avatar - use seller pfp or examplepfp.webp
                if ($conversation->seller && $conversation->seller->pfp) {
                    if (filter_var($conversation->seller->pfp, FILTER_VALIDATE_URL)) {
                        $avatar = $conversation->seller->pfp;
                    } elseif (Storage::disk('public')->exists($conversation->seller->pfp)) {
                        $avatar = asset('storage/' . $conversation->seller->pfp);
                    } else {
                        // If pfp path doesn't exist, use example
                        $avatar = asset('storage/examplepfp.webp');
                    }
                } else {
                    // Explicitly force example pfp when seller has no pfp set
                    $avatar = asset('storage/examplepfp.webp');
                }
            } else {
                // Buyer side avatar uses initials service
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($message->sender->name) . '&background=ef4444&color=fff';
            }
        }
        
        $formattedMessage = [
            'id' => $message->id,
            'type' => $formattedType,
            'content' => $this->formatMessageContent($message),
            'timestamp' => $this->formatTimestamp($message->created_at),
            'read' => false,
            'avatar' => $avatar,
        ];
        
        if ($message->attachments->count() > 0) {
            $formattedMessage['attachments'] = $message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_url' => $attachment->file_url,
                    'file_type' => $attachment->file_type,
                    'thumbnail_url' => $attachment->thumbnail_url,
                ];
            });
        }
        
        // Prepare broadcast message payload
        $broadcastMessage = [
            'id' => $message->id,
            'sender_id' => $message->sender_id, // Include sender ID so frontend can filter out own messages
            'type' => 'seller', // Always show as 'seller' (left side) for the other party
            'content' => $this->formatMessageContent($message),
            'timestamp' => $this->formatTimestamp($message->created_at),
            'read' => false,
            'avatar' => $avatar,
        ];
        
        // Add attachments to broadcast if any
        if ($message->attachments->count() > 0) {
            $broadcastMessage['attachments'] = $message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_url' => $attachment->file_url,
                    'file_type' => $attachment->file_type,
                    'thumbnail_url' => $attachment->thumbnail_url,
                ];
            });
        }
        
        // Broadcast to other participants in this conversation
        // Note: For the sender, they already have the message locally, so they'll ignore this broadcast
        // For others, this is how they receive the new message
        // The broadcast type should always be 'seller' (left side) for the other party
        // because the frontend shows 'seller' type on left, 'user' type on right
        event(new MessageSent($conversation, $broadcastMessage));

        return response()->json([
            'message' => $formattedMessage
        ], 201);
    }
    
    /**
     * Create a new conversation
     */
    public function createConversation(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'seller_id' => 'required|exists:sellers,id',
            'account_for_sale_id' => 'nullable|exists:accounts_for_sale,id',
            'initial_message' => 'required|string|min:40|max:1500',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Check if conversation already exists
        $existingConversation = Conversation::where('buyer_id', $user->id)
            ->where('seller_id', $request->seller_id)
            ->where('account_for_sale_id', $request->account_for_sale_id)
            ->first();
        
        if ($existingConversation) {
            return response()->json([
                'conversation_id' => $existingConversation->id,
                'message' => 'Conversation already exists'
            ]);
        }
        
        // Create conversation
        $conversation = Conversation::create([
            'buyer_id' => $user->id,
            'seller_id' => $request->seller_id,
            'account_for_sale_id' => $request->account_for_sale_id,
            'last_message_at' => now(),
            'seller_unread_count' => 1,
            'buyer_unread_count' => 0,
        ]);
        
        // Create initial message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'buyer',
            'message_type' => 'text',
            'content' => $request->initial_message,
        ]);
        
        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => 'Conversation created successfully'
        ], 201);
    }

    /**
     * Find an existing conversation for a buyer / seller / account combo
     */
    public function findConversation(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'seller_id' => 'required|exists:sellers,id',
            'account_for_sale_id' => 'nullable|exists:accounts_for_sale,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Conversation::where('buyer_id', $user->id)
            ->where('seller_id', $request->seller_id);

        if ($request->filled('account_for_sale_id')) {
            $query->where('account_for_sale_id', $request->account_for_sale_id);
        }

        $conversation = $query->first();

        if ($conversation) {
            // Store in session so the chat page can auto-open it without exposing the id in the URL
            session()->put('active_chat_conversation_id', $conversation->id);
        }

        return response()->json([
            'conversation_id' => $conversation ? $conversation->id : null,
        ]);
    }

    /**
     * Format message content for display
     */
    private function formatMessageContent(Message $message): string
    {
        if ($message->message_type === 'system') {
            return $message->content;
        }
        
        // Return the actual content (text), not attachment placeholders
        // Attachments are handled separately in the attachments array
        return $message->content ?? '';
    }

    /**
     * Format timestamp for display (relative format like 5m, 2h, 3d)
     */
    private function formatTimestamp($timestamp): string
    {
        $now = now();
        $diff = $now->diffInMinutes($timestamp);
        
        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . 'm';
        } elseif ($diff < 1440) {
            return round($diff / 60) . 'h';
        } elseif ($diff < 10080) {
            return round($diff / 1440) . 'd';
        } else {
            return $timestamp->format('M j');
        }
    }
}
