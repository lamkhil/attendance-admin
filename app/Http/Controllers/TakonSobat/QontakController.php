<?php

namespace App\Http\Controllers\TakonSobat;

use App\Events\QontakMessageReceived;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Models\Message;
use App\Models\Room;
use App\Models\Sender;
use App\Models\Avatar;

class QontakController extends Controller
{
    /**
     * Webhook callback from Qontak
     */
    public function callback(Request $request)
    {
        $payload = $request->all();

        Log::info('Qontak Webhook Received : ' . json_encode($payload));

        // 🔐 minimal guard
        if (!isset($payload['id'], $payload['room']['id'])) {
            return response()->json([
                'status' => 'ignored',
                'reason' => 'invalid payload'
            ], 400);
        }

        try {
            DB::transaction(function () use ($payload) {

                /** =====================
                 *  AVATAR
                 *  ===================== */
                $avatarId = null;

                if (!empty($payload['sender']['avatar']['url'])) {
                    $avatarId = sha1($payload['sender']['avatar']['url']);

                    Avatar::updateOrCreate(
                        ['id' => $avatarId],
                        [
                            'url'        => $payload['sender']['avatar']['url'] ?? null,
                            'url_large'  => $payload['sender']['avatar']['large']['url'] ?? null,
                            'url_medium' => $payload['sender']['avatar']['medium']['url'] ?? null,
                            'url_small'  => $payload['sender']['avatar']['small']['url'] ?? null,
                            'filename'   => $payload['sender']['avatar']['filename'] ?? null,
                            'size'       => $payload['sender']['avatar']['size'] ?? null,
                        ]
                    );
                }

                /** =====================
                 *  SENDER
                 *  ===================== */
                if (!empty($payload['sender_id'])) {
                    Sender::updateOrCreate(
                        ['id' => $payload['sender_id']],
                        [
                            'type'      => $payload['sender_type'] ?? null,
                            'name'      => $payload['sender']['name'] ?? '-',
                            'avatar_id' => $avatarId,
                        ]
                    );
                }

                /** =====================
                 *  ROOM
                 *  ===================== */
                Room::updateOrCreate(
                    ['id' => $payload['room']['id']],
                    [
                        'name'                   => $payload['room']['name'] ?? null,
                        'description'            => $payload['room']['description'] ?? null,
                        'status'                 => $payload['room']['status'] ?? null,
                        'type'                   => $payload['room']['type'] ?? null,
                        'channel'                => $payload['room']['channel'] ?? null,
                        'channel_account'        => $payload['room']['channel_account'] ?? null,
                        'organization_id'        => $payload['room']['organization_id'] ?? null,
                        'account_uniq_id'        => $payload['room']['account_uniq_id'] ?? null,
                        'channel_integration_id' => $payload['room']['channel_integration_id'] ?? null,
                        'session_at'             => $payload['room']['session_at'] ?? null,
                        'unread_count'           => $payload['room']['unread_count'] ?? 0,
                        'avatar'                 => $payload['room']['avatar']['url'] ?? null,
                        'tags'                   => $payload['room']['tags'] ?? [], // ✅ AMAN
                        'resolved_at'            => $payload['room']['resolved_at'] ?? null,
                        'resolved_by_id'         => $payload['room']['resolved_by_id'] ?? null,
                        'resolved_by_type'       => $payload['room']['resolved_by_type'] ?? null,
                        'external_id'            => $payload['room']['external_id'] ?? null,
                        'created_at'             => $payload['room']['created_at'] ?? now(),
                        'updated_at'             => $payload['room']['updated_at'] ?? now(),
                    ]
                );


                /** =====================
                 *  MESSAGE
                 *  ===================== */
                Message::updateOrCreate(
                    ['id' => $payload['id']],
                    [
                        'type'             => $payload['type'] ?? null,
                        'is_campaign'      => $payload['is_campaign'] ?? false,
                        'room_id'          => $payload['room']['id'],
                        'sender_id'        => $payload['sender_id'] ?? null,
                        'participant_id'   => $payload['participant_id'] ?? null,
                        'participant_type' => $payload['participant_type'] ?? null,
                        'organization_id'  => $payload['organization_id'] ?? null,
                        'text'             => $payload['text'] ?? null,
                        'status'           => $payload['status'] ?? null,
                        'external_id'      => $payload['external_id'] ?? null,
                        'local_id'         => $payload['local_id'] ?? null,
                        'reply'            => isset($payload['reply']) ? $payload['reply']['id'] : null,
                        'created_at'       => $payload['created_at'] ?? now(),
                        'file_uniq_id'   => $payload['file_uniq_id'] ?? null,
                        'file_url'       => $payload['file']['url'] ?? null,
                        'file_name'      => $payload['file']['filename'] ?? null,
                        'file_size'      => $payload['file']['size'] ?? null,
                        'file_large_url' => $payload['file']['large']['url'] ?? null,
                        'file_medium_url' => $payload['file']['medium']['url'] ?? null,
                        'file_small_url' => $payload['file']['small']['url'] ?? null,

                    ]
                );
            });
            $message = Message::with(['room', 'sender'])->find($payload['id']);

            try {
                if ($payload['sender']['name'] != 'DPMPTSP Surabaya') {
                    event(new QontakMessageReceived($message));
                }
            } catch (\Throwable $th) {
                Log::error('Error firing QontakMessageReceived event: ' . $th->getMessage());
            }

            return response()->json([
                'status' => 'ok'
            ]);
        } catch (\Throwable $e) {
            Log::error('Qontak webhook error', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'status' => 'error'
            ], 500);
        }
    }
}
