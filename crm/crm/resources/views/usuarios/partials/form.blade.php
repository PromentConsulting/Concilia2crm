@php($user = $user ?? null)

<div class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700">Nombre</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $user->name ?? '') }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                required
            >
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $user->email ?? '') }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                required
            >
            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">
                Contraseña
                @if($user)<span class="text-xs text-slate-400">(dejar en blanco para no cambiar)</span>@endif
            </label>
            <input
                type="password"
                name="password"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                {{ $user ? '' : 'required' }}
            >
            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Repetir contraseña</label>
            <input
                type="password"
                name="password_confirmation"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                {{ $user ? '' : 'required' }}
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Rol</label>
            <select
                name="role_id"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Sin rol</option>
                @foreach ($roles as $role)
                    <option
                        value="{{ $role->id }}"
                        {{ (int) old('role_id', $user->role_id ?? 0) === $role->id ? 'selected' : '' }}
                    >
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-2 mt-6">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input
                    type="checkbox"
                    name="is_admin"
                    value="1"
                    class="h-4 w-4 text-[#9d1872]"
                    {{ old('is_admin', $user->is_admin ?? false) ? 'checked' : '' }}
                >
                Usuario administrador (todos los permisos)
            </label>
        </div>
    </div>
</div>
