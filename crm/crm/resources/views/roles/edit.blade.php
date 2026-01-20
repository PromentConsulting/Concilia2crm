@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">
            Editar rol: {{ $role->name }}
        </h1>
    </header>

    <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('roles.partials.form', ['role' => $role])
        <div class="flex justify-between items-center">
            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('¿Eliminar este rol?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs text-rose-600 hover:underline">
                    Eliminar rol
                </button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('roles.index') }}" class="text-sm text-slate-500 hover:underline">
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
