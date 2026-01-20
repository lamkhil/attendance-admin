<?php

namespace App\Http\Controllers\TakonSobat;

use App\Http\Controllers\Controller;
use App\Models\RoomTelegramThread;
use App\Services\QontakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();

        Log::info('Telegram Webhook Update Received: ' . json_encode($update));

        if (!isset($update['message'])) {
            return response()->json(['ok' => true]);
        }

        $message = $update['message'];

        if (isset($message['from'])) {
            if ($message['from']['is_bot'] == false && $message['from']['first_name'] != 'Telegram') {
                // Update to qontak
                $roomThread = RoomTelegramThread::where('telegram_discussion_message_id', $message['reply_to_message']['message_id'])->first();

                if (!$roomThread) {
                    Log::warning('No RoomTelegramThread found for message_id: ' . $message['reply_to_message']['message_id']);
                    return response()->json(['ok' => true]);
                }

                $roomId = $roomThread->room_id;

                $this->sendReplyToQontak($roomId, $message);
            }
        }

        $this->captureDiscussionRoot($message);

        return response()->json(['ok' => true]);
    }

    /**
     * Tangkap comment pertama (Leave a comment)
     */
    protected function captureDiscussionRoot(array $message): void
    {
        // Pastikan ini reply ke message yang diforward dari channel
        if (
            isset($message['chat']['id']) &&
            isset($message['forward_origin'])
        ) {
            RoomTelegramThread::where(
                'telegram_channel_message_id',
                $message['forward_origin']['message_id'] // pakai ID asli di channel
            )->update([
                'telegram_group_id' => $message['chat']['id'],           // discussion group ID
                'telegram_discussion_message_id' => $message['message_id'], // ID pesan reply di discussion
            ]);

            Log::info("Captured discussion root for chat_id {$message['chat']['id']} and message_id {$message['message_id']}");
        }
    }

    protected function sendReplyToQontak(string $roomId, array $message): void
    {
        $qontak = new QontakService();

        $text = $message['text'] ?? '';

        // Jika ada file / media, bisa ditambahkan nanti
        $fileUrl = null;

        if (isset($message['photo'])) {
            // Telegram photo biasanya array, ambil versi terbesar
            $fileUrl = end($message['photo'])['file_id'] ?? null;
        } elseif (isset($message['document'])) {
            $fileUrl = $message['document']['file_id'] ?? null;
        }

        try {
            $result = $qontak->sendWhatsAppMessage(
                roomId: $roomId,
                text: $text,
                filePath: $fileUrl,
                localId: $message['message_id'],
                createdAt: now()->toIso8601String()
            );
            Log::info("Sent reply from Telegram to Qontak for room_id {$roomId} with result: " . json_encode($result));
        } catch (\Throwable $e) {
            Log::error("Failed to send reply from Telegram to Qontak: {$e->getMessage()}", $message);
        }
    }
}
