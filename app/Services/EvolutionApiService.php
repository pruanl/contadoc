<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class EvolutionApiService
{
    public function status(?string $instance = null): array
    {
        return $this->request()
            ->get($this->endpoint('/instance/connectionState/'.$this->instance($instance)))
            ->json() ?? [];
    }

    public function qrCode(?string $instance = null): array
    {
        return $this->request()
            ->get($this->endpoint('/instance/connect/'.$this->instance($instance)))
            ->json() ?? [];
    }

    public function findWebhook(?string $instance = null): array
    {
        return $this->request()
            ->get($this->endpoint('/webhook/find/'.$this->instance($instance)))
            ->json() ?? [];
    }

    public function setWebhook(string $url, ?string $instance = null): array
    {
        return $this->request()
            ->post($this->endpoint('/webhook/set/'.$this->instance($instance)), [
                'webhook' => [
                    'enabled' => true,
                    'url' => $url,
                    'webhookByEvents' => false,
                    'webhookBase64' => true,
                    'events' => [
                        'MESSAGES_UPSERT',
                    ],
                ],
            ])
            ->throw()
            ->json() ?? [];
    }

    public function sendText(string $number, string $text, ?string $instance = null): array
    {
        return $this->request()
            ->post($this->endpoint('/message/sendText/'.$this->instance($instance)), [
                'number' => $number,
                'text' => $text,
                'linkPreview' => true,
            ])
            ->throw()
            ->json() ?? [];
    }

    public function download(string $url): string
    {
        $response = $this->request()->get($url);
        $response->throw();

        return $response->body();
    }

    public function mediaBase64(array $messageData, ?string $instance = null): ?array
    {
        $messageId = data_get($messageData, 'key.id');
        $message = $messageId
            ? ['key' => ['id' => $messageId]]
            : [
                'key' => $messageData['key'] ?? null,
                'message' => $messageData['message'] ?? null,
                'messageType' => $messageData['messageType'] ?? null,
            ];

        $response = $this->request()
            ->post($this->endpoint('/chat/getBase64FromMediaMessage/'.$this->instance($instance)), [
                'message' => array_filter($message),
                'convertToMp4' => false,
            ])
            ->throw()
            ->json() ?? [];

        $base64 = data_get($response, 'base64')
            ?? data_get($response, 'data.base64')
            ?? data_get($response, 'data');

        return is_string($base64)
            ? [
                'base64' => $base64,
                'fileName' => data_get($response, 'fileName') ?? data_get($response, 'data.fileName'),
                'mimetype' => data_get($response, 'mimetype') ?? data_get($response, 'data.mimetype'),
                'mediaType' => data_get($response, 'mediaType') ?? data_get($response, 'data.mediaType'),
                'size' => data_get($response, 'size.fileLength.low') ?? data_get($response, 'data.size.fileLength.low'),
            ]
            : null;
    }

    private function request(): PendingRequest
    {
        $request = Http::timeout(20);

        if ($key = config('services.evolution.key')) {
            $request = $request
                ->withHeaders(['apikey' => $key])
                ->withToken($key);
        }

        return $request;
    }

    private function endpoint(string $path): string
    {
        return rtrim((string) config('services.evolution.url'), '/').'/'.ltrim($path, '/');
    }

    private function instance(?string $instance): string
    {
        return $instance ?: (string) config('services.evolution.instance');
    }
}
