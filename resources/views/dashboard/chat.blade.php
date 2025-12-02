@extends('layouts.app')

@section('content')
    <!-- Full Screen Background Image -->
    <div id="background-image" class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500 ease-in-out" style="background-image: url('{{ asset('storage/home_page/degaultbanner.webp') }}'); opacity: 1;"></div>
        <div class="absolute inset-0" style="background-color:rgba(14, 16, 21, 0.95);"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen pt-16 sm:pt-16 pb-20 md:pb-8">
        <!-- Dashboard Navigation -->
        @include('components.dashboard-nav')
        
        <!-- Main Content -->
        <div class="relative z-10 px-4 sm:px-6 lg:px-8" style="padding-top: 122px;">
            <div x-data="chatData()" x-init="init()" class="mx-auto max-w-[1550px]">
                <div class="flex rounded-xl overflow-hidden" style="background-color: #0e1015; border: 1px solid #2d2c31; height: calc((100vh - 200px) * 1.38);">
                    <!-- Left Column - Conversation List -->
                    <div class="w-full md:w-1/3 border-r flex flex-col" style="border-color: #2d2c31; background-color: #0e1015;" 
                         :class="selectedConversation !== null ? 'hidden md:flex' : 'flex'">
                        <!-- Header with Notifications Toggle -->
                        <div class="flex items-center justify-between px-4 py-3 border-b" style="border-color: #2d2c31;">
                            <div class="flex items-center gap-2">
                                <button type="button" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" style="background-color: rgba(45, 44, 49, 0.5);" role="switch" aria-checked="false">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0"></span>
                                </button>
                                <label class="text-sm text-gray-400">{{ __('messages.browser_notifications') }}</label>
                            </div>
                        </div>
                        
                        <!-- Search Box -->
                        <form class="px-4 py-3 border-b" style="border-color: #2d2c31;">
                            <div class="relative flex items-center">
                                <input 
                                    type="search" 
                                    id="hub-search-field" 
                                    autocomplete="off" 
                                    placeholder="{{ __('messages.search_conversations') }}" 
                                    class="w-full px-4 py-2 pr-10 rounded-lg text-white placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all" 
                                    style="background-color: #1b1a1e; border: 1px solid #2d2c31;"
                                >
                                <button type="button" class="absolute right-3 text-gray-400 hover:text-white transition-colors" aria-label="{{ __('messages.search') }}">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Conversation List -->
                        <div class="flex-1 overflow-y-auto chat-scrollbar">
                            <!-- Loading State -->
                            <div x-show="loading" class="flex items-center justify-center py-8">
                                <div class="text-gray-400">{{ __('messages.loading_conversations') }}</div>
                            </div>
                            
                            <!-- Conversations -->
                            <nav aria-label="Conversation List" class="p-2" x-show="!loading && conversations.length > 0">
                                <template x-for="conversation in conversations" :key="conversation.id">
                                    <a 
                                        href="#" 
                                        @click.prevent="selectConversation(conversation.id)"
                                        class="flex items-start gap-3 px-3 py-3 rounded-lg mb-1 transition-all cursor-pointer"
                                        :class="selectedConversation === conversation.id ? '' : 'hover:bg-gray-800/50'"
                                        :style="selectedConversation === conversation.id ? 'background-color: rgba(59, 130, 246, 0.15); border-left: 3px solid #3b82f6;' : 'background-color: transparent;'"
                                    >
                                        <div class="relative flex-shrink-0">
                                            <img 
                                                :src="conversation.avatar" 
                                                :alt="conversation.name" 
                                                class="w-12 h-12 rounded-full object-cover"
                                                :onerror="`this.onerror=null; this.src='{{ asset('storage/examplepfp.webp') }}';`"
                                            >
                                            <span x-show="conversation.unread" class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-red-600 border-2" style="border-color: #0e1015;"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="text-sm font-medium text-white truncate" x-text="conversation.name"></div>
                                                <span class="text-xs text-gray-400 ml-2 flex-shrink-0" x-text="conversation.timestamp"></span>
                                            </div>
                                            <!-- Linked account title -->
                                            <template x-if="conversation.account && conversation.account.title">
                                                <div class="text-xs text-gray-400 truncate mb-0.5">
                                                    <span class="text-red-400 font-semibold mr-1">Account:</span>
                                                    <template x-if="conversation.account.url">
                                                        <a :href="conversation.account.url" class="hover:text-red-400 hover:underline" x-text="conversation.account.title"></a>
                                                    </template>
                                                    <template x-if="!conversation.account.url">
                                                        <span x-text="conversation.account.title"></span>
                                                    </template>
                                                </div>
                                            </template>
                                            <div class="text-sm text-gray-400 truncate" x-text="conversation.lastMessage"></div>
                                        </div>
                                    </a>
                                </template>
                            </nav>
                            
                            <!-- Empty State -->
                            <div x-show="!loading && conversations.length === 0" class="text-center py-8">
                                <p class="text-gray-400">{{ __('messages.no_conversations_yet') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Chat Messages (Only show when conversation is selected) -->
                    <div x-show="selectedConversation !== null" 
                         x-cloak
                         class="flex flex-1 flex-col w-full md:w-auto" 
                         style="background-color: #0e1015;">
                        <!-- Chat Header -->
                        <header class="flex items-center justify-between px-4 py-3 border-b" style="border-color: #2d2c31;">
                            <div class="flex items-center gap-3">
                                <!-- Mobile Back Button -->
                                <button @click="selectedConversation = null" class="md:hidden text-gray-400 hover:text-white transition-colors mr-2 p-2 -ml-2">
                                    <i class="fa-solid fa-arrow-left text-lg"></i>
                                </button>
                                <template x-if="getSelectedConversation()">
                                    <div class="relative">
                                        <img 
                                            :src="getSelectedConversation().avatar" 
                                            :alt="getSelectedConversation().name" 
                                            class="w-10 h-10 rounded-full object-cover"
                                            :onerror="`this.onerror=null; this.src='{{ asset('storage/examplepfp.webp') }}';`"
                                        >
                                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2" 
                                              :class="getSelectedConversation().status === 'online' ? 'bg-green-500' : 'bg-gray-500'" 
                                              style="border-color: #0e1015;"></span>
                                    </div>
                                </template>
                                <template x-if="getSelectedConversation()">
                                    <div>
                                        <div class="text-sm font-medium text-white" x-text="getSelectedConversation().name"></div>
                                        <div class="text-xs text-gray-400" x-text="getSelectedConversation().status === 'online' ? '{{ __('messages.online') }}' : '{{ __('messages.offline') }}'"></div>
                                    </div>
                                </template>
                                <!-- Paid badge -->
                                <template x-if="getSelectedConversation() && getSelectedConversation().paid">
                                    <div class="ml-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold" style="background-color: rgba(34,197,94,0.15); color: #86efac; border: 1px solid rgba(34,197,94,0.3);">
                                            <i class="fa-solid fa-badge-check text-green-400"></i>
                                            <span>
                                                <template x-if="getSelectedConversation().buyerId && Number(getSelectedConversation().buyerId) === Number(currentUserId)">
                                                    <span>{{ __('messages.you_paid_badge') }}</span>
                                                </template>
                                                <template x-if="!getSelectedConversation().buyerId || Number(getSelectedConversation().buyerId) !== Number(currentUserId)">
                                                    <span>{{ __('messages.buyer_paid_badge') }}</span>
                                                </template>
                                            </span>
                                        </span>
                                    </div>
                                </template>
                            </div>
                            <button type="button" class="text-gray-400 hover:text-white transition-colors" aria-label="{{ __('messages.search_in_conversation') }}">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </header>
                        
                        <!-- Messages Area -->
                        <div id="messages-container" class="flex-1 overflow-y-auto chat-scrollbar px-4 py-4">
                            <template x-if="getSelectedConversation() && getSelectedMessages().length > 0">
                                <div class="space-y-4">
                                    <template x-for="(message, index) in getSelectedMessages()" :key="index">
                                        <div>
                                            <!-- System Message -->
                                            <template x-if="message.type === 'system'">
                                                <div class="flex justify-center">
                                                    <div class="text-sm text-gray-400 text-center max-w-2xl">
                                                        <div class="inline-block px-4 py-2 rounded-lg" style="background-color: rgba(27, 26, 30, 0.5);">
                                                            <span x-html="message.content"></span>
                                                            <span class="ml-2 text-xs text-gray-500" x-text="message.timestamp"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <!-- User Message -->
                                            <template x-if="message.type === 'user'">
                                                <div class="flex items-start gap-3 justify-end">
                                                    <div class="flex flex-col items-end max-w-[70%]">
                                                        <div class="px-4 py-2 rounded-lg" style="background-color: rgba(59, 130, 246, 0.2);">
                                                            <p x-show="message.content && message.content.trim()" class="text-sm text-white mb-2" x-text="message.content"></p>
                                                            <!-- Attachments -->
                                                            <template x-if="message.attachments && message.attachments.length > 0">
                                                                <div class="mt-2 space-y-2">
                                                                    <template x-for="attachment in message.attachments" :key="attachment.id">
                                                                        <div>
                                                                            <template x-if="attachment.file_type && attachment.file_type.startsWith('image/')">
                                                                                <a :href="attachment.file_url" target="_blank" class="block">
                                                                                    <img :src="attachment.thumbnail_url || attachment.file_url" :alt="attachment.file_name" class="max-w-full max-h-64 rounded-lg cursor-pointer hover:opacity-80 transition-opacity">
                                                                                </a>
                                                                            </template>
                                                                            <template x-if="attachment.file_type && attachment.file_type.startsWith('video/')">
                                                                                <video :src="attachment.file_url" controls class="max-w-full max-h-64 rounded-lg">
                                                                                    Your browser does not support the video tag.
                                                                                </video>
                                                                            </template>
                                                                            <template x-if="!attachment.file_type || (!attachment.file_type.startsWith('image/') && !attachment.file_type.startsWith('video/'))">
                                                                                <a :href="attachment.file_url" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-700/20 transition-colors" style="background-color: rgba(59, 130, 246, 0.1);">
                                                                                    <i class="fa-solid fa-file text-blue-400"></i>
                                                                                    <span class="text-sm text-white" x-text="attachment.file_name"></span>
                                                                                    <i class="fa-solid fa-download text-blue-400 ml-auto"></i>
                                                                                </a>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <div class="flex items-center gap-1 mt-1 text-xs text-gray-400">
                                                            <span x-text="message.timestamp"></span>
                                                            <span x-show="message.read === false" class="text-gray-500">✓</span>
                                                            <span x-show="message.read === true" class="text-blue-500">✓✓</span>
                                                        </div>
                                                    </div>
                                                    <img 
                                                        src="{{ Auth::user()->role === 'seller'
                                                            ? (
                                                                Auth::user()->seller && Auth::user()->seller->pfp
                                                                    ? (filter_var(Auth::user()->seller->pfp, FILTER_VALIDATE_URL)
                                                                        ? Auth::user()->seller->pfp
                                                                        : asset('storage/' . Auth::user()->seller->pfp))
                                                                    : asset('storage/examplepfp.webp')
                                                              )
                                                            : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=ef4444&color=fff' }}" 
                                                        alt="{{ __('messages.you') }}" 
                                                        class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                                        onerror="this.onerror=null; this.src='{{ Auth::user()->role === 'seller' ? asset('storage/examplepfp.webp') : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=ef4444&color=fff' }}';"
                                                    >
                                                </div>
                                            </template>
                                            
                                            <!-- Seller Message -->
                                            <template x-if="message.type === 'seller'">
                                                <div class="flex items-start gap-3">
                                                    <img 
                                                        :src="message.avatar || getSelectedConversation().avatar" 
                                                        alt="{{ __('messages.seller') }}" 
                                                        class="w-8 h-8 rounded-full object-cover flex-shrink-0"
                                                        :onerror="`this.onerror=null; this.src='{{ asset('storage/examplepfp.webp') }}';`"
                                                    >
                                                    <div class="flex flex-col max-w-[70%]">
                                                        <div class="px-4 py-2 rounded-lg" style="background-color: rgba(27, 26, 30, 0.5);">
                                                            <p x-show="message.content && message.content.trim()" class="text-sm text-white mb-2" x-text="message.content"></p>
                                                            <!-- Attachments -->
                                                            <template x-if="message.attachments && message.attachments.length > 0">
                                                                <div class="mt-2 space-y-2">
                                                                    <template x-for="attachment in message.attachments" :key="attachment.id">
                                                                        <div>
                                                                            <template x-if="attachment.file_type && attachment.file_type.startsWith('image/')">
                                                                                <a :href="attachment.file_url" target="_blank" class="block">
                                                                                    <img :src="attachment.thumbnail_url || attachment.file_url" :alt="attachment.file_name" class="max-w-full max-h-64 rounded-lg cursor-pointer hover:opacity-80 transition-opacity">
                                                                                </a>
                                                                            </template>
                                                                            <template x-if="attachment.file_type && attachment.file_type.startsWith('video/')">
                                                                                <video :src="attachment.file_url" controls class="max-w-full max-h-64 rounded-lg">
                                                                                    Your browser does not support the video tag.
                                                                                </video>
                                                                            </template>
                                                                            <template x-if="!attachment.file_type || (!attachment.file_type.startsWith('image/') && !attachment.file_type.startsWith('video/'))">
                                                                                <a :href="attachment.file_url" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-700/20 transition-colors" style="background-color: rgba(27, 26, 30, 0.3);">
                                                                                    <i class="fa-solid fa-file text-gray-400"></i>
                                                                                    <span class="text-sm text-white" x-text="attachment.file_name"></span>
                                                                                    <i class="fa-solid fa-download text-gray-400 ml-auto"></i>
                                                                                </a>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <div class="flex items-center gap-1 mt-1 text-xs text-gray-400">
                                                            <span x-text="message.timestamp"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Message Input -->
                        <div class="border-t px-4 py-3" style="border-color: #2d2c31;">
                            <!-- Hidden file input -->
                            <input type="file" id="file-input" multiple accept="image/*,video/*,.pdf,.doc,.docx" class="hidden" @change="handleFileSelection($event)">
                            
                            <!-- Selected files preview -->
                            <div x-show="selectedFiles.length > 0" class="mb-2 flex flex-wrap gap-2">
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg" style="background-color: rgba(27, 26, 30, 0.5);">
                                        <template x-if="file.type && file.type.startsWith('image/')">
                                            <img :src="file.preview" class="w-10 h-10 object-cover rounded" alt="Preview">
                                        </template>
                                        <template x-if="!file.type || !file.type.startsWith('image/')">
                                            <i class="fa-solid fa-file text-gray-400"></i>
                                        </template>
                                        <span class="text-xs text-white max-w-[150px] truncate" x-text="file.name"></span>
                                        <button type="button" @click="removeFile(index)" class="text-red-400 hover:text-red-500">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            
                            <form @submit.prevent="sendMessage" class="flex items-end gap-2">
                                <div class="flex-1 relative">
                                    <textarea 
                                        x-model="messageInput"
                                        placeholder="{{ __('messages.say_something') }}" 
                                        rows="1"
                                        class="w-full px-4 py-2 pr-20 rounded-lg text-white placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all resize-none" 
                                        style="background-color: #1b1a1e; border: 1px solid #2d2c31; min-height: 44px; max-height: 120px;"
                                        @keydown.enter.prevent="if (!$event.shiftKey) { sendMessage($event) }"
                                    ></textarea>
                                    <div class="absolute right-2 bottom-2 flex items-center gap-1">
                                        <button type="button" @click="document.getElementById('file-input').click()" class="text-gray-400 hover:text-white transition-colors p-1" aria-label="{{ __('messages.send_file') }}" title="{{ __('messages.send_file') }}">
                                            <i class="fa-solid fa-paperclip"></i>
                                        </button>
                                        <button type="button" class="text-gray-400 hover:text-white transition-colors p-1" aria-label="{{ __('messages.insert_emoji') }}" title="{{ __('messages.insert_emoji') }}">
                                            <i class="fa-regular fa-face-smile"></i>
                                        </button>
                                        <button type="button" class="text-gray-400 hover:text-white transition-colors px-2 py-1 text-xs rounded" style="background-color: rgba(27, 26, 30, 0.5);">{{ __('messages.gif') }}</button>
                                    </div>
                                </div>
                                <button type="submit" :disabled="sendingMessage || (!messageInput.trim() && selectedFiles.length === 0)" class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed" aria-label="{{ __('messages.send') }}" title="{{ __('messages.send') }}">
                                    <i class="fa-solid fa-paper-plane" x-show="!sendingMessage"></i>
                                    <i class="fa-solid fa-spinner fa-spin" x-show="sendingMessage"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Empty State (Only show when no conversation is selected) -->
                    <div x-show="selectedConversation === null" 
                         x-cloak
                         class="hidden md:flex flex-1 flex-col items-center justify-center" 
                         style="background-color: #0e1015;">
                        <div class="text-center">
                            <i class="fa-solid fa-comments text-6xl text-gray-600 mb-4"></i>
                            <p class="text-gray-400 text-lg">{{ __('messages.select_conversation_to_start') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<!-- Pusher JS for realtime chat -->
<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
const initialConversationIdFromServer = {{ isset($activeConversationId) && $activeConversationId ? (int)$activeConversationId : 'null' }};
const currentUserId = {{ Auth::id() }};

function chatData() {
    return {
        selectedConversation: null,
        initialConversationId: initialConversationIdFromServer,
        currentUserId: currentUserId,
        conversations: [],
        messages: {},
        pusher: null,
        pusherChannels: {},
        loading: true,
        sendingMessage: false,
        messageInput: '',
        selectedFiles: [],
        init() {
            // Conversations will auto-open if an initial conversation id
            // was provided by the backend via $activeConversationId
            this.initPusher();
            this.loadConversations();
        },
        initPusher() {
            // Helper function to get CSRF token
            const getCsrfToken = () => {
                // Try meta tag first
                const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (metaToken) return metaToken;
                
                // Fallback to cookie (Laravel stores it as XSRF-TOKEN)
                const cookies = document.cookie.split(';');
                for (let cookie of cookies) {
                    const [name, value] = cookie.trim().split('=');
                    if (name === 'XSRF-TOKEN') {
                        return decodeURIComponent(value);
                    }
                }
                return '';
            };
            
            // Initialize Pusher client (keys from .env, key is safe on frontend)
            const csrfToken = getCsrfToken();
            console.log('CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
            this.pusher = new Pusher('85c1f07b1c79530f4ced', {
                cluster: 'eu',
                authEndpoint: '/broadcasting/auth',
                authTransport: 'ajax',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-XSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                },
                enabledTransports: ['ws', 'wss']
            });

            // Connection status callbacks
            this.pusher.connection.bind('error', (err) => {
                console.error('Pusher connection error:', err);
            });
        },
        loadConversations() {
            this.loading = true;
            fetch('{{ route("account.chat.conversations") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                this.conversations = data.conversations || [];
                this.loading = false;

                // If we came from an account page with ?conversation=ID, auto-select it
                if (this.initialConversationId) {
                    const found = this.conversations.find(
                        c => Number(c.id) === Number(this.initialConversationId)
                    );
                    if (found) {
                        this.selectConversation(found.id);
                    }
                }

                // Subscribe to realtime updates for all existing conversations
                this.conversations.forEach(c => {
                    this.subscribeToConversationChannel(c.id);
                });
            })
            .catch(error => {
                console.error('Error loading conversations:', error);
                this.conversations = [];
                this.loading = false;
            });
        },
        selectConversation(id) {
            this.selectedConversation = id;
            // Scroll to bottom when conversation is selected
            this.$nextTick(() => {
                this.loadMessages(id);
            });
        },
        subscribeToConversationChannel(conversationId) {
            if (!this.pusher) {
                return;
            }
            
            // Prevent duplicate subscriptions
            if (this.pusherChannels[conversationId]) {
                return;
            }
            
            const channelName = 'private-conversations.' + conversationId;
            
            // Mark as subscribing to prevent duplicates
            this.pusherChannels[conversationId] = true;
            
            const channel = this.pusher.subscribe(channelName);

            // Handle subscription success
            channel.bind('pusher:subscription_succeeded', () => {
                // Store the channel reference
                this.pusherChannels[conversationId] = channel;
            });

            // Handle subscription errors
            channel.bind('pusher:subscription_error', (status) => {
                console.error('Subscription error for', channelName, status);
                // Remove from channels on error so we can retry
                delete this.pusherChannels[conversationId];
            });

            // Handle incoming messages
            channel.bind('message.sent', (data) => {
                const convId = conversationId;
                const incoming = data.message || {};

                // Skip if this message is from the current user (they already have it locally)
                // This prevents the sender from seeing their own message twice
                if (incoming.sender_id && this.currentUserId) {
                    const incomingSenderId = Number(incoming.sender_id);
                    const currentUserId = Number(this.currentUserId);
                    if (incomingSenderId === currentUserId) {
                        return; // Exit early, don't process own messages from broadcast
                    }
                }

                // Ensure messages array exists
                if (!this.messages[convId]) {
                    this.messages[convId] = [];
                }
                
                // Check if message already exists (prevent duplicates by ID)
                const messageExists = this.messages[convId].some(msg => 
                    msg.id && incoming.id && Number(msg.id) === Number(incoming.id)
                );
                
                if (!messageExists) {
                    this.messages[convId].push(incoming);
                }

                // Update conversation preview and unread state
                const conversation = this.conversations.find(c => Number(c.id) === Number(convId));
                if (conversation) {
                    // Update last message - show attachment indicator if no text content
                    if (incoming.attachments && incoming.attachments.length > 0 && !incoming.content) {
                        const firstAttachment = incoming.attachments[0];
                        if (firstAttachment.file_type && firstAttachment.file_type.startsWith('image/')) {
                            conversation.lastMessage = '📷 Image';
                        } else if (firstAttachment.file_type && firstAttachment.file_type.startsWith('video/')) {
                            conversation.lastMessage = '🎥 Video';
                        } else {
                            conversation.lastMessage = '📎 ' + firstAttachment.file_name;
                        }
                    } else if (incoming.content) {
                        conversation.lastMessage = incoming.content;
                    }
                    
                    if (incoming.timestamp) {
                        conversation.timestamp = incoming.timestamp;
                    }
                    if (this.selectedConversation !== convId) {
                        conversation.unread = true;
                        conversation.unreadCount = (conversation.unreadCount || 0) + 1;
                    }
                }

                // Auto-scroll if current conversation is open
                if (this.selectedConversation === convId) {
                    this.scrollToBottom(true);
                }
            });

            // Handle payment status updates (paid badge)
            channel.bind('payment.status', (data) => {
                const convId = conversationId;
                const conversation = this.conversations.find(c => Number(c.id) === Number(convId));
                if (conversation && data) {
                    if (typeof data.paid !== 'undefined') {
                        conversation.paid = !!data.paid;
                    }
                    if (typeof data.orderId !== 'undefined') {
                        conversation.paidOrderId = data.orderId;
                    }
                    // If currently open, trigger header rerender
                    if (this.selectedConversation === convId) {
                        this.$nextTick(() => {});
                    }
                }
            });

            this.pusherChannels[conversationId] = channel;
        },
        loadMessages(conversationId) {
            if (this.messages[conversationId]) {
                // Scroll to bottom when messages are already loaded
                this.scrollToBottom(false);
                return;
            }
            
            fetch(`{{ route("account.chat.messages", ["id" => ":id"]) }}`.replace(':id', conversationId), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                this.messages[conversationId] = data.messages || [];
                // Update conversation's last message if needed
                const conversation = this.conversations.find(c => c.id === conversationId);
                if (conversation && data.messages && data.messages.length > 0) {
                    const lastMsg = data.messages[data.messages.length - 1];
                    // Show attachment indicator if no text content
                    if (lastMsg.attachments && lastMsg.attachments.length > 0 && !lastMsg.content) {
                        const firstAttachment = lastMsg.attachments[0];
                        if (firstAttachment.file_type && firstAttachment.file_type.startsWith('image/')) {
                            conversation.lastMessage = '📷 Image';
                        } else if (firstAttachment.file_type && firstAttachment.file_type.startsWith('video/')) {
                            conversation.lastMessage = '🎥 Video';
                        } else {
                            conversation.lastMessage = '📎 ' + firstAttachment.file_name;
                        }
                    } else {
                        conversation.lastMessage = lastMsg.content || 'No messages yet';
                    }
                    conversation.unread = false;
                    conversation.unreadCount = 0;
                }
                // Scroll to bottom after messages are loaded
                this.scrollToBottom(false);
            })
            .catch(error => {
                console.error('Error loading messages:', error);
                this.messages[conversationId] = [];
            });
        },
        getSelectedConversation() {
            if (this.selectedConversation === null) return null;
            return this.conversations.find(c => c.id === this.selectedConversation);
        },
        getSelectedMessages() {
            if (this.selectedConversation === null) return [];
            return this.messages[this.selectedConversation] || [];
        },
        scrollToBottom(smooth = true) {
            // Use $nextTick to ensure DOM is updated, then scroll
            this.$nextTick(() => {
                // Small delay to ensure all DOM updates are complete
                setTimeout(() => {
                    const messagesContainer = document.getElementById('messages-container');
                    if (messagesContainer) {
                        if (smooth) {
                            messagesContainer.scrollTo({
                                top: messagesContainer.scrollHeight,
                                behavior: 'smooth'
                            });
                        } else {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    } else {
                        // Fallback: try to find the container by class if ID doesn't work
                        const fallbackContainer = document.querySelector('.flex-1.overflow-y-auto.chat-scrollbar');
                        if (fallbackContainer) {
                            if (smooth) {
                                fallbackContainer.scrollTo({
                                    top: fallbackContainer.scrollHeight,
                                    behavior: 'smooth'
                                });
                            } else {
                                fallbackContainer.scrollTop = fallbackContainer.scrollHeight;
                            }
                        }
                    }
                }, 100); // Increased delay to ensure rendering is complete
            });
        },
        handleFileSelection(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => {
                // Validate file size (10MB max)
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File ${file.name} is too large. Maximum size is 10MB.`);
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'video/mp4', 'video/mov', 'video/avi', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.some(type => file.type === type)) {
                    alert(`File type ${file.type} is not allowed.`);
                    return;
                }
                
                // Create preview for images
                const fileObj = {
                    file: file,
                    name: file.name,
                    type: file.type,
                    size: file.size,
                    preview: null
                };
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        fileObj.preview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
                
                this.selectedFiles.push(fileObj);
            });
            
            // Reset input to allow selecting same file again
            event.target.value = '';
        },
        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },
        sendMessage(event) {
            event.preventDefault();
            
            if ((!this.messageInput.trim() && this.selectedFiles.length === 0) || this.sendingMessage || !this.selectedConversation) {
                return;
            }
            
            this.sendingMessage = true;
            
            const formData = new FormData();
            
            // Add text content if provided
            if (this.messageInput.trim()) {
                formData.append('content', this.messageInput.trim());
            }
            
            // Determine message type based on attachments
            let messageType = 'text';
            if (this.selectedFiles.length > 0) {
                const firstFile = this.selectedFiles[0].file;
                if (firstFile.type.startsWith('image/')) {
                    messageType = 'image';
                } else if (firstFile.type.startsWith('video/')) {
                    messageType = 'video';
                } else {
                    messageType = 'file';
                }
            }
            formData.append('message_type', messageType);
            
            // Add attachments
            this.selectedFiles.forEach((fileObj, index) => {
                formData.append(`attachments[${index}]`, fileObj.file);
            });
            
            fetch(`{{ route("account.chat.send", ["id" => ":id"]) }}`.replace(':id', this.selectedConversation), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Add message to local state
                    if (!this.messages[this.selectedConversation]) {
                        this.messages[this.selectedConversation] = [];
                    }
                    this.messages[this.selectedConversation].push(data.message);
                    
                    // Update conversation last message
                    const conversation = this.conversations.find(c => c.id === this.selectedConversation);
                    if (conversation) {
                        // Show attachment indicator if no text content
                        if (data.message.attachments && data.message.attachments.length > 0 && !data.message.content) {
                            const firstAttachment = data.message.attachments[0];
                            if (firstAttachment.file_type && firstAttachment.file_type.startsWith('image/')) {
                                conversation.lastMessage = '📷 Image';
                            } else if (firstAttachment.file_type && firstAttachment.file_type.startsWith('video/')) {
                                conversation.lastMessage = '🎥 Video';
                            } else {
                                conversation.lastMessage = '📎 ' + firstAttachment.file_name;
                            }
                        } else {
                            conversation.lastMessage = data.message.content || 'Sent an attachment';
                        }
                        conversation.timestamp = data.message.timestamp;
                    }
                    
                    // Clear input and files
                    this.messageInput = '';
                    this.selectedFiles = [];
                    
                    // Scroll to bottom after sending message
                    this.scrollToBottom(true);
                }
                this.sendingMessage = false;
            })
            .catch(error => {
                console.error('Error sending message:', error);
                this.sendingMessage = false;
            });
        }
    }
}
</script>
@endpush

@push('styles')
    <style>
        /* Custom Scrollbar for Chat List */
        .chat-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .chat-scrollbar::-webkit-scrollbar-track {
            background: #0e1015;
            border-radius: 4px;
        }
        .chat-scrollbar::-webkit-scrollbar-thumb {
            background: #1b1a1e;
            border-radius: 4px;
            border: 1px solid #2d2c31;
        }
        .chat-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #2d2c31;
        }
        
        /* Firefox scrollbar */
        .chat-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #1b1a1e #0e1015;
        }
        
        /* Alpine.js cloak */
        [x-cloak] { display: none !important; }
    </style>
@endpush
