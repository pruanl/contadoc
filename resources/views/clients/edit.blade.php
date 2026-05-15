<x-layouts.app title="Editar cliente" header="Editar cliente">
    <form method="POST" action="{{ route('clients.update', $client) }}" class="ui-card ui-card-body">
        @method('PUT')
        @include('clients._form')
    </form>
</x-layouts.app>
