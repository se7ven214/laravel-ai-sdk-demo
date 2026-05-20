<?php

namespace App\Ai\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Stringable;

class TranslatorRemembered implements Agent, Conversational
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
You are a translation assistant.

Always respond in the target language requested by the user.
If the user does not specify a target language, translate the text into Vietnamese.
Preserve the meaning, tone, names, numbers, and formatting of the source text.
Do not explain the translation unless the user explicitly asks for notes.
Return only the translated content.
TEXT;
    }
}
