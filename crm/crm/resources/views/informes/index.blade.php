@extends('layouts.app')

@section('title', 'Informes comerciales')

@section('content')
@php
    $from     = $filters['from'] ?? null;
    $to       = $filters['to'] ?? null;
    $ownerId  = $filters['owner_id'] ?? null;
    $origen   = $filters['origen'] ?? null;

    function num_format($value) {
        return number_format((float) $value, 2, ',', '.');
    }
@endphp

<div class="space-y-6">
    {{-- CABECERA --}}
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Informes y reporting
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Seguimiento de leads, oportunidades, ventas y actividad comercial.
            </p>
        </div>
    </header>

    {{-- FILTROS --}}
    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('informes.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Desde</label>
                <input
                    type="date"
                    name="from"
                    value="{{ $from }}"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Hasta</label>
                <input
                    type="date"
                    name="to"
                    value="{{ $to }}"
                    class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Usuario responsable (leads / tareas)</label>
                <select
                    name="owner_id"
                    class="min-w-[180px] rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($ownerId == $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Canal / origen (leads)</label>
                <select
                    name="origen"
                    class="min-w-[160px] rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Todos</option>
                    @foreach($origins as $origin)
                        <option value="{{ $origin }}" @selected($origen === $origin)>
                            {{ ucfirst(str_replace('_',' ', $origin)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ml-auto flex gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
                >
                    Aplicar filtros
                </button>

                <a
                    href="{{ route('informes.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Limpiar
                </a>
            </div>
        </form>
    </section>

    {{-- KPIs PRINCIPALES --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <p class="text-xs uppercase tracking-wide text-slate-500">Leads / solicitudes</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">
                {{ $kpis['total_solicitudes'] }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Total de solicitudes recibidas en el periodo.
            </p>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <p class="text-xs uppercase tracking-wide text-slate-500">Oportunidades / peticiones</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">
                {{ $kpis['total_peticiones'] }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Importe presupuestado:
                <span class="font-medium text-slate-700">
                    {{ $kpis['importe_peticiones'] ? num_format($kpis['importe_peticiones']) . ' €' : '—' }}
                </span>
            </p>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <p class="text-xs uppercase tracking-wide text-slate-500">Pedidos / ventas</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">
                {{ $kpis['total_pedidos'] }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Importe total de pedidos:
                <span class="font-medium text-slate-700">
                    {{ $kpis['importe_pedidos'] ? num_format($kpis['importe_pedidos']) . ' €' : '—' }}
                </span>
            </p>
        </div>

        <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <p class="text-xs uppercase tracking-wide text-slate-500">Tareas comerciales</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">
                {{ $tasksStats['completadas'] }} / {{ $tasksStats['total'] }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                Cumplimiento:
                <span class="font-medium text-slate-700">
                    {{ $tasksStats['completion_rate'] !== null ? $tasksStats['completion_rate'] . '%' : '—' }}
                </span>
            </p>
        </div>
    </section>

    {{-- PIPELINE + LEADS POR CANAL --}}
    <section class="grid gap-4 lg:grid-cols-2">
        {{-- PIPELINE --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">
                Pipeline comercial
            </h2>

            <table class="min-w-full text-sm divide-y divide-slate-100">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-3 py-2 text-left">Fase</th>
                        <th class="px-3 py-2 text-right">Registros</th>
                        <th class="px-3 py-2 text-right">Conversión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr>
                        <td class="px-3 py-2">Leads / solicitudes</td>
                        <td class="px-3 py-2 text-right">{{ $pipeline['leads'] }}</td>
                        <td class="px-3 py-2 text-right text-slate-400">—</td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Oportunidades / peticiones</td>
                        <td class="px-3 py-2 text-right">{{ $pipeline['peticiones'] }}</td>
                        <td class="px-3 py-2 text-right">
                            {{ $pipeline['rate_leads_to_peticiones'] !== null ? $pipeline['rate_leads_to_peticiones'] . '%' : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Pedidos / ventas</td>
                        <td class="px-3 py-2 text-right">{{ $pipeline['pedidos'] }}</td>
                        <td class="px-3 py-2 text-right">
                            {{ $pipeline['rate_peticiones_to_pedidos'] !== null ? $pipeline['rate_peticiones_to_pedidos'] . '%' : '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="mt-3 text-xs text-slate-500">
                Conversión global leads → pedidos:
                <span class="font-medium text-slate-700">
                    {{ $pipeline['rate_leads_to_pedidos'] !== null ? $pipeline['rate_leads_to_pedidos'] . '%' : '—' }}
                </span>
            </p>
        </div>

        {{-- LEADS POR CANAL --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">
                Leads por canal / origen
            </h2>

            @if($channelStats->isEmpty())
                <p class="text-sm text-slate-500">
                    No hay leads en el periodo seleccionado.
                </p>
            @else
                <table class="min-w-full text-sm divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left">Canal</th>
                            <th class="px-3 py-2 text-right">Leads</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($channelStats as $row)
                            <tr>
                                <td class="px-3 py-2">
                                    {{ ucfirst(str_replace('_',' ', $row->origen ?? 'Sin origen')) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ $row->total }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    {{-- INDICADORES POR COMERCIAL + TAREAS --}}
    <section class="grid gap-4 lg:grid-cols-2">
        {{-- POR COMERCIAL --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">
                Indicadores por comercial (leads)
            </h2>

            @if(empty($userStats))
                <p class="text-sm text-slate-500">
                    No hay datos de leads con responsable asignado en el periodo.
                </p>
            @else
                <table class="min-w-full text-sm divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left">Usuario</th>
                            <th class="px-3 py-2 text-right">Leads</th>
                            <th class="px-3 py-2 text-right">Leads cerrados*</th>
                            <th class="px-3 py-2 text-right">Ratio cierre</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($userStats as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['user_name'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['leads'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['cerradas'] }}</td>
                                <td class="px-3 py-2 text-right">
                                    {{ $row['ratio'] !== null ? $row['ratio'] . '%' : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="mt-3 text-[11px] text-slate-400">
                    * Se consideran cerradas las solicitudes con estado distinto de "abierta".
                </p>
            @endif
        </div>

        {{-- SEGUIMIENTO DE TAREAS --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">
                Planificación y seguimiento de tareas
            </h2>

            <ul class="space-y-2 text-sm text-slate-700">
                <li>
                    <span class="font-medium">Tareas totales:</span>
                    {{ $tasksStats['total'] }}
                </li>
                <li>
                    <span class="font-medium">Tareas completadas:</span>
                    {{ $tasksStats['completadas'] }}
                </li>
                <li>
                    <span class="font-medium">Tareas pendientes:</span>
                    {{ $tasksStats['pendientes'] }}
                </li>
                <li>
                    <span class="font-medium">Porcentaje de cumplimiento:</span>
                    {{ $tasksStats['completion_rate'] !== null ? $tasksStats['completion_rate'] . '%' : '—' }}
                </li>
            </ul>

            <p class="mt-3 text-xs text-slate-500">
                Estos indicadores utilizan las tareas creadas en el CRM (llamadas, reuniones, tareas, etc.) en el periodo filtrado.
            </p>
        </div>
    </section>

    {{-- EVOLUCIÓN MENSUAL --}}
    <section class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">
            Evolución mensual (leads, oportunidades y pedidos)
        </h2>

        @if(empty($months))
            <p class="text-sm text-slate-500">
                No hay datos en el periodo seleccionado.
            </p>
        @else
            <div class="overflow-x-auto -mx-3 sm:mx-0">
                <table class="min-w-[480px] sm:min-w-full text-sm divide-y divide-slate-100">
                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2 text-left">Mes</th>
                            <th class="px-3 py-2 text-right">Leads</th>
                            <th class="px-3 py-2 text-right">Peticiones</th>
                            <th class="px-3 py-2 text-right">Pedidos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($months as $row)
                            <tr>
                                <td class="px-3 py-2">{{ $row['label'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['leads'] ?? 0 }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['peticiones'] ?? 0 }}</td>
                                <td class="px-3 py-2 text-right">{{ $row['pedidos'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Los datos mensuales permiten comparar la evolución de actividad comercial por ejercicio y periodo.
            </p>
        @endif
    </section>
</div>
@endsection
