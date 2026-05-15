<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\AuthorizedSender;
use App\Models\Document;
use App\Models\WebhookEvent;
use App\Models\WhatsappMessage;
use App\Services\EvolutionApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessEvolutionWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public WebhookEvent $webhookEvent)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(EvolutionApiService $evolution): void
    {
        try {
            $payload = $this->webhookEvent->payload;
            $messagePayload = $this->messagePayload($payload);
            $remoteJid = $this->remoteJid($payload, $messagePayload);
            $phone = Client::normalizePhone(str($remoteJid)->before('@')->toString() ?: $this->remotePhone($payload, $messagePayload));
            $phoneVariants = Client::phoneVariants($phone);
            $body = $this->text($messagePayload);

            $this->debugLog('info', 'Webhook job started.', [
                'webhook_event_id' => $this->webhookEvent->id,
                'event_type' => $this->webhookEvent->event_type,
                'instance' => Arr::get($payload, 'instance'),
                'message_id' => Arr::get($payload, 'data.key.id'),
                'remote_jid' => $remoteJid,
                'normalized_phone' => $phone,
                'phone_variants' => $phoneVariants,
                'message_type' => Arr::get($payload, 'data.messageType'),
            ]);

            if ($phone === '') {
                $this->webhookEvent->update([
                    'status' => 'ignored',
                    'error' => 'Remote phone not found in payload.',
                    'processed_at' => now(),
                ]);

                $this->debugLog('warning', 'Webhook ignored: remote phone not found.', [
                    'webhook_event_id' => $this->webhookEvent->id,
                    'message_id' => Arr::get($payload, 'data.key.id'),
                ]);

                return;
            }

            $sender = AuthorizedSender::query()
                ->whereIn('normalized_phone', $phoneVariants)
                ->where('is_active', true)
                ->first();

            if (! $sender) {
                $this->webhookEvent->update([
                    'status' => 'ignored',
                    'error' => 'Sender phone is not authorized: '.$phone,
                    'processed_at' => now(),
                ]);

                $this->debugLog('warning', 'Webhook ignored: sender not authorized.', [
                    'webhook_event_id' => $this->webhookEvent->id,
                    'normalized_phone' => $phone,
                    'phone_variants' => $phoneVariants,
                ]);

                return;
            }

            [$client, $clientHint, $matchConfidence] = $this->resolveClient($body, $sender->user_id);

            $this->debugLog('info', 'Webhook sender resolved.', [
                'webhook_event_id' => $this->webhookEvent->id,
                'authorized_sender_id' => $sender->id,
                'user_id' => $sender->user_id,
                'client_id' => $client?->id,
                'client_hint' => $clientHint,
                'match_confidence' => $matchConfidence,
            ]);

            $message = WhatsappMessage::create([
                'client_id' => $client?->id,
                'user_id' => $sender->user_id,
                'remote_phone' => $phone,
                'remote_jid' => $remoteJid,
                'direction' => $this->fromMe($payload, $messagePayload) ? 'outgoing' : 'incoming',
                'body' => $body,
                'payload' => $payload,
                'message_at' => $this->messageAt($payload, $messagePayload),
            ]);

            if ($media = $this->media($payload, $messagePayload)) {
                $this->storeDocument($client, $message, $media, $evolution, $sender, $clientHint, $matchConfidence, $payload);
            } else {
                $this->debugLog('info', 'Webhook message has no media.', [
                    'webhook_event_id' => $this->webhookEvent->id,
                    'whatsapp_message_id' => $message->id,
                ]);
            }

            $this->webhookEvent->update([
                'status' => 'processed',
                'error' => null,
                'processed_at' => now(),
            ]);

            $this->debugLog('info', 'Webhook job processed.', [
                'webhook_event_id' => $this->webhookEvent->id,
                'whatsapp_message_id' => $message->id,
            ]);
        } catch (Throwable $exception) {
            $this->webhookEvent->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
                'processed_at' => now(),
            ]);

            $this->debugLog('error', 'Webhook job failed.', [
                'webhook_event_id' => $this->webhookEvent->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function messagePayload(array $payload): array
    {
        $data = Arr::get($payload, 'data', $payload);

        return Arr::get($data, 'message')
            ?? Arr::get($data, 'messages.0.message')
            ?? Arr::get($payload, 'message')
            ?? $data;
    }

    private function remoteJid(array $payload, array $messagePayload): ?string
    {
        return Arr::get($payload, 'data.key.remoteJid')
            ?? Arr::get($payload, 'key.remoteJid')
            ?? Arr::get($messagePayload, 'key.remoteJid')
            ?? Arr::get($payload, 'data.remoteJid')
            ?? Arr::get($payload, 'data.from')
            ?? Arr::get($payload, 'from');
    }

    private function remotePhone(array $payload, array $messagePayload): ?string
    {
        return Arr::get($payload, 'data.phone')
            ?? Arr::get($payload, 'phone')
            ?? Arr::get($messagePayload, 'phone');
    }

    private function fromMe(array $payload, array $messagePayload): bool
    {
        return (bool) (Arr::get($payload, 'data.key.fromMe') ?? Arr::get($messagePayload, 'key.fromMe') ?? false);
    }

    private function text(array $messagePayload): ?string
    {
        return Arr::get($messagePayload, 'conversation')
            ?? Arr::get($messagePayload, 'extendedTextMessage.text')
            ?? Arr::get($messagePayload, 'documentMessage.caption')
            ?? Arr::get($messagePayload, 'imageMessage.caption')
            ?? Arr::get($messagePayload, 'videoMessage.caption')
            ?? Arr::get($messagePayload, 'text')
            ?? Arr::get($messagePayload, 'caption');
    }

    private function resolveClient(?string $body, int $userId): array
    {
        $hint = $this->clientHint($body);

        if (! $hint) {
            return [null, null, null];
        }

        $client = Client::where('user_id', $userId)
            ->whereRaw('lower(name) = ?', [mb_strtolower($hint)])
            ->first();

        if ($client) {
            return [$client, $hint, 100];
        }

        $client = Client::where('user_id', $userId)
            ->whereRaw('lower(name) like ?', ['%'.mb_strtolower($hint).'%'])
            ->first();

        if ($client) {
            return [$client, $hint, 80];
        }

        return [null, $hint, 0];
    }

    private function clientHint(?string $body): ?string
    {
        if (! $body) {
            return null;
        }

        $patterns = [
            '/(?:^|\n)\s*(?:cliente|do cliente|da cliente|guardar no cliente|salvar no cliente)\s*[:\-]?\s*(.+)$/iu',
            '/(?:^|\n)\s*(?:para|p\/)\s*[:\-]?\s*(.+)$/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                return trim(str($matches[1])->before("\n")->before(',')->before(';')->toString());
            }
        }

        return null;
    }

    private function messageAt(array $payload, array $messagePayload): mixed
    {
        $timestamp = Arr::get($payload, 'data.messageTimestamp') ?? Arr::get($messagePayload, 'messageTimestamp');

        return is_numeric($timestamp) ? now()->setTimestamp((int) $timestamp) : now();
    }

    private function media(array $payload, array $messagePayload): ?array
    {
        foreach (['documentMessage', 'imageMessage', 'videoMessage', 'audioMessage', 'stickerMessage'] as $key) {
            if ($media = Arr::get($messagePayload, $key)) {
                return $media + ['media_type' => $key];
            }
        }

        $media = Arr::get($payload, 'data.media') ?? Arr::get($payload, 'media');

        return is_array($media) ? $media : null;
    }

    private function storeDocument(?Client $client, WhatsappMessage $message, array $media, EvolutionApiService $evolution, AuthorizedSender $sender, ?string $clientHint, ?float $matchConfidence, array $payload): void
    {
        $mediaResult = null;
        $mime = $media['mimetype'] ?? $media['mimeType'] ?? null;
        $binary = null;

        $this->debugLog('info', 'Webhook media detected.', [
            'webhook_event_id' => $this->webhookEvent->id,
            'whatsapp_message_id' => $message->id,
            'media_type' => $media['media_type'] ?? null,
            'mimetype' => $mime,
            'has_inline_base64' => ! empty($media['base64']),
            'has_url' => ! empty($media['url']),
            'has_media_url' => ! empty($media['mediaUrl']),
            'is_encrypted_url' => ! empty($media['url']) && $this->isEncryptedWhatsappMediaUrl((string) $media['url']),
            'evolution_url_configured' => (bool) config('services.evolution.url'),
        ]);

        if (! empty($media['base64'])) {
            $binary = $this->decodeBase64((string) $media['base64']);
        } elseif (! empty($media['url']) && ! $this->isEncryptedWhatsappMediaUrl((string) $media['url'])) {
            $binary = $evolution->download((string) $media['url']);
        } elseif (! empty($media['mediaUrl']) && ! $this->isEncryptedWhatsappMediaUrl((string) $media['mediaUrl'])) {
            $binary = $evolution->download((string) $media['mediaUrl']);
        } elseif (config('services.evolution.url') && ($mediaResult = $this->fetchMediaBase64($evolution, $payload))) {
            $binary = $this->decodeBase64($mediaResult['base64']);
        }

        $mime = $mediaResult['mimetype'] ?? $mime;
        $name = $this->mediaName($media, $mediaResult, $mime);
        $path = null;
        $size = $this->mediaSize($media) ?? $mediaResult['size'] ?? null;

        if ($binary !== null && $binary !== false) {
            $folder = $client?->id ? 'documents/'.$client->id : 'documents/inbox';
            $path = $folder.'/'.uniqid('', true).'-'.$name;
            Storage::disk('local')->put($path, $binary);
            $size = strlen($binary);
        }

        $document = Document::create([
            'client_id' => $client?->id,
            'user_id' => $sender->user_id,
            'whatsapp_message_id' => $message->id,
            'file_path' => $path,
            'original_name' => $name,
            'mime_type' => $mime,
            'size' => $size,
            'status' => $client ? 'new' : 'pending',
            'origin' => 'official_whatsapp',
            'sender_phone' => $sender->normalized_phone,
            'client_hint' => $clientHint,
            'match_confidence' => $matchConfidence,
            'received_at' => $message->message_at ?? now(),
        ]);

        $this->debugLog('info', 'Webhook document stored.', [
            'webhook_event_id' => $this->webhookEvent->id,
            'document_id' => $document->id,
            'client_id' => $document->client_id,
            'user_id' => $document->user_id,
            'file_path' => $document->file_path,
            'file_exists' => $document->file_path ? Storage::disk('local')->exists($document->file_path) : false,
            'original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'size' => $document->size,
            'status' => $document->status,
        ]);
    }

    private function isEncryptedWhatsappMediaUrl(string $url): bool
    {
        return str_contains($url, 'mmg.whatsapp.net') || str_contains(parse_url($url, PHP_URL_PATH) ?? '', '.enc');
    }

    private function fetchMediaBase64(EvolutionApiService $evolution, array $payload): ?array
    {
        try {
            $result = $evolution->mediaBase64((array) Arr::get($payload, 'data', $payload), Arr::get($payload, 'instance'));

            $this->debugLog($result ? 'info' : 'warning', 'Evolution media base64 lookup finished.', [
                'webhook_event_id' => $this->webhookEvent->id,
                'message_id' => Arr::get($payload, 'data.key.id'),
                'has_result' => (bool) $result,
                'file_name' => $result['fileName'] ?? null,
                'mimetype' => $result['mimetype'] ?? null,
                'media_type' => $result['mediaType'] ?? null,
                'size' => $result['size'] ?? null,
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->debugLog('error', 'Evolution media base64 lookup failed.', [
                'webhook_event_id' => $this->webhookEvent->id,
                'message_id' => Arr::get($payload, 'data.key.id'),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function decodeBase64(string $base64): string|false
    {
        if (str_contains($base64, ',')) {
            $base64 = (string) str($base64)->after(',');
        }

        return base64_decode($base64, true);
    }

    private function mediaName(array $media, ?array $mediaResult, ?string $mime): string
    {
        $name = $mediaResult['fileName']
            ?? $media['fileName']
            ?? $media['filename']
            ?? 'whatsapp-file-'.now()->format('YmdHis');

        $name = str($name)->replaceMatches('/[\/\\\\]+/', '-')->trim()->toString();

        if (pathinfo($name, PATHINFO_EXTENSION) !== '') {
            return $name;
        }

        $extension = $this->extensionFor($mime, $mediaResult['mediaType'] ?? $media['media_type'] ?? null);

        return $extension ? $name.'.'.$extension : $name;
    }

    private function extensionFor(?string $mime, ?string $mediaType): ?string
    {
        $extension = $mime ? File::extension($mime) : null;

        if ($extension) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return match ($mediaType) {
            'imageMessage' => 'jpg',
            'documentMessage' => 'pdf',
            'videoMessage' => 'mp4',
            'audioMessage' => 'ogg',
            'stickerMessage' => 'webp',
            default => null,
        };
    }

    private function mediaSize(array $media): ?int
    {
        $length = $media['fileLength'] ?? null;

        if (is_numeric($length)) {
            return (int) $length;
        }

        if (is_array($length) && isset($length['low']) && is_numeric($length['low'])) {
            return (int) $length['low'];
        }

        return null;
    }

    private function debugLog(string $level, string $message, array $context = []): void
    {
        if (! config('services.evolution.webhook_debug')) {
            return;
        }

        Log::log($level, '[contadoc-webhook] '.$message, $context);
    }
}
