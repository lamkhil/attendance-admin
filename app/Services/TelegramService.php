<?php

namespace App\Services;

use App\Jobs\TelegramApiJob;
use App\Models\RoomTelegramThread;
use App\Models\TelegramChannel;
use App\Models\TelegramGroup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected $defaultChannelId;
    protected $defaultGroupId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->defaultChannelId = config('services.telegram.chat_id');
        $this->defaultGroupId = config('services.telegram.group_id');
    }

    protected function api(string $method, array $payload, bool $background = true)
    {
        if ($background) {
            TelegramApiJob::dispatch(
                $method,
                $payload,
                $this->botToken
            );

            return true; // async = no response
        }

        // sync = langsung dapet response
        return $this->callTelegram($method, $payload);
    }


    protected function callTelegram(string $method, array $payload)
    {
        $group = \App\Models\TelegramGroup::where(
            'chat_id',
            $payload['chat_id'] ?? null
        )->first();

        $token = $group?->bot_token ?? $this->botToken;

        return Http::post(
            "https://api.telegram.org/bot{$token}/{$method}",
            $payload
        );
    }


    public function setWebHook(string $url, string $chatId)
    {
        return $this->api('setWebhook', [
            'url' => $url,
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Kirim pesan dari Qontak (text / file)
     */
    public function sendFromQontak($message)
    {
        $text = $this->buildText($message) ?? '';

        $thread = RoomTelegramThread::where('room_id', $message->room->id)->first();

        // 1️⃣ Belum pernah kirim ke Telegram
        if (!$thread) {
            return $this->sendNewConversation($message->room, $text);
        }

        if ($thread->telegram_thread_id == null) {
            $encodedMessage = json_encode($message);
            Log::error("Telegram thread ID is null for message: {$encodedMessage}");
            return;
        }

        if ($message->file_url) {
            return $this->sendFileToTopic(
                chatId: $thread->telegram_chat_id,
                topicId: $thread->telegram_thread_id,
                text: $text,
                fileUrl: $message->file_url,
                type: $message->type
            );
        }
        return $this->sendToTopic(
            chatId: $thread->telegram_chat_id,
            topicId: $thread->telegram_thread_id,
            text: $text
        );
    }

    /**
     * Build caption / text dari pesan Qontak
     *
     * @param object $message
     * @param bool $isNewConversation
     */
    protected function buildText($message)
    {
        if ($message->participant_type == 'agent') {
            // ========================
            // Bagian info pengirim
            // ========================
            $info = "👨‍💼 Agent {$message->sender->name}";

            // ========================
            // Bagian isi pesan
            // ========================
            $body = "";
            if (!empty($message->text)) {
                $body .= "💬 {$message->text}";
            }

            // ========================
            // Gabungkan semua bagian
            // ========================
            $textParts = [$info];

            $textParts[] = "—————\n" . $body;

            return  implode("\n", $textParts);
        }


        return $message->text;
    }




    protected function sendNewConversation($room, string $text)
    {
        if ((string) $text === '0') {

            if (!empty($room->tags)) {

                $lastTag = end($room->tags);

                $qontak = new QontakService();
                $qontak->deleteRoomTags($room->id, $lastTag);
            }

            return;
        }

        $group = TelegramGroup::whereIn('slug', $room->tags)->first();
        if ($group == null) {
            Log::error('Group not found: ' . $text);
            return;
        }
        $groupId = $group?->chat_id;

        $responseCreateTopic = $this->createTopic(
            chatId: $groupId,
            name: "{$room->name} / {$room->account_uniq_id}"
        );
        RoomTelegramThread::create([
            'room_id' => $room->id,
            'telegram_chat_id' => $groupId,
            'telegram_thread_id' => $responseCreateTopic['result']['message_thread_id'],
        ]);

        $messages = $room->messages;

        foreach ($messages as $message) {

            $text = $this->buildText($message);
            if ($message->file_url) {
                $response = $this->sendFileToTopic(
                    chatId: $groupId,
                    topicId: $responseCreateTopic['result']['message_thread_id'],
                    fileUrl: $message->file_url,
                    type: $message->type,
                    text: $text
                );
            } else {
                $response = $this->sendToTopic(
                    chatId: $groupId,
                    topicId: $responseCreateTopic['result']['message_thread_id'],
                    text: $text
                );
            }
        }

        return $response;
    }

    /** =====================
     * FILE / IMAGE SUPPORT
     * ===================== */

    protected function sendFileNewConversation($room, string $text, string $fileUrl, string $type)
    {

        $group = TelegramGroup::whereIn('slug', $room->tags)->first();
        if ($group == null) {
            return;
        }
        $groupId = $group?->chat_id;

        $prefix = '';

        if (!empty($room->tags)) {
            if (in_array('oss', $room->tags)) {
                $prefix .= '(OSS) ';
            }

            if (in_array('ssw', $room->tags)) {
                $prefix .= '(SSW) ';
            }
        }

        $responseCreateTopic = $this->createTopic(
            chatId: $groupId,
           name: trim("{$prefix}{$room->name} / {$room->account_uniq_id}")
        );
        RoomTelegramThread::create([
            'room_id' => $room->id,
            'telegram_chat_id' => $groupId,
            'telegram_thread_id' => $responseCreateTopic['result']['message_thread_id'],
        ]);

        $response = $this->sendFileToTopic(
            chatId: $groupId,
            topicId: $responseCreateTopic['result']['message_thread_id'],
            text: $text,
            fileUrl: $fileUrl,
            type: $type
        );

        return $response;
    }


    // =========================
    // TOPIC DISCUSSION SUPPORT
    // =========================


    protected function sendToTopic(
        int|string $chatId,
        int $topicId,
        string $text
    ) {
        return $this->api('sendMessage', [
            'chat_id' => $chatId,
            'message_thread_id' => $topicId,
            'text' => $text,
        ]);
    }

    protected function createTopic(int|string $chatId, string $name)
    {
        return $this->api('createForumTopic', [
            'chat_id' => $chatId,
            'name' => $name,
        ], false);
    }

    protected function sendFileToTopic(
        int|string $chatId,
        int $topicId,
        $text,
        string $fileUrl,
        string $type
    ) {
        $payload = [
            'chat_id' => $chatId,
            'message_thread_id' => $topicId,
            'caption' => $text ?? '',
        ];

        if ($type === 'image') {
            $payload['photo'] = $fileUrl;
            return $this->api('sendPhoto', $payload);
        }

        $payload['document'] = $fileUrl;
        return $this->api('sendDocument', $payload);
    }

    public function closeTopic(
        int|string $chatId,
        int $topicId
    ) {
        return $this->api('closeForumTopic', [
            'chat_id' => $chatId,
            'message_thread_id' => $topicId,
        ], false);
    }

    public function deleteTopic(
        int|string $chatId,
        int $topicId
    ) {
        return $this->api('deleteForumTopic', [
            'chat_id' => $chatId,
            'message_thread_id' => $topicId,
        ], false);
    }
}
