@php($role = $role ?? null)

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-1">
        <div>
            <label class="block text-sm font-medium text-slate-700">Nombre del rol</label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $role->name ?? '') }}"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                required
            >
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700">Descripción</label>
            <textarea
                name="description"
                rows="4"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >{{ old('description', $role->description ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input
                type="checkbox"
                name="is_default"
                value="1"
                {{ old('is_default', $role->is_default ?? false) ? 'checked' : '' }}
                class="h-4 w-4 text-[#9d1872]"
            >
            Rol predeterminado para nuevos usuarios
        </label>
    </div>

    <div class="space-y-4 lg:col-span-2">
        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
            <h2 class="text-sm font-semibold text-slate-800 mb-2">Permisos del rol</h2>
            <p class="mb-3 text-xs text-slate-500">
                Marca qué puede hacer un usuario con este rol en cada módulo.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($permissionsByModule as $module => $perms)
                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ ucfirst($module) }}
                        </h3>
                        <div class="space-y-1">
                            @foreach ($perms as $perm)
                                <label class="flex items-center gap-2 text-xs text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $perm->id }}"
                                        class="h-3.5 w-3.5 text-[#9d1872]"
                                        {{ in_array($perm->id, old('permissions', $role ? $role->permissions->pluck('id')->all() : []), true) ? 'checked' : '' }}
                                    >
                                    {{ $perm->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
