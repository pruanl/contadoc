<x-layouts.app title="Usuarios" header="Usuarios">
    <div class="mb-4 flex justify-end">
        <a href="{{ route('admin.users.create') }}" class="ui-button ui-button-primary">Novo usuario</a>
    </div>

    <div class="ui-card overflow-hidden">
        <table class="ui-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Perfil</th>
                    <th>Plano</th>
                    <th>Numeros</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $user->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                        </td>
                        <td><x-status-badge :status="$user->role === 'admin' ? 'active' : 'archived'">{{ $user->role }}</x-status-badge></td>
                        <td>{{ $user->plan }}</td>
                        <td>{{ $user->authorized_senders_count }} / {{ $user->authorizedSenderLimit() }}</td>
                        <td class="text-right"><a href="{{ route('admin.users.edit', $user) }}" class="font-medium underline">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.app>
