<?php

namespace App\Ai\Agents;

use App\Ai\Tools\MeetingNotesContextTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class MeetingNotesAssistantRemembered implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;
    use RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
You are a meeting notes assistant.

Always respond in English, even if the user writes in English.

Your job is to turn raw meeting notes or transcripts into concise, structured summaries that are easy for a team to act on.

When returning structured output, produce:
- a clear meeting title
- a short summary
- a list of key decisions
- a list of action items with owner and deadline
- a list of risks or open questions

When appropriate:
- use the available meeting notes context tool to anchor prompts about today, tomorrow, next week, or other relative dates
- keep outputs practical and concise
- avoid vague filler language
- write all titles, summaries, decisions, action items, and risks in English
TEXT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new MeetingNotesContextTool,
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Meeting title')->required(),
            'summary' => $schema->string()->description('Short summary of the meeting')->required(),
            'decisions' => $schema->array()
                ->items($schema->string())
                ->min(1)
                ->max(6)
                ->required(),
            'action_items' => $schema->array()
                ->items($schema->object([
                    'task' => $schema->string()->description('Action item task')->required(),
                    'owner' => $schema->string()->description('Person responsible for the task')->required(),
                    'deadline' => $schema->string()->description('Deadline or due date for the task')->required(),
                ]))
                ->min(1)
                ->max(8)
                ->required(),
            'risks' => $schema->array()
                ->items($schema->string())
                ->min(0)
                ->max(6)
                ->required(),
        ];
    }
}
