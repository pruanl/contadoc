<x-layouts.app title="Clientes" header="Clientes">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form class="flex flex-1 gap-2" method="GET">
            <input name="search" value="{{ request('search') }}" placeholder="Buscar por nome, telefone ou email" class="ui-input">
            <select name="status" class="ui-input max-w-44">
                <option value="">Todos</option>
                <option value="active" @selected(request('status') === 'active')>Ativos</option>
                <option value="pending" @selected(request('status') === 'pending')>Pendentes</option>
            </select>
            <button class="ui-button ui-button-secondary">Filtrar</button>
        </form>
        <a href="{{ route('clients.create') }}" class="ui-button ui-button-primary">Novo cliente</a>
    </div>

    <div class="ui-card overflow-hidden">
        <table class="ui-table">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">Cliente</th>
                    <th class="px-4 py-3 font-medium">Telefone</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Envio direto</th>
                    <th class="px-4 py-3 font-medium">Docs</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    @php
                        $whatsappLink = $client->activeWhatsappLink ?? $client->pendingWhatsappLink;
                        $whatsappStatus = $whatsappLink?->status ?? 'inactive';
                        $whatsappStatusLabel = [
                            'active' => 'Ativo',
                            'pending' => 'Convite enviado',
                            'revoked' => 'Revogado',
                            'inactive' => 'Nao ativado',
                        ][$whatsappStatus] ?? $whatsappStatus;
                    @endphp
                    <tr class="{{ $client->status === 'pending' ? 'bg-amber-50/50' : '' }}">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $client->name }}</p>
                            <p class="text-zinc-500">{{ $client->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $client->phone }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$client->status" /></td>
                        <td class="px-4 py-3"><x-status-badge :status="$whatsappStatus">{{ $whatsappStatusLabel }}</x-status-badge></td>
                        <td class="px-4 py-3">{{ $client->documents_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('clients.show', $client) }}" class="font-medium underline">Abrir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center ui-muted">Nenhum cliente cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-layouts.app>
