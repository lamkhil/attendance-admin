<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GeminiService
{
    public static function ask(string $prompt, $roomId): string
    {
        $key = 'chatbot:' . $roomId;

        if (!RateLimiter::remaining($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return "Aduh sebentar, otakku lagi ngeblank 🤯. Coba kirim pesan ".$seconds." detik lagi";
        }

        RateLimiter::increment($key);

        $maxPromptLength = 1000; // karakter
        if (strlen($prompt) > $maxPromptLength) {
            return "Pesan terlalu panjang, mohon ringkas agar bisa saya pahami.";
        }


        $models = config('ai.models');

        foreach ($models as $model) {
            try {
                $answer = self::call($model, $prompt);

                if ($answer) {
                    return $answer;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini model failed', [
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return "Mohon maaf, pesan Anda tidak dapat kami jawab melalui sesi saat ini. Untuk memperoleh informasi yang jelas dan akurat, silakan menghubungi petugas DPMPTSP Kota Surabaya pada jam operasional.";
    }

    protected static function call(string $model, string $prompt): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $body = [
            'system_instruction' => [
                'parts' => [
                    ['text' => SystemPrompt::text()]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => trim($prompt)]
                    ]
                ]
            ]
        ];

        $response = Http::timeout(15)
            ->retry(1, 300) // retry 1x (hemat & aman)
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

Aturan wajib aku:
- Gunakan **bahasa Indonesia natural, ramah, dan ringkas**.  
- Maksimal **3 paragraf** agar mudah dibaca.  
- Jangan mengarang kebijakan, angka, tarif, atau ketentuan hukum.  
- Kalau info kurang pasti atau perlu verifikasi, jawab dengan hangat:  
  **"Silakan hubungi petugas DPMPTSP Kota Surabaya melalui nomor ini pada jam operasional. Mereka pasti akan membantu 😊"**  
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
