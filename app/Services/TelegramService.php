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
     * Kirim pesan dari Qontak (text / file)
     */
    public function sendFromQontak($message)
    {
        $text = $this->buildText($message);

        $thread = RoomTelegramThread::where('room_id', $message->room->id)->first();

        // 1️⃣ Belum pernah kirim ke Telegram
        if (!$thread) {
            $text = $this->buildText($message, true);
            if ($message->file_url) {
                return $this->sendFileNewConversation($message->room, $text, $message->file_url, $message->type);
            }
            return $this->sendNewConversation($message->room, $text);
        }

        // 2️⃣ Sudah ada thread diskusi → reply
        if ($thread->telegram_discussion_message_id) {
            if ($message->file_url) {
                return $this->replyFileToDiscussion($thread, $text, $message->file_url, $message->type);
            }
            return $this->replyToDiscussion($thread, $text);
        }

        // 3️⃣ Sudah di channel tapi belum ada comment → kirim normal
        if ($message->file_url) {
            return $this->sendFileToChannel($thread, $text, $message->file_url, $message->type);
        }

        return $this->sendToChannel($thread, $text);
    }

    /**
     * Build caption / text dari pesan Qontak
     *
     * @param object $message
     * @param bool $isNewConversation
     */
    protected function buildText($message, bool $isNewConversation = false)
    {
        // ========================
        // Bagian info pengirim
        // ========================
        $info = "👤 {$message->sender->name}";

        if ($isNewConversation) {
            $info .= "\n📞 {$message->room->account_uniq_id}";

            // Tambahkan unread_count
            if (isset($message->room->unread_count)) {
                $info .= "\n📩 Unread: {$message->room->unread_count}";
            }
        }

        // ========================
        // Bagian isi pesan
        // ========================
        $body = "";
        if (!empty($message->text)) {
            $body .= "💬 {$message->text}";
        }

        // ========================
        // Bagian tags dan status → hanya untuk new conversation
        // ========================
        $tagsText = "";
        $statusText = "";
        if ($isNewConversation) {

            // Tags
            if (!empty($message->room->tags) && count($message->room->tags) > 0) {
                $hashtags = array_map(function ($tag) {
                    $clean = preg_replace('/[^a-zA-Z0-9 ]/', '', $tag);
                    $clean = str_replace(' ', '_', $clean);
                    return "#{$clean}";
                }, $message->room->tags);
                $tagsText = "🏷️ " . implode(' ', $hashtags);
            }

            // Status
            if (!empty($message->room->status)) {
                $statusEmoji = match (strtolower($message->room->status)) {
                    'resolved'   => '✅',
                    'unassigned' => '🟢',
                    'blocked'    => '⛔',
                    default      => 'ℹ️',
                };
                $statusText = "—————\n$statusEmoji Status: " . ucfirst($message->room->status);
            }
        }

        // ========================
        // Gabungkan semua bagian
        // ========================
        $textParts = [$info];

        if ($body !== "") {
            $textParts[] = "—————\n" . $body;
        }
        if ($tagsText !== "") {
            $textParts[] = "—————\n" . $tagsText;
        }
        if ($statusText !== "") {
            $textParts[] = $statusText;
        }

        return implode("\n", $textParts);
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

    public function editConversation($message)
    {
        $text = $this->buildText($message, true);

        $thread = RoomTelegramThread::where('room_id', $message->room->id)->first();

        $this->editMessage(
            chatId: $thread->telegram_chat_id,
            messageId: $thread->telegram_channel_message_id,
            newText: $text
        );
    }

    /**
     * Edit pesan yang sudah dikirim
     */
    public function editMessage(int|string $chatId, int $messageId, string $newText)
    {
        return $this->api('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $newText,
        ]);
    }
}
