<?php

namespace App\Http\Controllers;

use App\Models\Client;
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
