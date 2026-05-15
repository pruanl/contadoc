<x-layouts.app title="Editar documento" header="Editar documento">
    <form method="POST" action="{{ route('documents.update', $document) }}" class="ui-card ui-card-body">
        @method('PUT')
        @include('documents._form')
    </form>
</x-layouts.app>
