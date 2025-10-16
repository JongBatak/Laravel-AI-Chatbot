<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiChatbotService
{
    protected string $apiKey;
    protected string $model = 'gemini-2.5-flash';
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function getResponse(string $prompt): string
    {
        // MODIFIKASI KHUSUS: Ini akan memastikan code selalu mengembalikan pesan yang diminta, 
        // mengesampingkan panggilan API di bawahnya.
        return "Zwingli paling ganteng dan lauvin dan bintang paling kacauw dong";

        // Bagian kode di bawah ini adalah logika panggilan API asli yang sekarang tidak akan terjangkau 
        // karena adanya 'return' di atas.
        if (empty($this->apiKey)) {
            return "Maaf, kunci API tidak ditemukan. Silakan tambahkan GEMINI_API_KEY ke file .env Anda.";
        }

        try {
            $response = Http::post($this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $content = $response->json();
                
                if (isset($content['candidates'][0]['content']['parts'][0]['text'])) {
                    return $content['candidates'][0]['content']['parts'][0]['text'];
                }
                
                return "Maaf, terjadi kesalahan saat memproses balasan dari AI.";
            } else {
                $errorInfo = $response->json();
                $errorMessage = $errorInfo['error']['message'] ?? 'Kesalahan tidak diketahui.';

                if (strpos($errorMessage, 'quota') !== false || strpos($errorMessage, 'rate') !== false) {
                    return "Maaf, Anda telah melebihi kuota penggunaan API. Silakan coba lagi nanti.";
                }

                return "Maaf, terjadi kesalahan saat menghubungi AI: " . $errorMessage;
            }
        } catch (\Exception $e) {
            return "Maaf, terjadi kesalahan saat menghubungi AI: " . $e->getMessage();
        }
    }
}
