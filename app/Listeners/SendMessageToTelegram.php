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

        // Base caption / text
        $baseText = <<<TXT
👤 {$message->sender->name}
🕒 {$message->created_at}
TXT;

        // Tambahkan text dari pesan jika ada
        $text = $baseText;
        if (!empty($message->text)) {
            $text .= "\n💬 {$message->text}";
        }

        // Kirim ke Telegram
        if ($message->type === 'text') {
            $telegram->sendFromQontak($message->room, $text);
        } else {
            // file / image
            $telegram->sendFromQontak($message->room, $text, $message->file_url, $message->type);
        }
    }
}
