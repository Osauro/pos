<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreenApiService
{
    private const BASE_URL = 'https://api.green-api.com';

    /**
     * Verifica el estado de la instancia (útil para diagnóstico).
     * Devuelve el stateInstance: 'authorized', 'notAuthorized', 'blocked', etc.
     */
    public function getInstanceState(string $instanceId, string $token): array
    {
        try {
            $url = self::BASE_URL . "/waInstance{$instanceId}/getStateInstance/{$token}";
            $response = Http::withoutVerifying()->timeout(10)->get($url);

            Log::info('GreenAPI getInstanceState', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->json() ?? ['error' => 'empty response'];
        } catch (\Throwable $e) {
            Log::error('GreenAPI getInstanceState: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Envía un mensaje de texto por WhatsApp.
     */
    public function sendMessage(string $instanceId, string $token, string $phone, string $message): bool
    {
        try {
            $chatId = $this->formatPhone($phone);
            $url    = self::BASE_URL . "/waInstance{$instanceId}/sendMessage/{$token}";

            $payload = [
                'chatId'  => $chatId,
                'message' => $message,
            ];

            Log::info('GreenAPI sendMessage →', [
                'url'    => $url,
                'chatId' => $chatId,
            ]);

            $response = Http::withoutVerifying()->timeout(15)->post($url, $payload);

            Log::info('GreenAPI sendMessage ←', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful() && !isset($response->json()['error']);
        } catch (\Throwable $e) {
            Log::error('GreenAPI sendMessage: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía una imagen en base64 (data URL o raw base64) por WhatsApp.
     */
    public function sendImageBase64(
        string $instanceId,
        string $token,
        string $phone,
        string $base64,
        string $caption = ''
    ): bool {
        try {
            $chatId  = $this->formatPhone($phone);
            $raw     = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $content = base64_decode($raw);
            $url     = self::BASE_URL . "/waInstance{$instanceId}/sendFileByUpload/{$token}";

            Log::info('GreenAPI sendFileByUpload →', [
                'url'    => $url,
                'chatId' => $chatId,
                'bytes'  => strlen($content),
            ]);

            $response = Http::withoutVerifying()->timeout(30)
                ->attach('file', $content, 'comprobante.jpg', ['Content-Type' => 'image/jpeg'])
                ->post($url, [
                    'chatId'   => $chatId,
                    'fileName' => 'comprobante.jpg',
                    'caption'  => $caption,
                ]);

            Log::info('GreenAPI sendFileByUpload ←', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful() && !isset($response->json()['error']);
        } catch (\Throwable $e) {
            Log::error('GreenAPI sendImageBase64: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatea un número telefónico al formato chatId de Green API.
     * Entrada: 73010688      → Salida: 59173010688@c.us  (Bolivia, 8 dígitos)
     * Entrada: 59173010688   → Salida: 59173010688@c.us  (ya tiene código)
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Números bolivianos de 8 dígitos (móviles: 6xxxxxxx / 7xxxxxxx)
        if (strlen($phone) === 8 && in_array($phone[0], ['6', '7'])) {
            $phone = '591' . $phone;
        }

        return "{$phone}@c.us";
    }
}
