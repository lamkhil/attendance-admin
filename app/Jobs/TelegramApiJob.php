<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramApiJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $method,
        protected array $payload,
        protected ?string $defaultToken
    ) {}

    public function handle(): void
    {
        $group = \App\Models\TelegramGroup::where(
            'chat_id',
            $this->payload['chat_id'] ?? null
        )->first();

        $token = $group?->bot_token ?? $this->defaultToken;

        Log::info('Telegram API dispatch', [
            'method'   => $this->method,
            'chat_id'  => $this->payload['chat_id'] ?? null,
            'use_bot'  => $group ? 'group_token' : 'default_token',
        ]);

        $response = Http::post(
            "https://api.telegram.org/bot{$token}/{$this->method}",
            $this->payload
        );

        if ($response->failed()) {
            Log::error('Telegram API failed', [
                'method'   => $this->method,
                'chat_id'  => $this->payload['chat_id'] ?? null,
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
        } else {
            Log::info('Telegram API success', [
                'method'  => $this->method,
                'chat_id' => $this->payload['chat_id'] ?? null,
            ]);
        }
    }
}
