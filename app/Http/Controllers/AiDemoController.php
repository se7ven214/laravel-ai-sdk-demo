<?php

namespace App\Http\Controllers;

use App\Ai\Agents\MeetingNotesAssistantRemembered;
use App\Http\Controllers\Concerns\InteractsWithRememberedConversations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class AiDemoController extends Controller
{
    use InteractsWithRememberedConversations;

    public function index()
    {
        return $this->renderPage();
    }

    public function prompt(Request $request)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:10000'],
            'provider' => ['required', 'in:openai,gemini'],
            'start_new' => ['nullable', 'boolean'],
        ]);

        $provider = $validated['provider'];
        $agent = $this->rememberedAgent($request->boolean('start_new'));

        try {
            $response = $agent->prompt($validated['prompt'], provider: $provider);
        } catch (Throwable $e) {
            return $this->renderPage(
                provider: $provider,
                lastPrompt: $validated['prompt'],
                error: $e->getMessage(),
            );
        }

        if (! $response instanceof StructuredAgentResponse) {
            return $this->renderPage(
                provider: $provider,
                lastPrompt: $validated['prompt'],
                error: 'Expected a structured agent response.',
            );
        }

        return $this->renderPage(
            provider: $provider,
            lastPrompt: $validated['prompt'],
            structuredResult: $response->toArray(),
        );
    }

    public function clear()
    {
        $conversationIds = DB::table('agent_conversations')
            ->where('user_id', $this->conversationUser()->id)
            ->pluck('id');

        if ($conversationIds->isNotEmpty()) {
            DB::table('agent_conversation_messages')
                ->whereIn('conversation_id', $conversationIds)
                ->delete();

            DB::table('agent_conversations')
                ->whereIn('id', $conversationIds)
                ->delete();
        }

        return redirect()->route('ai-demo.index');
    }

    protected function rememberedAgent(bool $startNew): MeetingNotesAssistantRemembered
    {
        $agent = MeetingNotesAssistantRemembered::make();

        if ($startNew) {
            return $agent->forUser($this->conversationUser());
        }

        $latestConversationId = $this->latestConversationId();

        if ($latestConversationId) {
            return $agent->continue($latestConversationId, as: $this->conversationUser());
        }

        return $agent->forUser($this->conversationUser());
    }

    protected function conversationHistory(?string $conversationId): array
    {
        if (! $conversationId) {
            return [];
        }

        return DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'role' => $message->role,
                    'prompt' => $message->role === 'user' ? $message->content : null,
                    'response' => $message->role === 'assistant' ? $message->content : null,
                    'tool_calls' => json_decode($message->tool_calls, true) ?: [],
                    'tool_results' => json_decode($message->tool_results, true) ?: [],
                ];
            })
            ->values()
            ->all();
    }

    protected function latestToolActivity(?string $conversationId): array
    {
        if (! $conversationId) {
            return ['toolCalls' => [], 'toolResults' => []];
        }

        $assistantMessage = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->orderByDesc('created_at')
            ->first(['tool_calls', 'tool_results']);

        if (! $assistantMessage) {
            return ['toolCalls' => [], 'toolResults' => []];
        }

        $toolCalls = json_decode($assistantMessage->tool_calls, true) ?: [];
        $toolResults = json_decode($assistantMessage->tool_results, true) ?: [];

        return [
            'toolCalls' => collect($toolCalls)
                ->map(fn (array $toolCall) => $toolCall['name'] ?? 'UnknownTool')
                ->values()
                ->all(),
            'toolResults' => collect($toolResults)
                ->map(function (array $toolResult) {
                    $result = $toolResult['result'] ?? null;

                    if (! is_string($result)) {
                        return $result;
                    }

                    $decoded = json_decode($result, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : $result;
                })
                ->values()
                ->all(),
        ];
    }

    protected function renderPage(
        ?string $provider = null,
        ?string $lastPrompt = null,
        ?array $structuredResult = null,
        ?string $error = null,
    ) {
        $conversationId = $this->latestConversationId();
        $toolActivity = $this->latestToolActivity($conversationId);

        return view('ai-demo', [
            'history' => $this->conversationHistory($conversationId),
            'provider' => $provider ?? config('ai.default'),
            'structuredResult' => $structuredResult,
            'lastPrompt' => $lastPrompt,
            'streamPrompt' => null,
            'conversationId' => $conversationId,
            'latestToolCalls' => $toolActivity['toolCalls'],
            'latestToolResults' => $toolActivity['toolResults'],
            'error' => $error,
        ]);
    }
}
