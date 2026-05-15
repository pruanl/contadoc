<x-layouts.app title="Entrar">
    <div class="ui-card ui-card-body mx-auto mt-16 max-w-md">
        <h1 class="text-xl font-semibold">Entrar no Contadoc</h1>
        <p class="mt-1 text-sm text-zinc-500">Acesse o painel para acompanhar clientes e documentos.</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block text-sm font-medium">
                Email
                <input name="email" type="email" value="{{ old('email') }}" required autofocus class="ui-input mt-1">
            </label>
            <label class="block text-sm font-medium">
                Senha
                <input name="password" type="password" required class="ui-input mt-1">
            </label>
            <label class="flex items-center gap-2 text-sm text-zinc-600">
                <input name="remember" type="checkbox" value="1" class="rounded border-zinc-300">
                Lembrar acesso
            </label>
            <button class="ui-button ui-button-primary w-full">Entrar</button>
        </form>

        <p class="mt-4 text-center text-sm text-zinc-500">
            Ainda nao tem conta? <a href="{{ route('register') }}" class="font-medium text-zinc-950 underline">Criar conta</a>
        </p>
    </div>
</x-layouts.app>
