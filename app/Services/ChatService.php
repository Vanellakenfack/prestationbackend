<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function getOrCreateConversation(int $user1Id, int $user2Id): Conversation
    {
        return DB::transaction(function () use ($user1Id, $user2Id) {
            $userId1 = min($user1Id, $user2Id);
            $userId2 = max($user1Id, $user2Id);

            return Conversation::firstOrCreate([
                'user1_id' => $userId1,
                'user2_id' => $userId2
            ]);
        });
    }

    public function sendMessage(Conversation $conversation, User $sender, string $content): Message
    {
        return DB::transaction(function () use ($conversation, $sender, $content) {
            $message = $conversation->messages()->create([
                'id' => Str::uuid(),
                'sender_id' => $sender->id,
                'content' => $content
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message->load('sender');
        });
    }

    public function getUserConversations(User $user)
    {
        return Conversation::with([
                'participant1',
                'participant2',
                'latestMessage' => fn($q) => $q->with('sender')
            ])
            ->where(function($q) use ($user) {
                $q->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
            })
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) use ($user) {
                $conversation->other_user = $conversation->user1_id === $user->id 
                    ? $conversation->participant2 
                    : $conversation->participant1;
                return $conversation;
            });
    }

    public function markMessagesAsRead(Conversation $conversation, User $user): void
    {
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);
    }
}