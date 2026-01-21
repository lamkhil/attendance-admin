<?php

namespace App\Listeners;

use App\Events\QontakMessageReceived;
use App\Services\TelegramService;

class SendMessageToTelegram
{
    public function handle(QontakMessageReceived $event)
    {
        $message = $event->message->load(['room', 'sender']);
        $telegram = new TelegramService();

        // Kirim ke Telegram, service yang urus text / file
        $telegram->sendFromQontak($message);
    }
}
