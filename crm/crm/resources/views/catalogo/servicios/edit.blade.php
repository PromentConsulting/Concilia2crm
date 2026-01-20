@extends('layouts.app')

@section('title', 'Editar servicio')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Editar servicio</h1>
            <p class="text-sm text-slate-500">Actualiza los datos del servicio seleccionado.</p>
        </div>
        <a href="{{ route('catalogo.servicios.index') }}" class="text-sm text-[#9d1872] hover:underline">Volver al catálogo</a>
    </header>

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('catalogo.servicios.update', $servicio) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('catalogo.servicios.partials.form', ['servicio' => $servicio])

            <div class="flex justify-end gap-2">
                <a href="{{ route('catalogo.servicios.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]">Guardar cambios</button>
            </div>
        </form>
    </section>
</div>
@endsection