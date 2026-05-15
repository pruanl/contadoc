<x-layouts.app title="Criar conta">
    <div class="ui-card ui-card-body mx-auto mt-16 max-w-md">
        <h1 class="text-xl font-semibold">Criar conta</h1>
        <p class="mt-1 text-sm text-zinc-500">Comece com o plano Starter e cadastre seu primeiro numero autorizado.</p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block text-sm font-medium">
                Nome
                <input name="name" value="{{ old('name') }}" required autofocus class="ui-input mt-1">
            </label>
            <label class="block text-sm font-medium">
                Email
                <input name="email" type="email" value="{{ old('email') }}" required class="ui-input mt-1">
            </label>
            <label class="block text-sm font-medium">
                Senha
                <input name="password" type="password" required class="ui-input mt-1">
            </label>
            <label class="block text-sm font-medium">
                Confirmar senha
                <input name="password_confirmation" type="password" required class="ui-input mt-1">
            </label>
            <button class="ui-button ui-button-primary w-full">Criar e entrar</button>
        </form>
    </div>
</x-layouts.app>
