<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

trait InteractsWithRememberedConversations
{
    protected function conversationUser(): object
    {
        return (object) ['id' => 99];
    }

    protected function latestConversationId(): ?string
    {
        return DB::table('agent_conversations')
            ->where('user_id', $this->conversationUser()->id)
            ->orderByDesc('updated_at')
            ->pluck('id')
            ->first(fn (string $conversationId) => ! $this->conversationHasUnresolvedToolCalls($conversationId));
    }

    protected function conversationHasUnresolvedToolCalls(string $conversationId): bool
    {
        return DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->orderBy('created_at')
            ->get(['tool_calls', 'tool_results'])
            ->contains(function ($message) {
                $toolCalls = json_decode($message->tool_calls, true) ?: [];
                $toolResults = json_decode($message->tool_results, true) ?: [];

                return count($toolCalls) !== count($toolResults);
            });
    }
}
