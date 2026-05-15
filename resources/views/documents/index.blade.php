<x-layouts.app title="Documentos" header="Documentos">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form class="flex flex-1 gap-2" method="GET">
            <select name="client_id" class="ui-input max-w-64">
                <option value="">Todos os clientes</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected((int) request('client_id') === $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
            <select name="status" class="ui-input max-w-48">
                <option value="">Todos os status</option>
                <option value="new" @selected(request('status') === 'new')>Novo</option>
                <option value="pending" @selected(request('status') === 'pending')>Pendente</option>
                <option value="reviewed" @selected(request('status') === 'reviewed')>Revisado</option>
                <option value="archived" @selected(request('status') === 'archived')>Arquivado</option>
            </select>
            <button class="ui-button ui-button-secondary">Filtrar</button>
        </form>
        <a href="{{ route('documents.create') }}" class="ui-button ui-button-primary">Upload manual</a>
    </div>

    <div class="ui-card overflow-hidden">
        <table class="ui-table">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">Documento</th>
                    <th class="px-4 py-3 font-medium">Cliente</th>
                    <th class="px-4 py-3 font-medium">Origem</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $document->original_name ?? 'Documento sem nome' }}</p>
                            <p class="text-zinc-500">{{ $document->received_at?->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            {{ $document->client?->name ?? 'Triagem' }}
                            @if ($document->client_hint)
                                <p class="text-xs text-zinc-500">Dica: {{ $document->client_hint }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $document->origin }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$document->status" /></td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('documents.show', $document) }}" class="font-medium underline">Abrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center ui-muted">Nenhum documento recebido.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $documents->links() }}</div>
</x-layouts.app>
