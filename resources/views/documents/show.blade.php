<x-layouts.app :title="$document->original_name ?? 'Documento'" header="Documento">
    <div class="mb-4 flex items-center justify-between">
        <x-status-badge :status="$document->status" />
        <div class="flex gap-2">
            @if ($document->file_path)
                <a href="{{ route('documents.file', $document) }}" target="_blank" class="ui-button ui-button-secondary">Abrir arquivo</a>
                <a href="{{ route('documents.download', $document) }}" class="ui-button ui-button-secondary">Baixar</a>
            @endif
            <a href="{{ route('documents.edit', $document) }}" class="ui-button ui-button-secondary">Editar</a>
            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Remover este documento?')">
                @csrf
                @method('DELETE')
                <button class="ui-button ui-button-danger">Remover</button>
            </form>
        </div>
    </div>

    <section class="ui-card ui-card-body">
        <h2 class="font-semibold">{{ $document->original_name ?? 'Documento sem nome' }}</h2>
        <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
            <div>
                <dt class="text-zinc-500">Cliente</dt>
                <dd>
                    @if ($document->client)
                        <a class="underline" href="{{ route('clients.show', $document->client) }}">{{ $document->client->name }}</a>
                    @else
                        Triagem
                    @endif
                </dd>
            </div>
            <div><dt class="text-zinc-500">Origem</dt><dd>{{ $document->origin }}</dd></div>
            <div><dt class="text-zinc-500">Remetente</dt><dd>{{ $document->sender_phone ?: '-' }}</dd></div>
            <div><dt class="text-zinc-500">Cliente informado</dt><dd>{{ $document->client_hint ?: '-' }}</dd></div>
            <div><dt class="text-zinc-500">MIME</dt><dd>{{ $document->mime_type ?: '-' }}</dd></div>
            <div><dt class="text-zinc-500">Tamanho</dt><dd>{{ $document->size ? number_format($document->size / 1024, 1, ',', '.') . ' KB' : '-' }}</dd></div>
            <div><dt class="text-zinc-500">Recebido em</dt><dd>{{ $document->received_at?->format('d/m/Y H:i') ?: '-' }}</dd></div>
            <div><dt class="text-zinc-500">Caminho</dt><dd class="break-all">{{ $document->file_path ?: 'Arquivo nao baixado, apenas registrado' }}</dd></div>
        </dl>
    </section>

    @if ($preview)
        <section class="ui-card mt-6 overflow-hidden">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h2 class="font-semibold">Visualizacao</h2>
            </div>

            @if ($preview['kind'] === 'image')
                <div class="bg-zinc-100 p-4">
                    <img src="{{ $preview['url'] }}" alt="{{ $document->original_name ?? 'Documento' }}" class="mx-auto max-h-[72vh] max-w-full rounded border border-zinc-200 bg-white object-contain">
                </div>
            @else
                <iframe src="{{ $preview['url'] }}" title="{{ $document->original_name ?? 'Documento' }}" class="h-[72vh] w-full bg-white"></iframe>
            @endif
        </section>
    @elseif ($document->file_path)
        <section class="ui-card ui-card-body mt-6">
            <h2 class="font-semibold">Visualizacao indisponivel</h2>
            <p class="mt-2 text-sm text-zinc-600">Este tipo de arquivo nao abre direto no navegador. Use o download para acessar.</p>
        </section>
    @endif

    @if ($document->whatsappMessage)
        <section class="ui-card ui-card-body mt-6">
            <h2 class="font-semibold">Mensagem vinculada</h2>
            <p class="mt-2 text-sm text-zinc-600">{{ $document->whatsappMessage->body ?? 'Mensagem sem texto' }}</p>
        </section>
    @endif
</x-layouts.app>
