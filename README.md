# Contadoc POC

POC em Laravel 13 para controlar clientes, mensagens e documentos recebidos via Evolution API.

O fluxo principal usa um numero oficial do Contadoc. O contador encaminha arquivos para esse numero e informa o cliente no texto:

```text
cliente Empresa Azul
guardar no cliente Maria Silva
do cliente ACME
```

Se o sistema nao encontrar o cliente, o documento fica em triagem para vinculo manual.

## Setup local

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Configure o PostgreSQL local no `.env` e informe os dados da sua Evolution API:

```env
EVOLUTION_API_URL=https://sua-vps.example.com
EVOLUTION_API_KEY=
EVOLUTION_INSTANCE_NAME=contadoc-local
EVOLUTION_WEBHOOK_SECRET=
```

O webhook da Evolution deve apontar para:

```text
POST /webhooks/evolution
```

Para desenvolvimento com Herd, acesse a URL local configurada e crie o primeiro usuario em `/register`.
