<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatId;
    protected $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $this->chatId = config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
    }

    /**
     * Send a message to Telegram
     *
     * @param string $message
     * @param string|null $chatId Override default chat ID (optional)
     * @param string $parseMode 'HTML' or 'Markdown'
     * @return array|null
     */
    public function sendMessage(string $message, ?string $chatId = null, string $parseMode = 'HTML'): ?array
    {
        try {
            $response = Http::post($this->apiUrl, [
                'chat_id' => $chatId ?? $this->chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            $json = $response->json();

            if (!$response->successful() || !$json['ok']) {
                Log::error('Telegram API Error', [
                    'message' => $message,
                    'response' => $json,
                ]);
                return $json;
            }

            Log::info('Telegram message sent successfully', ['message' => $message]);
            return $json;

        } catch (\Throwable $e) {
            Log::error('Telegram Exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
