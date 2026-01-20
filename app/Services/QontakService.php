<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class QontakService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = 'https://service-chat.qontak.com/api/open/v1';
        $this->token   = config('services.qontak.token'); // simpan token di config/services.php
    }

    /**
     * Kirim pesan WhatsApp (text atau file)
     *
     * @param string $roomId
     * @param string|null $text
     * @param string|null $filePath  // path lokal ke file
     * @param string|null $localId
     * @param string|null $createdAt
     * @return array
     */
    public function sendWhatsAppMessage(
        string $roomId,
        ?string $text = null,
        ?string $filePath = null,
        ?string $localId = null,
        ?string $createdAt = null
    ): array {
        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ]);

        // kalau ada file, attach
        if ($filePath && file_exists($filePath)) {
            $request->attach('file', fopen($filePath, 'r'), basename($filePath));
        }

        // Data form-data
        $data = [
            'room_id'    => $roomId,
            'type'       => $filePath ? 'file' : 'text',
            'text'       => $text,
            'local_id'   => $localId,
            'created_at' => $createdAt ?? now()->toIso8601String(),
        ];

        // Hapus nilai null supaya Qontak API tidak menolak
        $data = array_filter($data, fn($v) => !is_null($v));

        $response = $request->asForm()
            ->post("{$this->baseUrl}/messages/whatsapp", $data);

        return $response->json();
    }
}
