<?php

namespace App\Http\Controllers\API;

use App\Events\Message;
use App\Http\Controllers\Controller;
use App\Events\Message\API;
use App\Models\API\Chat;
use Illuminate\Http\Request;
use MessageSent;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    // app/Http/Controllers/ChatController.php

    // User fetches their own messages
    public function index(Request $request)
    {
        $messages = Chat::where('receiverid', $request->user()->id)
            ->with('sender:id,name')
            ->latest()
            ->paginate(50);

        // Mark incoming Chats as read
        Chat::where('receiverid', $request->user()->id)
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    // Send a message (user or admin)
    public function store(Request $request)
    {
        $isAdmin = $request->user()->hasRole('admin');

        $userId  = $isAdmin
            ? $request->input('receiverid')   // admin specifies target user
            : $request->user()->id;        // user sends to themselves

        $message = Chat::create([
            'id'         => (string) Str::uuid(),
            'senderid'     => $userId,
            'receiverid'   => $request->user()->id,
            'text' => $isAdmin ? 'admin' : 'user',
            'body'        => $request->body,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('sender:id,name'), 201);
    }

    // Admin: list all conversations (one row per user, latest message)
    public function conversations()
    {
        $conversations = Chat::with('user:id,name,email')
            ->selectRaw('receiverid, MAX(id) as last_message_id')
            ->groupBy('receiverid')
            ->get()
            ->map(fn($row) => [
                'user'         => $row->user,
                'last_message' => Chat::find($row->last_message_id),
                'unread_count' => Chat::where('receiverid', $row->receiverid)
                    ->where('sender_type', 'user')
                    ->whereNull('read_at')
                    ->count(),
            ]);

        return response()->json($conversations);
    }

    // Admin: get all messages for a specific user
    public function thread(User $user)
    {
        $messages = Chat::where('receiverid', $user->id)
            ->with('sender:id,name')
            ->latest()
            ->paginate(50);

        Chat::where('receiverid', $user->id)
            ->where('sender_type', 'user')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }
}
