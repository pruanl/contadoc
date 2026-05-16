<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientWhatsappLink;
use App\Services\EvolutionApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $clients = Client::query()
            ->with(['activeWhatsappLink', 'pendingWhatsappLink'])
            ->withCount(['documents', 'whatsappMessages'])
            ->when(! Auth::user()->isAdmin(), fn ($query) => $query->where('user_id', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('normalized_phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create', ['client' => new Client(['status' => Client::STATUS_ACTIVE])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['normalized_phone'] = Client::normalizePhone($data['phone']);

        $data['user_id'] = Auth::id();

        Client::create($data);

        return redirect()->route('clients.index')->with('status', 'Cliente criado.');
    }

    public function show(Client $client): View
    {
        $this->authorizeClient($client);

        $client->load([
            'user',
            'activeWhatsappLink',
            'pendingWhatsappLink',
            'documents' => fn ($query) => $query->latest('received_at'),
            'whatsappMessages' => fn ($query) => $query->latest('message_at')->take(20),
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        $this->authorizeClient($client);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $data = $this->validated($request, $client);
        $data['normalized_phone'] = Client::normalizePhone($data['phone']);

        $client->update($data);

        return redirect()->route('clients.show', $client)->with('status', 'Cliente atualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Cliente removido.');
    }

    public function activateWhatsapp(Client $client, EvolutionApiService $evolution): RedirectResponse
    {
        $this->authorizeClient($client);

        $client->loadMissing('user');

        $normalized = Client::normalizePhone($client->phone);
        $variants = Client::phoneVariants($normalized);

        if ($normalized === '' || $variants === []) {
            return redirect()
                ->route('clients.show', $client)
                ->withErrors(['phone' => 'Informe um telefone valido para ativar envios deste cliente.']);
        }

        $existing = ClientWhatsappLink::query()
            ->whereIn('normalized_phone', $variants)
            ->whereIn('status', [ClientWhatsappLink::STATUS_PENDING, ClientWhatsappLink::STATUS_ACTIVE])
            ->first();

        if ($existing && ($existing->client_id !== $client->id || $existing->user_id !== $client->user_id)) {
            return redirect()
                ->route('clients.show', $client)
                ->withErrors(['phone' => 'Este telefone ja possui ativacao pendente ou ativa em outro cliente/escritorio.']);
        }

        if ($existing?->status === ClientWhatsappLink::STATUS_ACTIVE) {
            return redirect()
                ->route('clients.show', $client)
                ->with('status', 'Envios deste cliente ja estao ativos.');
        }

        $message = 'O Escritorio '.$client->user->name.' deseja cadastrar voce para envio de documentos. Para aceitar, responda Sim.';

        try {
            $result = $evolution->sendText($normalized, $message);

            $link = $existing ?: new ClientWhatsappLink([
                'user_id' => $client->user_id,
                'client_id' => $client->id,
                'phone' => $client->phone,
                'normalized_phone' => $normalized,
                'status' => ClientWhatsappLink::STATUS_PENDING,
                'requested_at' => now(),
            ]);

            $link->fill([
                'phone' => $client->phone,
                'normalized_phone' => $normalized,
                'status' => ClientWhatsappLink::STATUS_PENDING,
                'last_invite_sent_at' => now(),
                'metadata' => array_merge($link->metadata ?? [], [
                    'last_invite_message' => $message,
                    'last_invite_response' => $result,
                ]),
            ]);

            if (! $link->requested_at) {
                $link->requested_at = now();
            }

            $link->save();

            return redirect()->route('clients.show', $client)->with('status', 'Convite de ativacao enviado ao cliente.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('clients.show', $client)
                ->withErrors(['evolution' => $exception->getMessage()]);
        }
    }

    private function validated(Request $request, ?Client $client = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'tax_document' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in([Client::STATUS_ACTIVE, Client::STATUS_PENDING])],
            'notes' => ['nullable', 'string'],
        ]);

        $normalized = Client::normalizePhone($data['phone']);
        $variants = Client::phoneVariants($normalized);

        validator(
            ['normalized_phone' => $normalized],
            [
                'normalized_phone' => [
                    'required',
                    function (string $attribute, mixed $value, \Closure $fail) use ($variants, $client) {
                        $exists = Client::query()
                            ->where('user_id', Auth::id())
                            ->whereIn('normalized_phone', $variants)
                            ->when($client, fn ($query) => $query->whereKeyNot($client->id))
                            ->exists();

                        if ($exists) {
                            $fail('Este telefone ja esta cadastrado para outro cliente, considerando as variantes com e sem nono digito.');
                        }
                    },
                ],
            ],
        )->validate();

        return $data;
    }

    private function authorizeClient(Client $client): void
    {
        abort_unless(Auth::user()->isAdmin() || $client->user_id === Auth::id(), 403);
    }
}
