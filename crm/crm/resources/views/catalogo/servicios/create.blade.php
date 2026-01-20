@extends('layouts.app')

@section('title', 'Nuevo servicio')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nuevo servicio</h1>
            <p class="text-sm text-slate-500">Define un servicio disponible para las peticiones y pedidos.</p>
        </div>
        <a href="{{ route('catalogo.servicios.index') }}" class="text-sm text-[#9d1872] hover:underline">Volver al catálogo</a>
    </header>

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('catalogo.servicios.store') }}" class="space-y-4">
            @csrf
            @include('catalogo.servicios.partials.form', ['servicio' => new \App\Models\Service()])

            <div class="flex justify-end gap-2">
                <a href="{{ route('catalogo.servicios.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]">Guardar</button>
            </div>
        </form>
    </section>
</div>
@endsection