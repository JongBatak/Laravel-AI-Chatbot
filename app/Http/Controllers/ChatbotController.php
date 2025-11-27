<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    /**
     * Menampilkan halaman chatbot.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('chatbot');
    }

    /**
     * Mengirim pesan ke AI dan mengembalikan respons.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        $apiKey = 'GEMINI_API_KEY FROM ENV'; 

        $request->validate(['prompt' => 'required|string']);

        $prompt = $request->input('prompt');

        try {
            $response = Http::post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey, [
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
                    return response()->json(['reply' => $content['candidates'][0]['content']['parts'][0]['text']]);
                }
                
                return response()->json(['reply' => "Maaf, terjadi kesalahan saat memproses balasan dari AI."]);
            } else {
                $errorInfo = $response->json();
                $errorMessage = $errorInfo['error']['message'] ?? 'Kesalahan tidak diketahui.';
                return response()->json(['reply' => "Maaf, terjadi kesalahan saat menghubungi AI: " . $errorMessage]);
            }
        } catch (\Exception $e) {
            return response()->json(['reply' => "Maaf, terjadi kesalahan saat menghubungi AI: " . $e->getMessage()]);
        }
    }
}
