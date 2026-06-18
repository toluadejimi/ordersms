<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ChatController extends Controller
{
    /**
     * Show chat messages between the logged-in user and admin.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->where('sender_type', get_class($user));
        })
        ->orWhere(function ($q) use ($user) {
            $q->where('receiver_id', $user->id)
              ->where('sender_type', \App\Models\Admin::class);
        });

        // Search within messages
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where('message', 'LIKE', "%{$searchTerm}%");
        }

        // For users, only show messages that aren't deleted by them
        if (Schema::hasColumn('messages', 'deleted_by_user')) {
            $query->where(function($q) use ($user) {
                $q->whereNull('deleted_by_user')
                  ->orWhere('deleted_by_user', false)
                  ->orWhere(function($subQ) use ($user) {
                      $subQ->where('sender_type', \App\Models\Admin::class)
                           ->where('receiver_id', $user->id);
                  });
            });
        }

        $messages = $query->orderBy('created_at')->get();

        // Only update read timestamp if it's NOT an AJAX polling request
        if (!request()->ajax()) {
            $user->last_chat_read_at = now();
            $user->save();
        }

        // If polling via AJAX, return only the messages data
        if (request()->ajax()) {
            return response()->json([
                'messages' => $messages->map(function($msg) {
                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'image_path' => $msg->image_path,
                        'sender_id' => $msg->sender_id,
                        'sender_type' => $msg->sender_type,
                        'created_at' => $msg->created_at->diffForHumans(),
                        'is_user' => $msg->sender_id === auth()->id() && $msg->sender_type === \App\Models\User::class
                    ];
                }),
                'count' => $messages->count()
            ]);
        }

        // Otherwise return the full chat page
        return view('chat.index', compact('messages'));
    }

    /**
     * Send message from user to admin.
     */
   public function send(Request $request)
{
    $request->validate([
        'message' => 'required_without:image|nullable|string|max:1000',
        'image' => 'nullable|image|max:2048',
    ]);

    $user = auth()->user();
    $path = null;

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('chat_images', 'public');
    }

    $messageData = [
        'sender_id'    => $user->id,
        'sender_type'  => get_class($user), // App\Models\User
        'receiver_id'  => $user->id,
        'message'      => $request->message,
        'image_path'   => $path,
    ];

    // Add deleted_by_user column if it exists
    if (\Schema::hasColumn('messages', 'deleted_by_user')) {
        $messageData['deleted_by_user'] = false;
    }

    $message = \App\Models\Message::create($messageData);

    // 🔔 Send Telegram Notification to Admin
    try {
        $text = "<b>📩 New User Message</b>\n\n"
              . "<b>From:</b> {$user->name} (ID: {$user->id})\n"
              . "<b>Email:</b> {$user->email}\n"
              . "<b>Message:</b>\n"
              . htmlspecialchars($request->message ?? '[Image only]');

        app(\App\Services\TelegramService::class)->sendMessage($text);
    } catch (\Throwable $e) {
        \Log::error('Failed to send Telegram alert: ' . $e->getMessage());
    }

    return back()->with('message_sent', 'Message sent successfully.');
}


    /**
     * Delete user's own message (soft delete from user's perspective)
     */
    public function deleteMessage(Request $request, Message $message)
    {
        $user = auth()->user();

        // Check if user owns this message
        if ($message->sender_id !== $user->id || $message->sender_type !== get_class($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            // Check if deleted_by_user column exists
            if (Schema::hasColumn('messages', 'deleted_by_user')) {
                // Soft delete - mark as deleted by user but keep for admin
                $message->update(['deleted_by_user' => true]);
            } else {
                // Hard delete if column doesn't exist
                if ($message->image_path) {
                    Storage::disk('public')->delete($message->image_path);
                }
                $message->delete();
            }

            return response()->json([
                'success' => true, 
                'message' => 'Message deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error deleting message'
            ], 500);
        }
    }

    /**
     * Search messages for the current user
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $user = auth()->user();
        $searchTerm = $request->q;

        $query = Message::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->where('sender_type', get_class($user));
        })
        ->orWhere(function ($q) use ($user) {
            $q->where('receiver_id', $user->id)
              ->where('sender_type', \App\Models\Admin::class);
        });

        // Apply search filter
        $query->where('message', 'LIKE', "%{$searchTerm}%");

        // For users, only show messages that aren't deleted by them
        if (Schema::hasColumn('messages', 'deleted_by_user')) {
            $query->where(function($q) use ($user) {
                $q->whereNull('deleted_by_user')
                  ->orWhere('deleted_by_user', false)
                  ->orWhere(function($subQ) use ($user) {
                      // Show admin messages even if user "deleted" them from their view
                      $subQ->where('sender_type', \App\Models\Admin::class)
                           ->where('receiver_id', $user->id);
                  });
            });
        }

        $messages = $query->orderBy('created_at')
                         ->limit(20)
                         ->get();

        return response()->json([
            'messages' => $messages,
            'total_results' => $messages->count()
        ]);
    }

    /**
     * Clear chat for user (mark all their messages as deleted from their view)
     */
    public function clearChat(Request $request)
    {
        $user = auth()->user();

        try {
            if (Schema::hasColumn('messages', 'deleted_by_user')) {
                // Soft delete all user's messages from their view
                Message::where('sender_id', $user->id)
                    ->where('sender_type', get_class($user))
                    ->update(['deleted_by_user' => true]);
            } else {
                // Hard delete if column doesn't exist
                $userMessages = Message::where('sender_id', $user->id)
                    ->where('sender_type', get_class($user))
                    ->get();

                foreach ($userMessages as $message) {
                    if ($message->image_path) {
                        Storage::disk('public')->delete($message->image_path);
                    }
                    $message->delete();
                }
            }

            return response()->json([
                'success' => true, 
                'message' => 'Chat cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error clearing chat'
            ], 500);
        }
    }

    /**
     * Admin replies to user.
     */
    public function adminReply(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required_without:image|nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $admin = auth('admin')->user();
        $path = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
        }

        $messageData = [
            'sender_id'    => $admin->id,
            'sender_type'  => \App\Models\Admin::class,
            'receiver_id'  => $user->id,
            'message'      => $request->message,
            'image_path'   => $path,
        ];

        // Add deleted_by_user column if it exists
        if (Schema::hasColumn('messages', 'deleted_by_user')) {
            $messageData['deleted_by_user'] = false;
        }

        Message::create($messageData);

        return back()->with('reply_sent', 'Reply sent to user successfully.');
    }
}