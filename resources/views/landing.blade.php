<x-layouts.app title="Contadoc">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <header class="flex items-center justify-between py-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-md bg-zinc-950 text-sm font-semibold text-white">C</span>
                <span>
                    <span class="block font-semibold leading-5">Contadoc</span>
                    <span class="block text-xs ui-muted">Arquivo recebido, organizado e online</span>
                </span>
            </a>
            <nav class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="ui-button ui-button-secondary">Entrar</a>
                <a href="{{ route('register') }}" class="ui-button ui-button-primary">Criar conta</a>
            </nav>
        </header>

        <section class="overflow-hidden rounded-md border border-zinc-200 bg-zinc-950 text-white shadow-sm">
            <div class="grid min-h-[74vh] lg:grid-cols-[0.95fr_1.05fr]">
                <div class="flex flex-col justify-center px-6 py-12 sm:px-10 lg:px-12">
                    <p class="text-sm font-medium uppercase tracking-[0.18em] text-teal-300">Central de documentos por WhatsApp</p>
                    <h1 class="mt-5 max-w-3xl text-4xl font-semibold tracking-tight sm:text-6xl">
                        Receba documentos e fotos sem perder nada em conversas.
                    </h1>
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-zinc-300">
                        Envie ou encaminhe o arquivo para o numero oficial do Contadoc. O sistema recebe o documento ou foto, identifica os dados disponiveis e coloca tudo online no painel automaticamente.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('register') }}" class="ui-button bg-white text-zinc-950 hover:bg-zinc-100">Comecar agora</a>
                        <a href="{{ route('login') }}" class="ui-button border border-white/20 bg-white/10 text-white hover:bg-white/15">Acessar painel</a>
                    </div>
                </div>

                <div class="relative min-h-[34rem] border-t border-white/10 bg-zinc-900 lg:border-l lg:border-t-0">
                    <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(255,255,255,0.06)_1px,transparent_1px),linear-gradient(180deg,rgba(255,255,255,0.06)_1px,transparent_1px)] bg-[size:44px_44px]"></div>
                    <div class="relative flex h-full items-center justify-center p-5 sm:p-8">
                        <div class="w-full max-w-2xl overflow-hidden rounded-md border border-white/12 bg-white text-zinc-950 shadow-2xl">
                            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-500">Painel Contadoc</p>
                                    <p class="mt-1 font-semibold">Documentos recebidos</p>
                                </div>
                                <span class="rounded-md bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Online agora</span>
                            </div>

                            <div class="grid gap-0 md:grid-cols-[0.85fr_1.15fr]">
                                <div class="border-b border-zinc-200 bg-zinc-50 p-5 md:border-b-0 md:border-r">
                                    <p class="text-sm font-medium">Fluxo automatico</p>
                                    <div class="mt-5 space-y-4">
                                        <div class="rounded-md border border-zinc-200 bg-white p-4">
                                            <p class="text-xs font-medium text-zinc-500">1. WhatsApp oficial</p>
                                            <p class="mt-2 text-sm">Foto, PDF ou comprovante recebido.</p>
                                        </div>
                                        <div class="rounded-md border border-teal-200 bg-teal-50 p-4">
                                            <p class="text-xs font-medium text-teal-700">2. Processamento</p>
                                            <p class="mt-2 text-sm text-teal-950">Arquivo convertido, nomeado e salvo.</p>
                                        </div>
                                        <div class="rounded-md border border-zinc-200 bg-white p-4">
                                            <p class="text-xs font-medium text-zinc-500">3. Painel</p>
                                            <p class="mt-2 text-sm">Documento pronto para revisar.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <div class="grid grid-cols-3 gap-3">
                                        <div class="rounded-md border border-zinc-200 p-3">
                                            <p class="text-xs text-zinc-500">Hoje</p>
                                            <p class="mt-1 text-2xl font-semibold">18</p>
                                        </div>
                                        <div class="rounded-md border border-zinc-200 p-3">
                                            <p class="text-xs text-zinc-500">Triagem</p>
                                            <p class="mt-1 text-2xl font-semibold">4</p>
                                        </div>
                                        <div class="rounded-md border border-zinc-200 p-3">
                                            <p class="text-xs text-zinc-500">Clientes</p>
                                            <p class="mt-1 text-2xl font-semibold">12</p>
                                        </div>
                                    </div>

                                    <div class="mt-5 overflow-hidden rounded-md border border-zinc-200">
                                        <div class="grid grid-cols-[1fr_auto] gap-4 bg-zinc-50 px-4 py-3 text-xs font-medium text-zinc-500">
                                            <span>Arquivo</span>
                                            <span>Status</span>
                                        </div>
                                        <div class="divide-y divide-zinc-200 text-sm">
                                            <div class="grid grid-cols-[1fr_auto] gap-4 px-4 py-3">
                                                <span>comprovante-maio.jpg</span>
                                                <span class="font-medium text-emerald-700">Online</span>
                                            </div>
                                            <div class="grid grid-cols-[1fr_auto] gap-4 px-4 py-3">
                                                <span>balancete.pdf</span>
                                                <span class="font-medium text-sky-700">Novo</span>
                                            </div>
                                            <div class="grid grid-cols-[1fr_auto] gap-4 px-4 py-3">
                                                <span>foto-recibo.jpeg</span>
                                                <span class="font-medium text-amber-700">Triagem</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 rounded-md border border-zinc-200 bg-zinc-50 p-4">
                                        <p class="text-xs font-medium uppercase tracking-[0.16em] text-zinc-500">Preview</p>
                                        <div class="mt-3 rounded-md border border-zinc-200 bg-white p-4">
                                            <div class="h-24 rounded-md border border-dashed border-zinc-300 bg-zinc-50"></div>
                                            <div class="mt-3 h-2 w-3/4 rounded bg-zinc-200"></div>
                                            <div class="mt-2 h-2 w-1/2 rounded bg-zinc-200"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 py-12 md:grid-cols-3">
            <div class="ui-card ui-card-body">
                <p class="text-sm font-medium text-teal-700">Receba</p>
                <h2 class="mt-2 font-semibold">Um numero oficial</h2>
                <p class="mt-2 text-sm ui-muted">Clientes, equipe ou numeros autorizados encaminham documentos e fotos para o WhatsApp oficial do Contadoc.</p>
            </div>
            <div class="ui-card ui-card-body">
                <p class="text-sm font-medium text-sky-700">Organize</p>
                <h2 class="mt-2 font-semibold">Arquivo salvo com contexto</h2>
                <p class="mt-2 text-sm ui-muted">O sistema guarda MIME, extensao, origem, remetente e status para facilitar triagem e auditoria.</p>
            </div>
            <div class="ui-card ui-card-body">
                <p class="text-sm font-medium text-amber-700">Acesse</p>
                <h2 class="mt-2 font-semibold">Tudo online no painel</h2>
                <p class="mt-2 text-sm ui-muted">Depois do envio, o arquivo fica disponivel para visualizar, baixar, vincular a um cliente ou revisar.</p>
            </div>
        </section>

        <section class="mb-12 rounded-md border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="grid gap-8 lg:grid-cols-[0.7fr_1.3fr]">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.16em] text-zinc-500">Como funciona</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight">Do WhatsApp ao arquivo online em poucos segundos.</h2>
                </div>
                <div class="grid gap-3 md:grid-cols-4">
                    <div class="rounded-md border border-zinc-200 p-4">
                        <p class="text-xs font-medium text-zinc-500">01</p>
                        <p class="mt-3 text-sm font-medium">Recebe no numero oficial</p>
                    </div>
                    <div class="rounded-md border border-zinc-200 p-4">
                        <p class="text-xs font-medium text-zinc-500">02</p>
                        <p class="mt-3 text-sm font-medium">Baixa a midia com seguranca</p>
                    </div>
                    <div class="rounded-md border border-zinc-200 p-4">
                        <p class="text-xs font-medium text-zinc-500">03</p>
                        <p class="mt-3 text-sm font-medium">Salva com extensao correta</p>
                    </div>
                    <div class="rounded-md border border-zinc-200 p-4">
                        <p class="text-xs font-medium text-zinc-500">04</p>
                        <p class="mt-3 text-sm font-medium">Disponibiliza no painel</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
