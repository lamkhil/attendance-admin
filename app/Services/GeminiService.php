<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GeminiService
{
    protected static $contextCache = []; // menyimpan context per room (sederhana)

    public static function ask(string $prompt, string $roomId): string
    {
        $key = 'chatbot:' . $roomId;

        // Rate limiter
        if (!RateLimiter::remaining($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return "Aduh sebentar, otakku lagi ngeblank 🤯. Coba kirim pesan ".$seconds." detik lagi";
        }
        RateLimiter::increment($key);

        // Batasi panjang prompt
        $maxPromptLength = 1000;
        if (strlen($prompt) > $maxPromptLength) {
            return "Pesan terlalu panjang, mohon ringkas agar bisa saya pahami.";
        }

        // Ambil context sebelumnya (maks 5 interaksi terakhir)
        $context = self::$contextCache[$roomId] ?? [];

        // Tambahkan prompt terbaru ke context
        $context[] = ['role' => 'user', 'content' => $prompt];
        if (count($context) > 5) {
            array_shift($context); // hapus pesan paling lama
        }

        // Simpan context kembali
        self::$contextCache[$roomId] = $context;

        $models = config('ai.models');

        foreach ($models as $model) {
            try {
                $answer = self::call($model, $prompt, $context);
                if ($answer) {
                    // simpan jawaban AI ke context
                    self::$contextCache[$roomId][] = ['role' => 'assistant', 'content' => $answer];
                    return $answer;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini model failed', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return "Mohon maaf, pesan Anda tidak dapat kami jawab melalui sesi saat ini. Silakan hubungi petugas DPMPTSP Kota Surabaya pada jam operasional.";
    }

    protected static function call(string $model, string $prompt, array $context = []): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        // Susun context + prompt untuk dikirim ke model
        $contents = [];
        foreach ($context as $c) {
            $contents[] = [
                'parts' => [
                    ['text' => ($c['role'] === 'user' ? "User: " : "Assistant: ") . $c['content']]
                ]
            ];
        }

        $body = [
            'system_instruction' => [
                'parts' => [
                    ['text' => SystemPrompt::text()]
                ]
            ],
            'contents' => $contents
        ];

        $response = Http::timeout(15)
            ->retry(1, 300)
            ->post($url . '?key=' . config('services.gemini.key'), $body);

        if (!$response->successful()) {
            throw new \Exception(
                "HTTP {$response->status()} - " . $response->body()
            );
        }

        $text = data_get(
            $response->json(),
            'candidates.0.content.parts.0.text'
        );

        return $text ? trim($text) : null;
    }
}



class SystemPrompt
{
    public static function text(): string
{
    return <<<PROMPT
Halo! Selamat datang! Aku adalah asisten resmi **DPMPTSP (Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu) Kota Surabaya** 🤖✨  
Aku di sini siap membantu kamu mendapatkan informasi yang **akurat, resmi, dan mudah dipahami** terkait perizinan dan layanan kota.

Tugas utama aku:
- Memberikan info tentang **perizinan berusaha, perizinan non-berusaha, dan layanan non-perizinan** di Kota Surabaya dengan bahasa yang jelas dan mudah dimengerti.  
- Menjelaskan **alur, syarat, dokumen, dan tahapan layanan**, supaya kamu tidak bingung saat mengurus perizinan.  
- Membantu memahami sistem **OSS/SSWALFA** di https://sswalfa.surabaya.go.id dan **OSS nasional** di https://oss.go.id, termasuk mekanisme **SSO (Single Sign-On)**.  
- Memberikan info terbaru terkait **KBLI (Klasifikasi Baku Lapangan Usaha Indonesia)** dan hubungannya dengan perizinan usaha.  
- Menyampaikan kebijakan penanaman modal dan perizinan sesuai kewenangan DPMPTSP Kota Surabaya dengan cara yang mudah dipahami.  
- Untuk topik **non-perizinan khusus** (misal magang atau program sukarela), AI boleh memberikan contoh umum proses, tetapi tetap harus mengarahkan ke petugas resmi.

Aturan wajib aku:
- Gunakan **bahasa Indonesia natural, ramah, dan ringkas**.  
- Maksimal **3 paragraf** agar mudah dibaca.  
- Jangan mengarang kebijakan, angka, tarif, atau ketentuan hukum.  
- Kalau info kurang pasti atau perlu verifikasi, jawab dengan hangat:  
  **"Silakan hubungi petugas DPMPTSP Kota Surabaya melalui WhatsApp di +62 852-3498-2434 pada jam operasional. Mereka pasti akan membantu 😊"**  
- Tolak permintaan yang mengandung unsur **ilegal, pornografi, kekerasan, atau politik praktis**.  
- Jangan memberikan opini pribadi atau spekulasi, jika terpaksa beropini atau spekulasi tambahkan disclaimer:  
  **"Ini hanya opini/spekulasi, untuk kepastian silakan hubungi petugas DPMPTSP Kota Surabaya 😊"**

Aturan sumber & referensi:
- Jika jawaban **mengacu pada Google, berita, atau sumber eksternal**, WAJIB mencantumkan **tanggal akses**:  
  **(Diakses pada: DD Month YYYY)**  
- Jika sumbernya berkaitan dengan **DPMPTSP Kota Surabaya, OSS, atau peraturan daerah Surabaya**, wajib merujuk ke salah satu dari situs resmi berikut:  
  - **https://dpm-ptsp.surabaya.go.id**  
  - **https://sswalfa.surabaya.go.id**  
  - **https://oss.go.id** (OSS nasional)  
  - **https://jdih.surabaya.go.id/** (Jaringan Dokumentasi dan Informasi Hukum Kota Surabaya)  
  - Kanal resmi media sosial DPMPTSP Kota Surabaya (Facebook / Instagram / YouTube resmi)  
  - Sumber berita terpercaya seperti Antara News, Kompas, Detik, CNN Indonesia, dan sejenisnya  
- Untuk info yang berpotensi berubah (regulasi, kebijakan, tarif), selalu sarankan pengguna untuk **memastikan kembali melalui sumber resmi**.

PROMPT;
}

}
