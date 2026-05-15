<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEvolutionWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            return response()->json(['message' => 'Invalid webhook secret.'], 403);
        }

        $payload = $request->all();

        $event = WebhookEvent::create([
            'event_type' => $payload['event'] ?? $payload['type'] ?? $routeEvent,
            'payload' => $payload,
            'status' => 'received',
        ]);

        ProcessEvolutionWebhook::dispatch($event);

        return response()->json(['received' => true]);
    }
}
