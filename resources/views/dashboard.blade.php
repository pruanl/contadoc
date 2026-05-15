<x-layouts.app title="Dashboard" header="Dashboard">
    <div class="grid gap-4 md:grid-cols-4">
        <div class="ui-card ui-card-body">
            <p class="text-sm font-medium ui-muted">Clientes</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $clientsCount }}</p>
        </div>
        <div class="ui-card ui-card-body">
            <p class="text-sm font-medium ui-muted">Pendentes</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $pendingClientsCount }}</p>
        </div>
        <div class="ui-card ui-card-body">
            <p class="text-sm font-medium ui-muted">Documentos</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $documentsCount }}</p>
        </div>
        <div class="ui-card ui-card-body">
            <p class="text-sm font-medium ui-muted">Docs em triagem</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight">{{ $pendingDocumentsCount }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="ui-card overflow-hidden">
            <div class="ui-card-header flex items-center justify-between">
                <h2 class="font-semibold">Documentos recentes</h2>
                <a href="{{ route('documents.index') }}" class="text-sm font-medium underline">Ver todos</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($recentDocuments as $document)
                    <a href="{{ route('documents.show', $document) }}" class="block px-5 py-4 hover:bg-zinc-50">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-medium">{{ $document->original_name ?? 'Documento sem nome' }}</p>
                                <p class="text-sm text-zinc-500">{{ $document->client?->name ?? 'Aguardando vinculo' }}</p>
                            </div>
                            <x-status-badge :status="$document->status" />
                        </div>
                    </a>
                @empty
                    <p class="px-5 py-6 text-sm text-zinc-500">Nenhum documento recebido ainda.</p>
                @endforelse
            </div>
        </section>

        <section class="ui-card overflow-hidden">
            <div class="ui-card-header">
                <h2 class="font-semibold">Mensagens recentes</h2>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse ($recentMessages as $message)
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-medium">{{ $message->client?->name ?? $message->remote_phone }}</p>
                            <span class="text-xs text-zinc-500">{{ $message->message_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="mt-1 line-clamp-2 text-sm text-zinc-600">{{ $message->body ?? 'Mensagem sem texto' }}</p>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-zinc-500">Nenhuma mensagem capturada ainda.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
