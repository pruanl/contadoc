<x-layouts.app title="Editar usuario" header="Editar usuario">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="ui-card ui-card-body">
        @method('PUT')
        @include('admin.users._form')
    </form>

    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-4" onsubmit="return confirm('Remover este usuario?')">
        @csrf
        @method('DELETE')
        <button class="ui-button ui-button-danger">Remover usuario</button>
    </form>
</x-layouts.app>
