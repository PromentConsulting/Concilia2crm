@extends('layouts.app')

@section('title', 'Editar factura')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Editar factura</h1>
            <p class="mt-1 text-sm text-slate-500">Actualiza los datos de la factura seleccionada.</p>
        </div>
        <a href="{{ route('facturas.show', $factura) }}" class="text-sm text-slate-600 hover:underline">Volver a la ficha</a>
    </header>

    <form method="POST" action="{{ route('facturas.update', $factura) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('facturas.partials.form', ['factura' => $factura])

        @include('facturas.partials.lineas', [
            'factura' => $factura,
            'services' => $services,
            'categories' => $categories,
        ])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('facturas.show', $factura) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                Cancelar
            </a>
            <button type="submit" class="rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection