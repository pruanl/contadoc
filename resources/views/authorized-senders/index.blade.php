<x-layouts.app title="Numeros autorizados" header="Numeros autorizados">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm ui-muted">
            Cadastre os telefones do contador ou da equipe que podem encaminhar arquivos para o numero oficial do Contadoc.
        </p>
        <a href="{{ route('authorized-senders.create') }}" class="ui-button ui-button-primary">Novo numero</a>
    </div>

    <div class="ui-card overflow-hidden">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($senders as $sender)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $sender->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $sender->notes }}</p>
                        </td>
                        <td>{{ $sender->phone }}</td>
                        <td><x-status-badge :status="$sender->is_active ? 'active' : 'archived'">{{ $sender->is_active ? 'Ativo' : 'Inativo' }}</x-status-badge></td>
                        <td class="text-right">
                            <a href="{{ route('authorized-senders.edit', $sender) }}" class="font-medium underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center ui-muted">Nenhum numero autorizado ainda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $senders->links() }}</div>
</x-layouts.app>
