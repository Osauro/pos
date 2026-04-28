<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreenApiService
{
    private const BASE_URL = 'https://api.green-api.com';

    /**
     * Envía un mensaje de texto por WhatsApp.
     */
    public function sendMessage(string $instanceId, string $token, string $phone, string $message): bool
    {
        try {
            $url = self::BASE_URL . "/waInstance{$instanceId}/sendMessage/{$token}";

            $response = Http::timeout(10)->post($url, [
                'chatId'  => $this->formatPhone($phone),
                'message' => $message,
            ]);

            return $response->successful();
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
            // Eliminar prefijo data:image/...;base64, si viene como dataURL
            $raw = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $content = base64_decode($raw);

            $url = self::BASE_URL . "/waInstance{$instanceId}/sendFileByUpload/{$token}";

            $response = Http::timeout(30)
                ->attach('file', $content, 'comprobante.jpg', ['Content-Type' => 'image/jpeg'])
                ->post($url, [
                    'chatId'   => $this->formatPhone($phone),
                    'fileName' => 'comprobante.jpg',
                    'caption'  => $caption,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('GreenAPI sendImageBase64: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatea un número telefónico al formato chatId de Green API.
     * Entrada: 59171234567 → Salida: 59171234567@c.us
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        return "{$phone}@c.us";
    }
}
