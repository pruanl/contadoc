<x-layouts.app title="WhatsApp" header="Conexao WhatsApp">
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="ui-card ui-card-body">
            <h2 class="font-semibold">Numero oficial</h2>
            <p class="mt-1 text-sm ui-muted">Use este WhatsApp como caixa de entrada do Contadoc. O contador encaminha arquivos para ca e informa o cliente no texto.</p>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-zinc-500">Nome</dt><dd>{{ $instance->name }}</dd></div>
                <div><dt class="text-zinc-500">Status</dt><dd><x-status-badge :status="$instance->status" /></dd></div>
                <div><dt class="text-zinc-500">Telefone</dt><dd>{{ $instance->connected_phone ?: '-' }}</dd></div>
                <div><dt class="text-zinc-500">Ultima consulta</dt><dd>{{ $instance->last_checked_at?->format('d/m/Y H:i') ?: '-' }}</dd></div>
            </dl>
            <form method="POST" action="{{ route('whatsapp.qr') }}" class="mt-5">
                @csrf
                <button class="ui-button ui-button-primary">Consultar QR Code</button>
            </form>
            <form method="POST" action="{{ route('whatsapp.webhook') }}" class="mt-3">
                @csrf
                <button class="ui-button ui-button-secondary">Configurar webhook</button>
            </form>
        </section>

        <section class="ui-card ui-card-body lg:col-span-2">
            <h2 class="font-semibold">Evolution API</h2>
            @php
                $webhookUrl = rtrim(config('app.url'), '/').'/webhooks/evolution';

                if (config('services.evolution.webhook_secret')) {
                    $webhookUrl .= '?secret='.urlencode((string) config('services.evolution.webhook_secret'));
                }
            @endphp
            <div class="mt-3 rounded-md border border-teal-200 bg-teal-50 px-3 py-2 text-sm text-teal-900">
                O botao de webhook registra na Evolution a URL <strong>{{ $webhookUrl }}</strong>. Em VPS, confirme que APP_URL esta com HTTPS publico.
            </div>
            @if ($error)
                <p class="mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $error }}</p>
            @elseif (! config('services.evolution.url'))
                <p class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">Configure EVOLUTION_API_URL no .env para consultar sua VPS.</p>
            @endif

            @php
                $qr = data_get($instance->metadata, 'qr.base64')
                    ?? data_get($instance->metadata, 'qr.qrcode.base64')
                    ?? data_get($instance->metadata, 'qr.code')
                    ?? null;
            @endphp

            @if ($qr && str_starts_with($qr, 'data:image'))
                <img src="{{ $qr }}" alt="QR Code WhatsApp" class="mt-5 h-64 w-64 rounded-lg border border-zinc-200 object-contain">
            @elseif ($qr)
                <pre class="mt-5 overflow-auto rounded-md bg-zinc-950 p-4 text-xs text-white">{{ $qr }}</pre>
            @endif

            <pre class="mt-5 max-h-96 overflow-auto rounded-md bg-zinc-950 p-4 text-xs text-white">{{ json_encode($instance->metadata ?? $status ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </section>
    </div>
</x-layouts.app>
