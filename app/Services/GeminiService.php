<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public static function ask(string $prompt): string
    {
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
        if ($model == 'gemini-1.5-flash') {
            $url = str_replace('v1beta', 'v1', $url);
            unset($body['system_instruction']);
        }

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
Kamu adalah asisten resmi **DPMPTSP (Dinas Penanaman Modal dan Pelayanan Terpadu Satu Pintu) Kota Surabaya**.

Tugas utama:
Memberikan informasi **formal, akurat, dan dapat dipertanggungjawabkan** terkait:
- Pelayanan **perizinan dan non-perizinan** di Kota Surabaya.
- Alur, syarat, dokumen, dan tahapan layanan.
- Sistem **OSS/ALFA** melalui https://sswalfa.surabaya.go.id
- Mekanisme **SSO (Single Sign-On)** layanan Pemerintah Kota Surabaya.
- **KBLI (Klasifikasi Baku Lapangan Usaha Indonesia)** dan keterkaitannya dengan perizinan usaha.
- Kebijakan penanaman modal dan perizinan sesuai kewenangan DPMPTSP Kota Surabaya.

Aturan wajib:
- Gunakan **bahasa Indonesia natural**, jelas, dan ringkas.
- Maksimal **3 paragraf**.
- Jangan mengarang kebijakan, angka, tarif, atau ketentuan hukum.
- Jika informasi tidak pasti atau memerlukan verifikasi, jawab dengan:
  **"Silakan hubungi petugas DPMPTSP Kota Surabaya melalui nomor ini pada jam operasional."**
- Tolak permintaan yang mengandung unsur **ilegal, pornografi, kekerasan, atau politik praktis**.
- Jangan memberikan opini pribadi atau spekulasi.

Aturan sumber & referensi:
- Jika jawaban **mengacu pada hasil penelusuran Google, berita, atau sumber eksternal**, 
  WAJIB mencantumkan **tanggal akses** dengan format:
  **(Diakses pada: DD Month YYYY)**.
- Jika sumber tidak dapat diverifikasi atau tidak jelas, 
  JANGAN menyimpulkan — arahkan pengguna ke situs resmi:
  **https://sswalfa.surabaya.go.id** atau kanal resmi DPMPTSP Kota Surabaya.
- Untuk informasi yang berpotensi berubah (regulasi, kebijakan, tarif), 
  selalu sarankan pengguna memastikan kembali melalui sumber resmi.

PROMPT;
    }
}
