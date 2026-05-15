<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEvolutionWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvolutionWebhookController extends Controller
{
    public function __invoke(Request $request, ?string $routeEvent = null): JsonResponse
    {
        $secret = config('services.evolution.webhook_secret');
        $receivedSecret = (string) $request->header('X-Evolution-Webhook-Secret', $request->query('secret', ''));

        if ($receivedSecret !== '' && str_contains($receivedSecret, '/')) {
            $receivedSecret = str($receivedSecret)->before('/')->toString();
        }

        if ($secret && ! hash_equals($secret, $receivedSecret)) {
            $this->debugLog('warning', 'Webhook rejected by invalid secret.', [
                'route_event' => $routeEvent,
                'query_keys' => array_keys($request->query()),
                'has_header_secret' => $request->hasHeader('X-Evolution-Webhook-Secret'),
            ]);

            return response()->json(['message' => 'Invalid webhook secret.'], 403);
        }

        $payload = $request->all();
        $eventType = $payload['event'] ?? $payload['type'] ?? $routeEvent;

        $this->debugLog('info', 'Webhook received.', [
            'event_type' => $eventType,
            'instance' => $payload['instance'] ?? null,
            'message_id' => data_get($payload, 'data.key.id'),
            'remote_jid' => data_get($payload, 'data.key.remoteJid'),
            'message_type' => data_get($payload, 'data.messageType'),
            'queue_connection' => config('queue.default'),
            'sync_processing' => (bool) config('services.evolution.webhook_sync'),
        ]);

        $event = WebhookEvent::create([
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'received',
        ]);

        if (config('services.evolution.webhook_sync')) {
            ProcessEvolutionWebhook::dispatchSync($event);
        } else {
            ProcessEvolutionWebhook::dispatch($event);
        }

        $this->debugLog('info', 'Webhook accepted.', [
            'webhook_event_id' => $event->id,
            'dispatched_sync' => (bool) config('services.evolution.webhook_sync'),
        ]);

        return response()->json(['received' => true]);
    }

    private function debugLog(string $level, string $message, array $context = []): void
    {
        if (! config('services.evolution.webhook_debug')) {
            return;
        }

        Log::log($level, '[contadoc-webhook] '.$message, $context);
    }
}
