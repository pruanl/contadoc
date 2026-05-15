<x-layouts.app title="Editar numero autorizado" header="Editar numero autorizado">
    <form method="POST" action="{{ route('authorized-senders.update', $sender) }}" class="ui-card ui-card-body">
        @method('PUT')
        @include('authorized-senders._form')
    </form>

    <form method="POST" action="{{ route('authorized-senders.destroy', $sender) }}" class="mt-4" onsubmit="return confirm('Remover este numero autorizado?')">
        @csrf
        @method('DELETE')
        <button class="ui-button ui-button-danger">Remover numero</button>
    </form>
</x-layouts.app>
