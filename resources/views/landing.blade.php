<x-layouts.app title="Contadoc">
    <div class="mx-auto max-w-6xl">
        <header class="flex items-center justify-between py-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-md bg-zinc-950 text-sm font-semibold text-white">C</span>
                <span>
                    <span class="block font-semibold leading-5">Contadoc</span>
                    <span class="block text-xs ui-muted">Documentos pelo WhatsApp</span>
                </span>
            </a>
            <nav class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="ui-button ui-button-secondary">Entrar</a>
                <a href="{{ route('register') }}" class="ui-button ui-button-primary">Criar conta</a>
            </nav>
        </header>

        <section class="grid min-h-[72vh] items-center gap-10 py-12 lg:grid-cols-[1.05fr_0.95fr]">
            <div>
                <div class="inline-flex rounded-md border border-teal-200 bg-teal-50 px-3 py-1 text-sm font-medium text-teal-900">
                    Um numero oficial para receber, organizar e auditar documentos
                </div>
                <h1 class="mt-5 max-w-3xl text-4xl font-semibold tracking-tight text-zinc-950 sm:text-6xl">
                    Pare de procurar documentos em conversas de WhatsApp.
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-600">
                    O contador cadastra seus numeros autorizados, encaminha arquivos para o WhatsApp oficial do Contadoc e informa o cliente. O sistema recebe, identifica e organiza tudo em um painel simples.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="ui-button ui-button-primary">Testar agora</a>
                    <a href="{{ route('login') }}" class="ui-button ui-button-secondary">Acessar painel</a>
                </div>
            </div>

            <div class="ui-card overflow-hidden">
                <div class="ui-card-header">
                    <p class="text-sm font-medium ui-muted">Fluxo no WhatsApp</p>
                </div>
                <div class="space-y-4 p-5">
                    <div class="rounded-lg bg-zinc-100 p-4">
                        <p class="text-sm font-medium">Fulano envia para o Contadoc</p>
                        <p class="mt-2 rounded-md bg-white p-3 text-sm shadow-sm">cliente Empresa Azul</p>
                        <p class="mt-2 text-xs ui-muted">anexo: balancete-abril.pdf</p>
                    </div>
                    <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-950">Contadoc processa</p>
                        <p class="mt-1 text-sm text-teal-900">Remetente autorizado, cliente encontrado, documento salvo.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-md border border-zinc-200 p-3">
                            <p class="text-xs ui-muted">Cliente</p>
                            <p class="font-medium">Empresa Azul</p>
                        </div>
                        <div class="rounded-md border border-zinc-200 p-3">
                            <p class="text-xs ui-muted">Status</p>
                            <p class="font-medium">Novo</p>
                        </div>
                        <div class="rounded-md border border-zinc-200 p-3">
                            <p class="text-xs ui-muted">Origem</p>
                            <p class="font-medium">WhatsApp</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 pb-12 md:grid-cols-3">
            <div class="ui-card ui-card-body">
                <h2 class="font-semibold">Numeros autorizados</h2>
                <p class="mt-2 text-sm ui-muted">Somente telefones liberados pelo usuario podem enviar arquivos para o sistema.</p>
            </div>
            <div class="ui-card ui-card-body">
                <h2 class="font-semibold">Triagem segura</h2>
                <p class="mt-2 text-sm ui-muted">Quando o cliente nao e encontrado, o documento fica pendente para revisao manual.</p>
            </div>
            <div class="ui-card ui-card-body">
                <h2 class="font-semibold">Pronto para planos</h2>
                <p class="mt-2 text-sm ui-muted">Starter, Pro e Business limitam quantos numeros cada conta pode cadastrar.</p>
            </div>
        </section>
    </div>
</x-layouts.app>
