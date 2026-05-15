<x-layouts.app title="Upload de documento" header="Upload de documento">
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="ui-card ui-card-body">
        @include('documents._form')
    </form>
</x-layouts.app>
