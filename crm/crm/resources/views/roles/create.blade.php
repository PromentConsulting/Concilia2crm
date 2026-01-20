@extends('layouts.app')

@section('title', 'Nuevo rol')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">Nuevo rol</h1>
    </header>

    <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
        @csrf
        @include('roles.partials.form')
        <div class="flex justify-end gap-2">
            <a href="{{ route('roles.index') }}" class="text-sm text-slate-500 hover:underline">
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
            >
                Guardar rol
            </button>
        </div>
    </form>
</div>
@endsection
