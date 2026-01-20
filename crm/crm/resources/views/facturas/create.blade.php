@extends('layouts.app')

@section('title', 'Nueva factura')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nueva factura</h1>
            <p class="mt-1 text-sm text-slate-500">Registra una nueva factura en el sistema.</p>
        </div>
        <a href="{{ route('facturas.index') }}" class="text-sm text-slate-600 hover:underline">Volver al listado</a>
    </header>

    @php($idempotencyKey = (string) \Illuminate\Support\Str::uuid())
    <form
        method="POST"
        action="{{ route('facturas.store') }}"
        class="space-y-6"
        x-data="{ submitting: false }"
        @submit="submitting = true"
    >
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
        @include('facturas.partials.form', ['factura' => $factura])

        @include('facturas.partials.lineas', [
            'factura' => null,
            'services' => $services,
            'categories' => $categories,
            'lineasPreset' => $lineasPreset,
        ])

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('facturas.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                Cancelar
            </a>
            <button
                type="submit"
                class="rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="submitting"
            >
                Guardar factura
            </button>
        </div>
    </form>
</div>
@endsection