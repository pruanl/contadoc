<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Contadoc' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ui-shell antialiased">
    <div class="min-h-screen lg:flex">
        @auth
            <aside class="ui-sidebar border-b lg:sticky lg:top-0 lg:min-h-screen lg:w-68 lg:border-b-0 lg:border-r">
                <div class="flex h-16 items-center border-b border-zinc-200 px-5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-zinc-950 text-sm font-semibold text-white">C</span>
                        <span>
                            <span class="block text-sm font-semibold leading-5">Contadoc</span>
                            <span class="block text-xs text-zinc-500">Controle documental</span>
                        </span>
                    </a>
                </div>
                <nav class="space-y-1 p-3">
                    <a class="ui-nav-link {{ request()->routeIs('dashboard') ? 'ui-nav-link-active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="h-2 w-2 rounded-full bg-teal-500"></span>Dashboard
                    </a>
                    <a class="ui-nav-link {{ request()->routeIs('clients.*') ? 'ui-nav-link-active' : '' }}" href="{{ route('clients.index') }}">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>Clientes
                    </a>
                    <a class="ui-nav-link {{ request()->routeIs('authorized-senders.*') ? 'ui-nav-link-active' : '' }}" href="{{ route('authorized-senders.index') }}">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>Numeros autorizados
                    </a>
                    <a class="ui-nav-link {{ request()->routeIs('send-instructions') ? 'ui-nav-link-active' : '' }}" href="{{ route('send-instructions') }}">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>Como enviar
                    </a>
                    <a class="ui-nav-link {{ request()->routeIs('documents.*') ? 'ui-nav-link-active' : '' }}" href="{{ route('documents.index') }}">
                        <span class="h-2 w-2 rounded-full bg-violet-500"></span>Documentos
                    </a>
                    @can('admin')
                        <a class="ui-nav-link {{ request()->routeIs('whatsapp.*') ? 'ui-nav-link-active' : '' }}" href="{{ route('whatsapp.show') }}">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>WhatsApp oficial
                        </a>
                        <a class="ui-nav-link {{ request()->routeIs('admin.users.*') ? 'ui-nav-link-active' : '' }}" href="{{ route('admin.users.index') }}">
                            <span class="h-2 w-2 rounded-full bg-zinc-900"></span>Usuarios
                        </a>
                    @endcan
                </nav>
            </aside>
        @endauth

        <main class="flex-1">
            @auth
                <header class="flex h-16 items-center justify-between border-b border-zinc-200 bg-white/70 px-6 backdrop-blur">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Painel administrativo</p>
                        <h1 class="font-semibold tracking-tight">{{ $header ?? 'Contadoc' }}</h1>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="ui-button ui-button-secondary">Sair</button>
                    </form>
                </header>
            @endauth

            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
