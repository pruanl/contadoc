<x-layouts.app title="Novo cliente" header="Novo cliente">
    <form method="POST" action="{{ route('clients.store') }}" class="ui-card ui-card-body">
        @include('clients._form')
    </form>
</x-layouts.app>
