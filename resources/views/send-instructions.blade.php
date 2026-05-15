<x-layouts.app title="Como enviar arquivos" header="Como enviar arquivos">
    <div class="grid gap-6 lg:grid-cols-3">
        <section class="ui-card ui-card-body lg:col-span-2">
            <h2 class="font-semibold">Envio pelo WhatsApp oficial</h2>
            <p class="mt-2 text-sm ui-muted">
                Encaminhe documentos para o numero oficial do Contadoc usando um dos seus numeros autorizados.
            </p>

            <div class="mt-5 rounded-md border border-zinc-200 bg-zinc-50 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Numero oficial</p>
                <p class="mt-1 text-lg font-semibold">{{ config('services.evolution.official_phone', 'Configure CONTADOC_OFFICIAL_WHATSAPP no .env') }}</p>
            </div>

            <div class="mt-5 space-y-3">
                <p class="text-sm font-medium">Na legenda ou mensagem, use um destes formatos:</p>
                <pre class="overflow-auto rounded-md bg-zinc-950 p-4 text-sm text-white">cliente Empresa Azul
guardar no cliente Maria Silva
do cliente ACME</pre>
                <p class="text-sm ui-muted">Se o cliente nao for encontrado, o documento cai em triagem para voce vincular manualmente.</p>
            </div>
        </section>

        <section class="ui-card ui-card-body">
            <h2 class="font-semibold">Seus numeros liberados</h2>
            <p class="mt-1 text-sm ui-muted">Plano {{ auth()->user()->plan }}: ate {{ auth()->user()->authorizedSenderLimit() }} numero(s).</p>
            <div class="mt-4 space-y-3">
                @forelse (auth()->user()->authorizedSenders as $sender)
                    <div class="rounded-md border border-zinc-200 p-3">
                        <p class="font-medium">{{ $sender->name }}</p>
                        <p class="text-sm text-zinc-500">{{ $sender->phone }}</p>
                    </div>
                @empty
                    <p class="text-sm ui-muted">Cadastre pelo menos um numero autorizado para comecar.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
