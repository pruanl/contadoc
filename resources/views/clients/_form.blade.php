@csrf

<div class="grid gap-4 md:grid-cols-2">
    <label class="ui-label">
        Nome
        <input name="name" value="{{ old('name', $client->name) }}" required class="ui-input mt-1">
    </label>
    <label class="ui-label">
        Telefone
        <input name="phone" value="{{ old('phone', $client->phone) }}" required class="ui-input mt-1">
    </label>
    <label class="ui-label">
        Email
        <input name="email" type="email" value="{{ old('email', $client->email) }}" class="ui-input mt-1">
    </label>
    <label class="ui-label">
        CPF/CNPJ
        <input name="tax_document" value="{{ old('tax_document', $client->tax_document) }}" class="ui-input mt-1">
    </label>
    <label class="ui-label">
        Status
        <select name="status" class="ui-input mt-1">
            <option value="active" @selected(old('status', $client->status) === 'active')>Ativo</option>
            <option value="pending" @selected(old('status', $client->status) === 'pending')>Pendente</option>
        </select>
    </label>
</div>

<label class="ui-label mt-4">
    Observacoes
    <textarea name="notes" rows="4" class="ui-input mt-1">{{ old('notes', $client->notes) }}</textarea>
</label>

<div class="mt-6 flex items-center gap-3">
    <button class="ui-button ui-button-primary">Salvar</button>
    <a href="{{ route('clients.index') }}" class="ui-button ui-button-secondary">Cancelar</a>
</div>
