<?php

namespace App\Listeners;

use App\Events\QontakMessageReceived;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMessageToTelegram implements ShouldQueue
{
    public function handle(QontakMessageReceived $event)
    {
        $message = $event->message->load(['room', 'sender']);
        $telegram = new TelegramService();

        // Kirim ke Telegram, service yang urus text / file
        $telegram->sendFromQontak($message);
    }
}
