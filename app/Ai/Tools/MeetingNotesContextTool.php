<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class MeetingNotesContextTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get the current local date and time so the meeting notes assistant can resolve relative deadlines such as today, tomorrow, Friday, or next week.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $timezone = $request->string('timezone', config('app.timezone', 'UTC'))->toString();
        $now = Carbon::now($timezone);

        return json_encode([
            'timezone' => $timezone,
            'iso_datetime' => $now->toIso8601String(),
            'date' => $now->toDateString(),
            'time' => $now->format('H:i:s'),
            'day_of_week' => $now->englishDayOfWeek,
            'guidance' => 'Use this local date and time to interpret relative deadlines mentioned in the meeting notes.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'timezone' => $schema->string()->description('IANA timezone name such as Asia/Ho_Chi_Minh or UTC')->required(),
        ];
    }
}
