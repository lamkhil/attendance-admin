<?php

namespace App\Services;

use App\Models\RoomTelegramThread;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $botToken;
    protected string $channelId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->channelId = config('services.telegram.chat_id');
    }

    protected function api(string $method, array $payload)
    {
        return Http::post(
            "https://api.telegram.org/bot{$this->botToken}/{$method}",
            $payload
        )->json();
    }

    /**
     * Kirim pesan dari Qontak
     */
    public function sendFromQontak($room, string $text, ?string $fileUrl = null, ?string $type = null)
    {
        $thread = RoomTelegramThread::where('room_id', $room->id)->first();

        // 1️⃣ Belum pernah kirim ke Telegram
        if (!$thread) {
            if ($fileUrl) {
                return $this->sendFileNewConversation($room, $text, $fileUrl, $type);
            }
            return $this->sendNewConversation($room, $text);
        }

        // 2️⃣ Sudah ada thread diskusi → reply
        if ($thread->telegram_discussion_message_id) {
            if ($fileUrl) {
                return $this->replyFileToDiscussion($thread, $text, $fileUrl, $type);
            }
            return $this->replyToDiscussion($thread, $text);
        }

        // 3️⃣ Sudah di channel tapi belum ada comment → kirim normal
        if ($fileUrl) {
            return $this->sendFileToChannel($thread, $text, $fileUrl, $type);
        }

        return $this->sendToChannel($thread, $text);
    }

    protected function sendNewConversation($room, string $text)
    {
        $response = $this->api('sendMessage', [
            'chat_id' => $this->channelId,
            'text' => $text,
        ]);

        RoomTelegramThread::create([
            'room_id' => $room->id,
            'telegram_chat_id' => $this->channelId,
            'telegram_channel_message_id' => $response['result']['message_id'],
        ]);

        return $response;
    }

    protected function sendToChannel(RoomTelegramThread $thread, string $text)
    {
        return $this->api('sendMessage', [
            'chat_id' => $thread->telegram_chat_id,
            'text' => $text,
        ]);
    }

    protected function replyToDiscussion(RoomTelegramThread $thread, string $text)
    {
        return $this->api('sendMessage', [
            'chat_id' => $thread->telegram_group_id,
            'reply_to_message_id' => $thread->telegram_discussion_message_id,
            'text' => $text,
        ]);
    }

    /** =====================
     * FILE / IMAGE SUPPORT
     * ===================== */

    protected function sendFileNewConversation($room, string $text, string $fileUrl, string $type)
    {
        $response = $this->sendFile($this->channelId, null, $text, $fileUrl, $type);

        RoomTelegramThread::create([
            'room_id' => $room->id,
            'telegram_chat_id' => $this->channelId,
            'telegram_channel_message_id' => $response['result']['message_id'],
        ]);

        return $response;
    }

    protected function sendFileToChannel(RoomTelegramThread $thread, string $text, string $fileUrl, string $type)
    {
        return $this->sendFile($thread->telegram_chat_id, null, $text, $fileUrl, $type);
    }

    protected function replyFileToDiscussion(RoomTelegramThread $thread, string $text, string $fileUrl, string $type)
    {
        return $this->sendFile($thread->telegram_group_id, $thread->telegram_discussion_message_id, $text, $fileUrl, $type);
    }

    protected function sendFile(int|string $chatId, ?int $replyToMessageId, string $text, string $fileUrl, string $type)
    {
        $payload = [
            'chat_id' => $chatId,
            'caption' => $text,
        ];

        if ($replyToMessageId) {
            $payload['reply_to_message_id'] = $replyToMessageId;
        }

        if ($type === 'image') {
            $payload['photo'] = $fileUrl;
            return $this->api('sendPhoto', $payload);
        } else {
            $payload['document'] = $fileUrl;
            return $this->api('sendDocument', $payload);
        }
    }
}
