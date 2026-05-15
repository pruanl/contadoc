<?php

namespace App\Http\Controllers;

use App\Models\AuthorizedSender;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthorizedSenderController extends Controller
{
    public function index(): View
    {
        return view('authorized-senders.index', [
            'senders' => Auth::user()->authorizedSenders()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('authorized-senders.create', [
            'sender' => new AuthorizedSender(['is_active' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->authorizedSenders()->count() >= $user->authorizedSenderLimit()) {
            return back()
                ->withInput()
                ->withErrors(['phone' => 'Seu plano permite ate '.$user->authorizedSenderLimit().' numero(s) autorizado(s).']);
        }

        $data = $this->validated($request);
        $data['user_id'] = Auth::id();
        $data['normalized_phone'] = Client::normalizePhone($data['phone']);

        AuthorizedSender::create($data);

        return redirect()->route('authorized-senders.index')->with('status', 'Numero autorizado.');
    }

    public function edit(AuthorizedSender $authorizedSender): View
    {
        abort_unless($authorizedSender->user_id === Auth::id(), 403);

        return view('authorized-senders.edit', ['sender' => $authorizedSender]);
    }

    public function update(Request $request, AuthorizedSender $authorizedSender): RedirectResponse
    {
        abort_unless($authorizedSender->user_id === Auth::id(), 403);

        $data = $this->validated($request, $authorizedSender);
        $data['normalized_phone'] = Client::normalizePhone($data['phone']);

        $authorizedSender->update($data);

        return redirect()->route('authorized-senders.index')->with('status', 'Numero atualizado.');
    }

    public function destroy(AuthorizedSender $authorizedSender): RedirectResponse
    {
        abort_unless($authorizedSender->user_id === Auth::id(), 403);

        $authorizedSender->delete();

        return redirect()->route('authorized-senders.index')->with('status', 'Numero removido.');
    }

    private function validated(Request $request, ?AuthorizedSender $sender = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $normalized = Client::normalizePhone($data['phone']);
        $variants = Client::phoneVariants($normalized);

        validator(
            ['normalized_phone' => $normalized],
            [
                'normalized_phone' => [
                    'required',
                    function (string $attribute, mixed $value, \Closure $fail) use ($variants, $sender) {
                        $exists = AuthorizedSender::query()
                            ->whereIn('normalized_phone', $variants)
                            ->when($sender, fn ($query) => $query->whereKeyNot($sender->id))
                            ->exists();

                        if ($exists) {
                            $fail('Este numero ja esta autorizado, considerando as variantes com e sem nono digito.');
                        }
                    },
                ],
            ],
        )->validate();

        return $data;
    }
}
