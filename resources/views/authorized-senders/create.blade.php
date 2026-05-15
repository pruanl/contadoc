<x-layouts.app title="Novo numero autorizado" header="Novo numero autorizado">
    <form method="POST" action="{{ route('authorized-senders.store') }}" class="ui-card ui-card-body">
        @include('authorized-senders._form')
    </form>
</x-layouts.app>
