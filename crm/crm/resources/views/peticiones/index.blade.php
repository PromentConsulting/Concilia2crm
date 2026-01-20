@extends('layouts.app')

@section('title', 'Peticiones')

@section('content')
@php
    $q      = $filtros['q'] ?? null;
    $estado = $filtros['estado'] ?? null;
@endphp

<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Peticiones
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Propuestas generadas a partir de solicitudes ganadas.
            </p>
        </div>

        <a
            href="{{ route('peticiones.create') }}"
            class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
        >
            + Nueva petición
        </a>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
        {{-- Filtros --}}
        <form method="GET" action="{{ route('peticiones.index') }}" class="flex flex-col gap-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Buscar por título, cuenta, contacto..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#9d1872] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select
                        name="estado"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">Todos los estados</option>
                        <option value="borrador"   {{ $estado === 'borrador' ? 'selected' : '' }}>Borrador</option>
                        <option value="enviada"    {{ $estado === 'enviada' ? 'selected' : '' }}>Enviada</option>
                        <option value="aceptada"   {{ $estado === 'aceptada' ? 'selected' : '' }}>Aceptada</option>
                        <option value="rechazada"  {{ $estado === 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                        <option value="cancelada"  {{ $estado === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>

                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
                    >
                        Aplicar filtros
                    </button>
                </div>
            </div>
        </form>

        {{-- Tabla --}}
        <div class="overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Título</th>
                        <th class="px-3 py-2 text-left">Cuenta</th>
                        <th class="px-3 py-2 text-left">Contacto</th>
                        <th class="px-3 py-2 text-left">Estado</th>
                        <th class="px-3 py-2 text-left">Importe</th>
                        <th class="px-3 py-2 text-left">Solicitud</th>
                        <th class="px-3 py-2 text-left">Fecha</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($peticiones as $peticion)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 font-medium text-slate-900">
                                <a href="{{ route('peticiones.show', $peticion) }}" class="hover:text-[#9d1872] hover:underline">
                                    {{ $peticion->titulo }}
                                </a>
                            </td>
                            <td class="px-3 py-2 text-slate-600">
                                {{ optional($peticion->cuenta)->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-slate-600">
                                {{ optional($peticion->contacto)->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs">
                                {{ ucfirst($peticion->estado) }}
                            </td>
                            <td class="px-3 py-2 text-xs">
                                {{ $peticion->importe_total ? number_format($peticion->importe_total, 2, ',', '.') . ' ' . $peticion->moneda : '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs">
                                {{ optional($peticion->solicitud)->asunto ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-500">
                                {{ optional($peticion->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-2 text-right text-xs">
                                <a href="{{ route('peticiones.show', $peticion) }}" class="text-[#9d1872] hover:underline">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-xs text-slate-500">
                                No se han encontrado peticiones con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $peticiones->links() }}
        </div>
    </section>
</div>
@endsection
