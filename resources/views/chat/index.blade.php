@extends('layouts.app')
@section('content')
<div class="mx-auto">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Support Chat</h1>
                    <p class="text-orange-100">Get help from our support team</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="clearChat()" 
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors duration-200"
                            title="Clear all your messages">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear Chat
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Messages Bar -->
        <div class="bg-gray-50 border-b border-gray-200 p-4">
            <form method="GET" class="flex items-center space-x-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search your messages..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                </div>
                
                <button type="submit" 
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Search</span>
                </button>
                
                @if(request('search'))
                    <a href="{{ route('chat.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </form>
            
            @if(request('search'))
                <div class="mt-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                    Searching for: "<strong>{{ request('search') }}</strong>" 
                    <span class="text-gray-500">({{ $messages->count() }} {{ $messages->count() === 1 ? 'result' : 'results' }})</span>
                </div>
            @endif
        </div>

        <!-- Flash Message -->
        @if(session('message_sent'))
            <div id="flash-message" class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 m-4 rounded-r-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('message_sent') }}
                </div>
            </div>
        @endif

        <!-- Chat Messages -->
        <div id="chat-messages" class="h-96 overflow-y-auto p-4 bg-gray-50 border-b border-gray-200">
            @forelse ($messages as $msg)
                <div class="mb-4 flex {{ $msg->sender_id === auth()->id() && $msg->sender_type === \App\Models\User::class ? 'justify-end' : 'justify-start' }}" 
                     data-message-id="{{ $msg->id }}">
                    <div class="max-w-md">
                        @if ($msg->sender_id === auth()->id() && $msg->sender_type === \App\Models\User::class)
                            <!-- User Message -->
                            <div class="bg-orange-500 text-white p-4 rounded-lg rounded-br-none shadow-md group {{ request('search') && stripos($msg->message, request('search')) !== false ? 'ring-2 ring-yellow-400' : '' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-orange-100">You</span>
                                    <!-- Delete Button for User's Own Messages -->
                                    <button onclick="deleteMessage({{ $msg->id }})" 
                                            class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-red-500 hover:bg-red-600 text-white p-1 rounded text-xs"
                                            title="Delete message">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                @if ($msg->image_path)
                                    <img src="{{ asset('storage/app/public/' . $msg->image_path) }}" class="w-48 h-auto mb-3 rounded-lg border-2 border-orange-300">
                                @endif
                                @if ($msg->message)
                                    <p class="text-sm leading-relaxed">
                                        @if(request('search'))
                                            {!! str_ireplace(request('search'), '<mark class="bg-yellow-200 text-gray-800 px-1 rounded">' . request('search') . '</mark>', e($msg->message)) !!}
                                        @else
                                            {{ $msg->message }}
                                        @endif
                                    </p>
                                @endif
                                <p class="text-xs text-orange-100 mt-2 text-right">{{ $msg->created_at->diffForHumans() }}</p>
                            </div>
                        @else
                            <!-- Support Message -->
                            <div class="bg-white border border-gray-200 p-4 rounded-lg rounded-bl-none shadow-md {{ request('search') && stripos($msg->message, request('search')) !== false ? 'ring-2 ring-yellow-400' : '' }}">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Support Team</span>
                                </div>
                                @if ($msg->image_path)
                                    <img src="{{ asset('storage/app/public/' . $msg->image_path) }}" class="w-48 h-auto mb-3 rounded-lg border border-gray-200">
                                @endif
                                @if ($msg->message)
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        @if(request('search'))
                                            {!! str_ireplace(request('search'), '<mark class="bg-yellow-200 text-gray-800 px-1 rounded">' . request('search') . '</mark>', e($msg->message)) !!}
                                        @else
                                            {{ $msg->message }}
                                        @endif
                                    </p>
                                @endif
                                <p class="text-xs text-gray-500 mt-2">{{ $msg->created_at->diffForHumans() }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <!-- No Messages State -->
                <div class="text-center py-12">
                    @if(request('search'))
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Messages Found</h3>
                        <p class="text-gray-500 mb-4">No messages contain "{{ request('search') }}"</p>
                        <a href="{{ route('chat.index') }}" 
                           class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Show All Messages
                        </a>
                    @else
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Messages Yet</h3>
                        <p class="text-gray-500">Start a conversation with our support team below.</p>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Message Input -->
        <div class="bg-white p-4 border-t border-gray-200">
            <form action="{{ route('chat.send') }}" method="POST" enctype="multipart/form-data" id="chat-form">
                @csrf
                <div class="flex flex-col space-y-3">
                    <textarea 
                        name="message" 
                        rows="3" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none" 
                        placeholder="Type your message here..."
                    ></textarea>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <label class="flex items-center cursor-pointer">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span class="text-sm text-gray-600" id="file-label">Attach Image</span>
                                <input type="file" name="image" class="hidden" accept="image/*">
                            </label>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2"
                        >
                            <span>Send</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentMessageCount = {{ $messages->count() }};
    let pollingInterval;

    // Delete individual message
    function deleteMessage(messageId) {
        if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
            fetch(`{{ route('chat.delete-message', ':id') }}`.replace(':id', messageId), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the message element
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageElement) {
                        messageElement.style.transition = 'opacity 0.3s ease-out';
                        messageElement.style.opacity = '0';
                        setTimeout(() => {
                            messageElement.remove();
                            currentMessageCount--;
                        }, 300);
                    }
                    
                    // Show success message
                    showNotification('Message deleted successfully', 'success');
                } else {
                    showNotification(data.message || 'Error deleting message', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting message:', error);
                showNotification('Error deleting message', 'error');
            });
        }
    }

    // Clear entire chat
    function clearChat() {
        if (confirm('Are you sure you want to clear all your messages? This will hide all your messages from your view but they will still be visible to support staff.')) {
            fetch(`{{ route('chat.clear') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showNotification(data.message || 'Error clearing chat', 'error');
                }
            })
            .catch(error => {
                console.error('Error clearing chat:', error);
                showNotification('Error clearing chat', 'error');
            });
        }
    }

    // Show notification
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 ${
            type === 'success' 
                ? 'bg-green-500 text-white' 
                : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Generate message HTML
    function generateMessageHTML(msg) {
        const isUser = msg.is_user;
        const searchTerm = "{{ request('search') }}";
        const highlightedMessage = searchTerm && msg.message ? 
            msg.message.replace(new RegExp(searchTerm, 'gi'), `<mark class="bg-yellow-200 text-gray-800 px-1 rounded">${searchTerm}</mark>`) : 
            msg.message;

        return `
            <div class="mb-4 flex ${isUser ? 'justify-end' : 'justify-start'}" data-message-id="${msg.id}">
                <div class="max-w-md">
                    ${isUser ? `
                        <!-- User Message -->
                        <div class="bg-orange-500 text-white p-4 rounded-lg rounded-br-none shadow-md group ${searchTerm && msg.message && msg.message.toLowerCase().includes(searchTerm.toLowerCase()) ? 'ring-2 ring-yellow-400' : ''}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-orange-100">You</span>
                                <button onclick="deleteMessage(${msg.id})" 
                                        class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-red-500 hover:bg-red-600 text-white p-1 rounded text-xs"
                                        title="Delete message">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            ${msg.image_path ? `<img src="{{ asset('storage/app/public/') }}/${msg.image_path}" class="w-48 h-auto mb-3 rounded-lg border-2 border-orange-300">` : ''}
                            ${msg.message ? `<p class="text-sm leading-relaxed">${highlightedMessage}</p>` : ''}
                            <p class="text-xs text-orange-100 mt-2 text-right">${msg.created_at}</p>
                        </div>
                    ` : `
                        <!-- Support Message -->
                        <div class="bg-white border border-gray-200 p-4 rounded-lg rounded-bl-none shadow-md ${searchTerm && msg.message && msg.message.toLowerCase().includes(searchTerm.toLowerCase()) ? 'ring-2 ring-yellow-400' : ''}">
                            <div class="flex items-center mb-2">
                                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Support Team</span>
                            </div>
                            ${msg.image_path ? `<img src="{{ asset('storage/app/public/') }}/${msg.image_path}" class="w-48 h-auto mb-3 rounded-lg border border-gray-200">` : ''}
                            ${msg.message ? `<p class="text-sm text-gray-700 leading-relaxed">${highlightedMessage}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-2">${msg.created_at}</p>
                        </div>
                    `}
                </div>
            </div>
        `;
    }

    // Fetch new messages via polling
    function fetchChatMessages() {
        // Don't auto-refresh if user is searching
        if ({{ request('search') ? 'true' : 'false' }}) {
            return;
        }

        fetch("{{ route('chat.index') }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.count !== currentMessageCount) {
                const chatContainer = document.querySelector('#chat-messages');
                const wasAtBottom = chatContainer.scrollTop >= (chatContainer.scrollHeight - chatContainer.clientHeight - 50);
                
                // Clear and rebuild messages
                let messagesHTML = '';
                data.messages.forEach(msg => {
                    messagesHTML += generateMessageHTML(msg);
                });
                
                if (messagesHTML === '') {
                    messagesHTML = `
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">No Messages Yet</h3>
                            <p class="text-gray-500">Start a conversation with our support team below.</p>
                        </div>
                    `;
                }
                
                chatContainer.innerHTML = messagesHTML;
                currentMessageCount = data.count;
                
                // Only auto-scroll if user was already at bottom
                if (wasAtBottom) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
                
                console.log('Chat messages updated - New count:', data.count);
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
        });
    }

    // Polling functions
    function startPolling() {
        pollingInterval = setInterval(fetchChatMessages, 3000); // Poll every 3 seconds
        console.log('Polling started');
    }
    
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            console.log('Polling stopped');
        }
    }

    // Auto-hide flash message after 5 seconds
    const flashMessage = document.getElementById('flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.transition = 'opacity 0.5s ease-out';
            flashMessage.style.opacity = '0';
            setTimeout(() => {
                flashMessage.remove();
            }, 500);
        }, 5000);
    }

    // Auto-resize textarea
    const textarea = document.querySelector('textarea[name="message"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Ctrl+Enter to send
        textarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    }

    // File input feedback
    const fileInput = document.querySelector('input[type="file"]');
    const fileLabel = document.getElementById('file-label');
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileLabel.textContent = this.files[0].name;
                fileLabel.classList.add('text-orange-600', 'font-medium');
            } else {
                fileLabel.textContent = 'Attach Image';
                fileLabel.classList.remove('text-orange-600', 'font-medium');
            }
        });
    }

    // Search functionality
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Focus on Ctrl+F
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                searchInput.focus();
            }
        });

        // Auto-scroll to first highlighted message
        if ({{ request('search') ? 'true' : 'false' }}) {
            const firstHighlighted = document.querySelector('.ring-yellow-400');
            if (firstHighlighted) {
                setTimeout(() => {
                    firstHighlighted.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 500);
            }
        }
    }

    // Initialize polling when page loads
    window.addEventListener('load', function() {
        console.log('Page loaded, starting chat polling');
        
        if (!{{ request('search') ? 'true' : 'false' }}) {
            // Start polling
            startPolling();
        }
        
        // Scroll to bottom on page load (unless searching)
        const chatBox = document.querySelector('#chat-messages');
        if (!{{ request('search') ? 'true' : 'false' }}) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    });

    // Stop polling when page is hidden, resume when visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopPolling();
        } else {
            if (!{{ request('search') ? 'true' : 'false' }}) {
                startPolling();
            }
        }
    });

    // Stop polling when user submits a message to avoid conflicts
    const chatForm = document.getElementById('chat-form');
    if (chatForm) {
        chatForm.addEventListener('submit', function() {
            stopPolling();
            // Restart polling after form submission
            setTimeout(() => {
                if (!{{ request('search') ? 'true' : 'false' }}) {
                    startPolling();
                }
            }, 1000);
        });
    }
</script>
@endsection