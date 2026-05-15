<x-layouts.app :title="$client->name" :header="$client->name">
    <div class="mb-4 flex items-center justify-between">
        <x-status-badge :status="$client->status" />
        <div class="flex gap-2">
            <a href="{{ route('clients.edit', $client) }}" class="ui-button ui-button-secondary">Editar</a>
            <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Remover este cliente?')">
                @csrf
                @method('DELETE')
                <button class="ui-button ui-button-danger">Remover</button>
            </form>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="ui-card ui-card-body">
            <h2 class="font-semibold">Dados</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-zinc-500">Telefone</dt><dd>{{ $client->phone }}</dd></div>
                <div><dt class="text-zinc-500">Email</dt><dd>{{ $client->email ?: '-' }}</dd></div>
                <div><dt class="text-zinc-500">CPF/CNPJ</dt><dd>{{ $client->tax_document ?: '-' }}</dd></div>
                <div><dt class="text-zinc-500">Observacoes</dt><dd class="whitespace-pre-line">{{ $client->notes ?: '-' }}</dd></div>
            </dl>
        </section>

        <section class="ui-card overflow-hidden lg:col-span-2">
            <div class="ui-card-header"><h2 class="font-semibold">Documentos</h2></div>
            <div class="divide-y divide-zinc-100">
                @forelse ($client->documents as $document)
                    <a href="{{ route('documents.show', $document) }}" class="flex items-center justify-between gap-3 px-5 py-4 hover:bg-zinc-50">
                        <div>
                            <p class="font-medium">{{ $document->original_name ?? 'Documento sem nome' }}</p>
                            <p class="text-sm text-zinc-500">{{ $document->received_at?->format('d/m/Y H:i') }} · {{ $document->origin }}</p>
                        </div>
                        <x-status-badge :status="$document->status" />
                    </a>
                @empty
                    <p class="px-5 py-6 text-sm text-zinc-500">Nenhum documento vinculado.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="ui-card mt-6 overflow-hidden">
        <div class="ui-card-header"><h2 class="font-semibold">Mensagens recentes</h2></div>
        <div class="divide-y divide-zinc-100">
            @forelse ($client->whatsappMessages as $message)
                <div class="px-5 py-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">{{ $message->direction === 'outgoing' ? 'Enviada' : 'Recebida' }}</span>
                        <span class="text-xs text-zinc-500">{{ $message->message_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-zinc-600">{{ $message->body ?? 'Mensagem sem texto' }}</p>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-zinc-500">Nenhuma mensagem capturada.</p>
            @endforelse
        </div>
    </section>
</x-layouts.app>
