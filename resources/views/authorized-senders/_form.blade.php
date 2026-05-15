@csrf

<div class="grid gap-4 md:grid-cols-2">
    <label class="ui-label">
        Nome
        <input name="name" value="{{ old('name', $sender->name) }}" required class="ui-input mt-1" placeholder="Ex: Fulano escritorio">
    </label>
    <label class="ui-label">
        Telefone
        <input name="phone" value="{{ old('phone', $sender->phone) }}" required class="ui-input mt-1" placeholder="Ex: 5585999999999">
    </label>
</div>

<label class="mt-4 flex items-center gap-2 text-sm font-medium">
    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $sender->is_active ?? true)) class="rounded border-zinc-300">
    Ativo para envio ao numero oficial
</label>

<label class="ui-label mt-4">
    Observacoes
    <textarea name="notes" rows="4" class="ui-input mt-1">{{ old('notes', $sender->notes) }}</textarea>
</label>

<div class="mt-6 flex items-center gap-3">
    <button class="ui-button ui-button-primary">Salvar</button>
    <a href="{{ route('authorized-senders.index') }}" class="ui-button ui-button-secondary">Cancelar</a>
</div>
