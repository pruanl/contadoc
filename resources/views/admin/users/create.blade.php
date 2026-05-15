<x-layouts.app title="Novo usuario" header="Novo usuario">
    <form method="POST" action="{{ route('admin.users.store') }}" class="ui-card ui-card-body">
        @include('admin.users._form')
    </form>
</x-layouts.app>
