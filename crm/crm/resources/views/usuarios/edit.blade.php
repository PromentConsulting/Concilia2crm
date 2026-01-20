@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">
            Editar usuario: {{ $user->name }}
        </h1>
    </header>

    <form method="POST" action="{{ route('usuarios.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('usuarios.partials.form', ['user' => $user])

        {{-- PERMISOS PERSONALIZADOS --}}
        <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
            <h2 class="text-sm font-semibold text-slate-800">Permisos del usuario</h2>
            <p class="text-xs text-slate-500">
                Este usuario hereda los permisos de su rol. Puedes sobrescribir permiso a permiso:
                <strong>Usar rol</strong>, <strong>Permitir</strong> o <strong>Denegar</strong>.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($permissionsByModule as $module => $perms)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {{ ucfirst($module) }}
                        </h3>
                        <div class="space-y-2">
                            @foreach ($perms as $perm)
                                @php
                                    $override = $overrides[$perm->id] ?? null; // null, true, false
                                    $inRole   = in_array($perm->id, $rolePermIds, true);
                                @endphp
                                <div class="space-y-1 rounded-md bg-white px-2 py-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-xs text-slate-800">
                                            {{ $perm->name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            @if ($override === null)
                                                Hereda:
                                                <span class="font-medium {{ $inRole ? 'text-emerald-600' : 'text-slate-500' }}">
                                                    {{ $inRole ? 'Permitido' : 'Denegado' }}
                                                </span>
                                            @else
                                                Override:
                                                <span class="font-medium {{ $override ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ $override ? 'Permitido' : 'Denegado' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-[11px] text-slate-600">
                                        <label class="inline-flex items-center gap-1">
                                            <input
                                                type="radio"
                                                name="perm[{{ $perm->id }}]"
                                                value=""
                                                {{ $override === null ? 'checked' : '' }}
                                            >
                                            Usar rol
                                        </label>
                                        <label class="inline-flex items-center gap-1">
                                            <input
                                                type="radio"
                                                name="perm[{{ $perm->id }}]"
                                                value="1"
                                                {{ $override === true ? 'checked' : '' }}
                                            >
                                            Permitir
                                        </label>
                                        <label class="inline-flex items-center gap-1">
                                            <input
                                                type="radio"
                                                name="perm[{{ $perm->id }}]"
                                                value="0"
                                                {{ $override === false ? 'checked' : '' }}
                                            >
                                            Denegar
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex justify-between items-center">
            @if (auth()->id() !== $user->id)
                <form method="POST" action="{{ route('usuarios.destroy', $user) }}" onsubmit="return confirm('¿Eliminar este usuario?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-rose-600 hover:underline">
                        Eliminar usuario
                    </button>
                </form>
            @endif

            <div class="flex gap-2">
                <a href="{{ route('usuarios.index') }}" class="text-sm text-slate-500 hover:underline">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
                >
                    Guardar cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
