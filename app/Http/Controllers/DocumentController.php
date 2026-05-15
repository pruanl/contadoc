<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = Document::query()
            ->with('client')
            ->when(! Auth::user()->isAdmin(), fn ($query) => $query->where('user_id', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->integer('client_id')))
            ->latest('received_at')
            ->paginate(15)
            ->withQueryString();

        return view('documents.index', [
            'documents' => $documents,
            'clients' => $this->clientsForUser(),
        ]);
    }

    public function create(): View
    {
        return view('documents.create', [
            'document' => new Document(['status' => 'new', 'origin' => 'manual']),
            'clients' => $this->clientsForUser(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('user_id', Auth::id())],
            'file' => ['required', 'file', 'max:20480'],
            'status' => ['required', Rule::in(['new', 'reviewed', 'archived', 'pending'])],
        ]);

        $file = $request->file('file');
        $clientId = filled($data['client_id'] ?? null) ? (int) $data['client_id'] : null;
        $path = $file->store($clientId ? 'documents/'.$clientId : 'documents/inbox');

        Document::create([
            'client_id' => $clientId,
            'user_id' => Auth::id(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => $clientId ? $data['status'] : 'pending',
            'origin' => 'manual',
            'received_at' => now(),
        ]);

        return redirect()->route('documents.index')->with('status', 'Documento enviado.');
    }

    public function show(Document $document): View
    {
        $this->authorizeDocument($document);

        $document->load(['client', 'whatsappMessage']);

        return view('documents.show', compact('document'));
    }

    public function edit(Document $document): View
    {
        $this->authorizeDocument($document);

        return view('documents.edit', [
            'document' => $document,
            'clients' => $this->clientsForUser(),
        ]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);

        $data = $request->validate([
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('user_id', $document->user_id ?? Auth::id())],
            'status' => ['required', Rule::in(['new', 'reviewed', 'archived', 'pending'])],
        ]);

        $data['client_id'] = filled($data['client_id'] ?? null) ? (int) $data['client_id'] : null;

        $document->update($data);

        return redirect()->route('documents.show', $document)->with('status', 'Documento atualizado.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);

        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('status', 'Documento removido.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorizeDocument($document);

        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    private function clientsForUser()
    {
        return Client::query()
            ->when(! Auth::user()->isAdmin(), fn ($query) => $query->where('user_id', Auth::id()))
            ->orderBy('name')
            ->get();
    }

    private function authorizeDocument(Document $document): void
    {
        abort_unless(Auth::user()->isAdmin() || $document->user_id === Auth::id(), 403);
    }
}
