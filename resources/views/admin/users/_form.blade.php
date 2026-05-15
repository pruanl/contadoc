@csrf

<div class="grid gap-4 md:grid-cols-2">
    <label class="ui-label">
        Nome
        <input name="name" value="{{ old('name', $user->name) }}" required class="ui-input mt-1">
    </label>
    <label class="ui-label">
        Email
        <input name="email" type="email" value="{{ old('email', $user->email) }}" required class="ui-input mt-1">
    </label>
    <label class="ui-label">
        Perfil
        <select name="role" class="ui-input mt-1">
            <option value="user" @selected(old('role', $user->role) === 'user')>Usuario</option>
            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
        </select>
    </label>
    <label class="ui-label">
        Plano
        <select name="plan" class="ui-input mt-1">
            <option value="starter" @selected(old('plan', $user->plan) === 'starter')>Starter - 1 numero</option>
            <option value="pro" @selected(old('plan', $user->plan) === 'pro')>Pro - 5 numeros</option>
            <option value="business" @selected(old('plan', $user->plan) === 'business')>Business - 20 numeros</option>
        </select>
    </label>
    <label class="ui-label">
        Senha
        <input name="password" type="password" @required(! $user->exists) class="ui-input mt-1">
    </label>
    <label class="ui-label">
        Confirmar senha
        <input name="password_confirmation" type="password" @required(! $user->exists) class="ui-input mt-1">
    </label>
</div>

<div class="mt-6 flex items-center gap-3">
    <button class="ui-button ui-button-primary">Salvar</button>
    <a href="{{ route('admin.users.index') }}" class="ui-button ui-button-secondary">Cancelar</a>
</div>
