<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class GroqService
{
    public function chat(array $messages, array $opts = []): string
    {
        $payload = array_merge([
            'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
            'messages' => $messages,
            'temperature' => $opts['temperature'] ?? 0.2,
            'top_p' => $opts['top_p'] ?? 1,
            'max_completion_tokens' => $opts['max_completion_tokens'] ?? 650,
            'stream' => false,
        ], $opts);

        $res = Http::withToken(config('services.groq.api_key'))
            ->acceptJson()
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', $payload)
            ->throw()
            ->json();

        return (string) data_get($res, 'choices.0.message.content', '');
    }
}
