<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Throwable;

class MessageController extends Controller
{
    /**
     * List messages in a chat (paginated)
     */
    public function index(Request $request, $chatId)
    {
        $chat = Chat::where('user_id', $request->user()->id)->findOrFail($chatId);

        $messages = Message::query()
            ->where('chat_id', $chat->id)
            ->with('media')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'per_page'     => $messages->perPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a message (text or image)
     */
    public function store(Request $request, $chatId)
    {
        $chat = Chat::where('user_id', $request->user()->id)->findOrFail($chatId);

        $data = $request->validate([
            'text'  => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png'],
        ]);

        if (empty($data['text']) && ! $request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'Message text or image is required.',
            ], 422);
        }

        $message = Message::create([
            'chat_id'     => $chat->id,
            'sender_id'   => $request->user()->id,
            'sender_type' => 'user',
            'text'        => $data['text'] ?? null,
            'type'        => $request->hasFile('image') ? 'image' : 'text',
            'status'      => 'sent',
        ]);

        // Handle image upload (if any)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('chat-images', 'public');

            $message->media()->create([
                'type'       => 'chat_image',
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        // Auto-set chat title from first user message & update last activity
        $chatUpdate = ['last_message_at' => now()];
        
        if ($chat->title === null && $message->sender_type === 'user' && $message->text) {
            $chatUpdate['title'] = Str::limit(trim($message->text), 40);
        }
        
        $chat->update($chatUpdate);

        $botMessage = null;
        try {
            if ($request->hasFile('image')) {
                $botMessage = $this->createImageAiMessage(
                    $chat,
                    $request->file('image'),
                    $request->input('analysis_mode')
                        ?? $request->input('modality')
                        ?? $request->input('mode')
                        ?? 'skin'
                );
            } elseif (!empty($data['text'])) {
                $botMessage = $this->createChatbotMessage($chat, $data['text']);
            }
        } catch (Throwable $exception) {
            Log::error('AI response failed', [
                'chat_id' => $chat->id,
                'message_id' => $message->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load('media'),
            'bot_response' => $botMessage,
        ], 201);
    }

    protected function createChatbotMessage(Chat $chat, string $text): ?Message
    {
        $baseUrl = config('services.ai.chatbot_url');
        if (empty($baseUrl)) {
            return null;
        }

        // Append /chat endpoint if not already in URL
        $url = str_ends_with($baseUrl, '/chat') ? $baseUrl : rtrim($baseUrl, '/') . '/chat';

        $response = Http::timeout(45)->post($url, [
            'message' => $text,
            'text' => $text,
        ]);

        if (! $response->successful()) {
            Log::warning('Chatbot API request failed', [
                'chat_id' => $chat->id,
                'status' => $response->status(),
            ]);
            return null;
        }

        $payload = $response->json();
        $botText = $this->extractChatbotText($payload, $response->body());

        if (! $botText) {
            Log::warning('Chatbot API returned empty response', [
                'chat_id' => $chat->id,
            ]);
            return null;
        }

        return Message::create([
            'chat_id' => $chat->id,
            'sender_id' => null,
            'sender_type' => 'ai',
            'text' => $botText,
            'type' => 'text',
            'status' => 'sent',
        ]);
    }

    protected function createImageAiMessage(Chat $chat, UploadedFile $image, string $mode): ?Message
    {
        $mode = $mode === 'eye' ? 'eye' : 'skin';
        $url = $mode === 'eye'
            ? config('services.ai.eye_url')
            : config('services.ai.skin_url');

        if (empty($url)) {
            return null;
        }

        $response = Http::timeout(60)
            ->attach(
                'file',  // Most FastAPI/Flask image APIs expect 'file' not 'image'
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName(),
                [
                    'Content-Type' => $image->getMimeType(),
                ]
            )
            ->post($url);

        if (! $response->successful()) {
            Log::warning('AI image API request failed', [
                'chat_id' => $chat->id,
                'status' => $response->status(),
                'mode' => $mode,
            ]);
            return null;
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            $payload = ['raw' => $response->body()];
        }

        $explanation = $payload['explanation'] ?? null;
        if (is_array($payload) && array_key_exists('explanation', $payload)) {
            unset($payload['explanation']);
        }

        return Message::create([
            'chat_id' => $chat->id,
            'sender_id' => null,
            'sender_type' => 'ai',
            'text' => null,
            'ai_result' => [
                'modality' => $mode,
                'payload' => $payload,
                'explanation' => $explanation,
            ],
            'type' => 'text',
            'status' => 'sent',
        ]);
    }

    protected function extractChatbotText($payload, string $rawBody): ?string
    {
        if (is_string($payload)) {
            return trim($payload) ?: null;
        }

        if (is_array($payload)) {
            $text = $payload['response'] ?? $payload['text'] ?? $payload['message'] ?? null;
            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }
        }

        $rawBody = trim($rawBody);
        return $rawBody !== '' ? $rawBody : null;
    }
}
