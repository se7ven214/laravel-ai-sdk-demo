<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TranslatorRemembered;
use App\Http\Controllers\Concerns\InteractsWithRememberedConversations;
use Illuminate\Http\Request;
use Laravel\Ai\Streaming\Events\TextDelta;
use Throwable;

class AiStreamController extends Controller
{
    use InteractsWithRememberedConversations;

    public function stream(Request $request)
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:10000'],
            'provider' => ['required', 'in:openai,gemini'],
            'target_language' => ['required', 'string', 'max:100'],
            'start_new' => ['nullable', 'boolean'],
        ]);

        $provider = $validated['provider'];
        $agent = $this->rememberedStreamingAgent($request->boolean('start_new'));
        $translationPrompt = $this->buildTranslationPrompt(
            $validated['prompt'],
            $validated['target_language'],
        );

        try {
            $stream = $agent->stream($translationPrompt, provider: $provider);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->stream(fn () => $this->sendStreamedResponse($stream), 200, [
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Content-Type' => 'text/event-stream',
            'Content-Encoding' => 'none',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    protected function rememberedStreamingAgent(bool $startNew): object
    {
        $agent = TranslatorRemembered::make();

        if ($startNew) {
            return $agent->forUser($this->conversationUser());
        }

        $conversationId = $this->latestConversationId();

        if ($conversationId) {
            return $agent->continue($conversationId, as: $this->conversationUser());
        }

        return $agent->forUser($this->conversationUser());
    }

    protected function sendStreamedResponse(iterable $stream): void
    {
        $this->disableOutputBuffering();

        try {
            foreach ($stream as $event) {
                if ($event instanceof TextDelta) {
                    $this->sendSseEvent([
                        'type' => 'text_delta',
                        'delta' => $event->delta,
                    ]);
                }
            }

            $this->sendSseEvent(['type' => 'done']);
        } catch (Throwable $e) {
            report($e);

            $this->sendSseEvent([
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function disableOutputBuffering(): void
    {
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        echo ':' . str_repeat(' ', 2048) . "\n\n";
        $this->flushOutput();
    }

    protected function sendSseEvent(array $payload): void
    {
        echo 'data: '.json_encode($payload)."\n\n";
        $this->flushOutput();
    }

    protected function flushOutput(): void
    {
        @ob_flush();
        flush();
    }

    protected function buildTranslationPrompt(string $content, string $targetLanguage): string
    {
        return <<<TEXT
Translate the following content into {$targetLanguage}.
Preserve the meaning, tone, names, numbers, and formatting.
Return only the translated content.

Content:
{$content}
TEXT;
    }
}
