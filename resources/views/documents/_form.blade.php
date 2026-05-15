@csrf

<div class="grid gap-4 md:grid-cols-2">
    <label class="ui-label">
        Cliente
        <select name="client_id" class="ui-input mt-1">
            <option value="">Sem cliente / triagem</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected((int) old('client_id', $document->client_id) === $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="ui-label">
        Status
        <select name="status" required class="ui-input mt-1">
            <option value="new" @selected(old('status', $document->status) === 'new')>Novo</option>
            <option value="pending" @selected(old('status', $document->status) === 'pending')>Pendente</option>
            <option value="reviewed" @selected(old('status', $document->status) === 'reviewed')>Revisado</option>
            <option value="archived" @selected(old('status', $document->status) === 'archived')>Arquivado</option>
        </select>
    </label>
</div>

@if (! $document->exists)
    <label class="ui-label mt-4">
        Arquivo
        <input name="file" type="file" required class="mt-1 block w-full rounded-md border border-zinc-200 bg-white text-sm shadow-sm file:mr-4 file:border-0 file:bg-zinc-950 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white">
    </label>
@endif

<div class="mt-6 flex items-center gap-3">
    <button class="ui-button ui-button-primary">Salvar</button>
    <a href="{{ route('documents.index') }}" class="ui-button ui-button-secondary">Cancelar</a>
</div>
