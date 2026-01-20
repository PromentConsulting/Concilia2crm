@extends('layouts.app')

@section('title', 'Nuevo documento')

@section('content')
<div class="max-w-xl space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Nuevo documento
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Sube y vincula un documento a una cuenta, solicitud, petición o pedido.
            </p>
        </div>
    </header>

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Prefill relaciones ocultas --}}
            <input type="hidden" name="account_id"   value="{{ $prefill['account_id'] }}">
            <input type="hidden" name="solicitud_id" value="{{ $prefill['solicitud_id'] }}">
            <input type="hidden" name="peticion_id"  value="{{ $prefill['peticion_id'] }}">
            <input type="hidden" name="pedido_id"    value="{{ $prefill['pedido_id'] }}">

            <div>
                <label class="block text-sm font-medium text-slate-700">Título *</label>
                <input
                    type="text"
                    name="titulo"
                    value="{{ old('titulo') }}"
                    required
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('titulo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Tipo de documento</label>
                <select
                    name="tipo"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                    <option value="">Sin especificar</option>
                    <option value="contrato"      {{ old('tipo') === 'contrato' ? 'selected' : '' }}>Contrato</option>
                    <option value="oferta"        {{ old('tipo') === 'oferta' ? 'selected' : '' }}>Oferta / Presupuesto</option>
                    <option value="factura"       {{ old('tipo') === 'factura' ? 'selected' : '' }}>Factura</option>
                    <option value="presentacion"  {{ old('tipo') === 'presentacion' ? 'selected' : '' }}>Presentación</option>
                    <option value="otro"          {{ old('tipo') === 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
                @error('tipo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Fecha del documento</label>
                <input
                    type="date"
                    name="fecha_documento"
                    value="{{ old('fecha_documento') }}"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >
                @error('fecha_documento')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Descripción</label>
                <textarea
                    name="descripcion"
                    rows="3"
                    class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872] focus:outline-none"
                >{{ old('descripcion') }}</textarea>
                @error('descripcion')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Archivo *</label>
                <input
                    type="file"
                    name="archivo"
                    required
                    class="mt-1 block w-full text-sm text-slate-700"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Tamaño máximo 20 MB.
                </p>
                @error('archivo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="{{ url()->previous() }}" class="text-sm text-slate-600 hover:text-slate-900">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-medium text-white shadow hover:bg-[#7b1459]"
                >
                    Guardar documento
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
