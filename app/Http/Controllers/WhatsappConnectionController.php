<?php

namespace App\Http\Controllers;

use App\Models\WhatsappInstance;
use App\Services\EvolutionApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WhatsappConnectionController extends Controller
{
    public function show(EvolutionApiService $evolution): View
    {
        $instance = WhatsappInstance::firstOrCreate(
            ['name' => config('services.evolution.instance')],
            ['user_id' => Auth::id(), 'status' => 'disconnected'],
        );

        $status = null;
        $error = null;

        try {
            if (config('services.evolution.url')) {
                $status = $evolution->status($instance->name);
                $webhook = $evolution->findWebhook($instance->name);
                $instance->update([
                    'status' => data_get($status, 'instance.state', data_get($status, 'state', $instance->status)),
                    'metadata' => [
                        'status' => $status,
                        'webhook' => $webhook,
                    ],
                    'last_checked_at' => now(),
                ]);
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        return view('whatsapp.show', compact('instance', 'status', 'error'));
    }

    public function qr(EvolutionApiService $evolution): RedirectResponse
    {
        $instance = WhatsappInstance::firstOrCreate(
            ['name' => config('services.evolution.instance')],
            ['user_id' => Auth::id(), 'status' => 'disconnected'],
        );

        try {
            $instance->update([
                'metadata' => ['qr' => $evolution->qrCode($instance->name)],
                'last_checked_at' => now(),
            ]);

            return redirect()->route('whatsapp.show')->with('status', 'QR Code consultado na Evolution.');
        } catch (\Throwable $exception) {
            return redirect()->route('whatsapp.show')->withErrors(['evolution' => $exception->getMessage()]);
        }
    }

    public function configureWebhook(EvolutionApiService $evolution): RedirectResponse
    {
        $instance = WhatsappInstance::firstOrCreate(
            ['name' => config('services.evolution.instance')],
            ['user_id' => Auth::id(), 'status' => 'disconnected'],
        );

        try {
            $webhookUrl = rtrim(config('app.url'), '/').'/webhooks/evolution';

            if (config('services.evolution.webhook_secret')) {
                $webhookUrl .= '?secret='.urlencode((string) config('services.evolution.webhook_secret'));
            }

            $result = $evolution->setWebhook($webhookUrl, $instance->name);

            $instance->update([
                'metadata' => array_merge($instance->metadata ?? [], [
                    'webhook_configured' => $result,
                    'webhook_url' => $webhookUrl,
                ]),
                'last_checked_at' => now(),
            ]);

            return redirect()->route('whatsapp.show')->with('status', 'Webhook configurado na Evolution.');
        } catch (\Throwable $exception) {
            return redirect()->route('whatsapp.show')->withErrors(['evolution' => $exception->getMessage()]);
        }
    }
}
