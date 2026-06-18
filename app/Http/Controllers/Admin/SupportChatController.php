<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SupportChatController extends Controller
{
    public function inbox(Request $request)
    {
        $query = Message::select('receiver_id')
            ->distinct()
            ->with(['receiver' => function($q) {
                $q->select('id', 'username', 'email', 'created_at');
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            
            $query->whereHas('receiver', function($q) use ($searchTerm) {
                $q->where('username', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by message date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Sort by latest message
        $users = $query->orderBy('created_at', 'desc')
            ->get()
            ->pluck('receiver')
            ->unique('id')
            ->filter()
            ->values();

        // Check if read_at column exists
        $hasReadAtColumn = Schema::hasColumn('messages', 'read_at');

        // Add last message info for each user
        $users = $users->map(function($user) use ($hasReadAtColumn) {
            $lastMessage = Message::where(function($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->orWhere(function($subQ) use ($user) {
                      $subQ->where('sender_id', $user->id)
                           ->where('sender_type', \App\Models\User::class);
                  });
            })->latest()->first();

            $user->last_message = $lastMessage;
            
            // Only calculate unread count if read_at column exists
            if ($hasReadAtColumn) {
                $user->unread_count = Message::where('receiver_id', $user->id)
                    ->where('sender_type', \App\Models\User::class)
                    ->whereNull('read_at')
                    ->count();
            } else {
                $user->unread_count = 0; // Default to 0 if column doesn't exist
            }

            return $user;
        });

        // Sorting options
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'name_asc':
                    $users = $users->sortBy(function($user) {
                        return $user->username ?? $user->email;
                    });
                    break;
                case 'name_desc':
                    $users = $users->sortByDesc(function($user) {
                        return $user->username ?? $user->email;
                    });
                    break;
                case 'newest':
                    $users = $users->sortByDesc(function($user) {
                        return $user->last_message ? $user->last_message->created_at : $user->created_at;
                    });
                    break;
                case 'oldest':
                    $users = $users->sortBy(function($user) {
                        return $user->last_message ? $user->last_message->created_at : $user->created_at;
                    });
                    break;
                case 'unread':
                    if ($hasReadAtColumn) {
                        $users = $users->sortByDesc('unread_count');
                    }
                    break;
            }
        }

        $totalConversations = Message::select('receiver_id')->distinct()->count();
        $filteredCount = $users->count();

        return view('admin.support.inbox', compact('users', 'totalConversations', 'filteredCount'));
    }

    public function view(User $user, Request $request)
    {
        $query = Message::where('receiver_id', $user->id)
            ->orWhere(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->where('sender_type', \App\Models\User::class);
            });

        // Search within conversation
        if ($request->filled('message_search')) {
            $query->where('message', 'LIKE', "%{$request->message_search}%");
        }

        $messages = $query->orderBy('created_at')->get();

        // Mark messages as read only if read_at column exists
        if (Schema::hasColumn('messages', 'read_at')) {
            Message::where('receiver_id', $user->id)
                ->where('sender_type', \App\Models\User::class)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        if (request()->has('polling')) {
            return response()->view('admin.support.chat', compact('user', 'messages'))
                ->header('Content-Type', 'text/html');
        }

        return view('admin.support.chat', compact('user', 'messages'));
    }

    public function reply(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required_without:image|nullable|string|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $path = $request->hasFile('image')
            ? $request->file('image')->store('chat_images', 'public')
            : null;

        $messageData = [
            'sender_id'    => auth('admin')->id(),
            'sender_type'  => \App\Models\Admin::class,
            'receiver_id'  => $user->id,
            'message'      => $request->message,
            'image_path'   => $path,
        ];

        // Only add read_at if column exists
        if (Schema::hasColumn('messages', 'read_at')) {
            $messageData['read_at'] = null; // Admin messages start as unread for users
        }

        Message::create($messageData);

        return back()->with('reply_sent', 'Reply sent to user.');
    }

    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $searchTerm = $request->q;

        // Search users
        $users = User::where('username', 'LIKE', "%{$searchTerm}%")
            ->orWhere('email', 'LIKE', "%{$searchTerm}%")
            ->whereHas('sentMessages')
            ->orWhereHas('receivedMessages')
            ->limit(10)
            ->get(['id', 'username', 'email']);

        // Search messages
        $messages = Message::where('message', 'LIKE', "%{$searchTerm}%")
            ->with(['sender', 'receiver'])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'users' => $users,
            'messages' => $messages,
            'total_results' => $users->count() + $messages->count()
        ]);
    }

    public function markAsRead(Request $request, User $user)
    {
        // Only mark as read if column exists
        if (Schema::hasColumn('messages', 'read_at')) {
            Message::where('receiver_id', $user->id)
                ->where('sender_type', \App\Models\User::class)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }



    public function getStats()
    {
        $totalConversations = Message::select('receiver_id')->distinct()->count();
        
        $unreadMessages = 0;
        if (Schema::hasColumn('messages', 'read_at')) {
            $unreadMessages = Message::where('sender_type', \App\Models\User::class)
                ->whereNull('read_at')
                ->count();
        }
        
        $todayMessages = Message::whereDate('created_at', today())->count();
        $avgResponseTime = $this->calculateAverageResponseTime();

        return response()->json([
            'total_conversations' => $totalConversations,
            'unread_messages' => $unreadMessages,
            'today_messages' => $todayMessages,
            'avg_response_time' => $avgResponseTime
        ]);
    }

    public function deleteMessage(Message $message)
    {
        // Only allow admins to delete messages
        if (!auth('admin')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Delete associated image if exists
            if ($message->image_path) {
                Storage::disk('public')->delete($message->image_path);
            }
            
            $message->delete();
            
            return response()->json(['success' => true, 'message' => 'Message deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting message'], 500);
        }
    }

    public function deleteConversation(User $user)
    {
        // Only allow admins to delete conversations
        if (!auth('admin')->check()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Unauthorized');
        }

        try {
            $messages = Message::where('receiver_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->where('sender_type', \App\Models\User::class);
                })
                ->get();

            // Delete associated images
            foreach ($messages as $message) {
                if ($message->image_path) {
                    Storage::disk('public')->delete($message->image_path);
                }
            }

            // Delete all messages in the conversation
            Message::where('receiver_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->where('sender_type', \App\Models\User::class);
                })
                ->delete();

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Conversation deleted successfully']);
            }
            
            return back()->with('success', 'Conversation deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting conversation'], 500);
            }
            
            return back()->with('error', 'Error deleting conversation');
        }
    }
}