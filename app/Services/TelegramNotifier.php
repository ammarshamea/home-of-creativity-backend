<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramNotifier
{
    public function configured(string $bot = 'client'): bool
    {
        return filled($this->token($bot));
    }

    public function send(string $chatId, string $text, string $bot = 'client'): void
    {
        $token = $this->token($bot);
        if ($token === '') {
            throw new RuntimeException('Telegram bot is not configured.');
        }

        $response = Http::timeout(10)
            ->connectTimeout(3)
            ->retry([200, 500])
            ->acceptJson()
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ])
            ->throw();

        if ($response->json('ok') !== true) {
            throw new RuntimeException('Telegram did not accept the message.');
        }
    }

    private function token(string $bot): string
    {
        return $bot === 'staff'
            ? (string) config('services.telegram.staff_bot_token')
            : (string) config('services.telegram.bot_token');
    }
}
