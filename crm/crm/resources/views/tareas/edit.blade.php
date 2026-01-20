@extends('layouts.app')

@section('title', 'Editar tarea')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Editar tarea</h1>
        </div>
        <a href="{{ route('tareas.index') }}" class="text-sm text-slate-600 hover:text-slate-900">
            Volver al listado
        </a>
    </header>

    <form method="POST" action="{{ route('tareas.update', $tarea) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('tareas.partials.form', [
            'tarea'   => $tarea,
            'users'   => $users,
            'accounts'=> $accounts,
            'contacts'=> $contacts,
            'prefill' => $prefill,
        ])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('tareas.index') }}" class="text-sm text-slate-600 hover:text-slate-900">
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
            >
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection
