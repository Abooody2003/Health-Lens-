<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * List authenticated user's chats
     */
    public function index(Request $request)
    {
        $chats = Chat::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $chats->items(),
            'meta' => [
                'current_page' => $chats->currentPage(),
                'last_page'    => $chats->lastPage(),
                'per_page'     => $chats->perPage(),
                'total'        => $chats->total(),
            ],
        ]);
    }

    /**
     * Create a new chat
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $chat = Chat::create([
            'user_id' => $request->user()->id,
            'title'   => $data['title'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chat created successfully',
            'data' => $chat,
        ], 201);
    }

    /**
     * Delete a chat (and its messages)
     */
    public function destroy(Request $request, $id)
    {
        $chat = Chat::where('user_id', $request->user()->id)->findOrFail($id);

        $chat->delete(); // cascades to messages & media

        return response()->json([
            'success' => true,
            'message' => 'Chat deleted successfully',
        ]);
    }

    /**
     * Clear all chats for the authenticated user
     */
    public function clearAll(Request $request)
    {
        $deletedCount = Chat::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'All chats cleared successfully',
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }
}
