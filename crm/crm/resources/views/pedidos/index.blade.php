@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
@php
    $q        = $filtros['q'] ?? null;
    $estado   = $filtros['estado'] ?? null;
    $anio     = $filtros['anio'] ?? null;
    $advanced = $filtros['af'] ?? null;
@endphp

<div
    x-data="{
        advancedOpen: false,
        fields: [
            { key: 'numero', label: 'Nº Pedido', type: 'string' },
            { key: 'descripcion', label: 'Descripción', type: 'string' },
            { key: 'cuenta', label: 'Cliente', type: 'string' },
            { key: 'razon_social', label: 'Razón social', type: 'string' },
            { key: 'provincia', label: 'Provincia', type: 'string' },
            { key: 'estado_pedido', label: 'Estado del pedido', type: 'string' },
            { key: 'estado_facturacion', label: 'Estado de facturación', type: 'string' },
            { key: 'dpto_comercial', label: 'Dpto. Comercial', type: 'string' },
            { key: 'dpto_consultor', label: 'Dpto. Consultor', type: 'string' },
            { key: 'subvencion', label: 'Subvención', type: 'string' },
            { key: 'tipo_proyecto', label: 'Tipo de proyecto', type: 'string' },
            { key: 'proyecto_externo', label: 'CBE', type: 'string' },
            { key: 'anio', label: 'Año', type: 'number' },
            { key: 'importe_total', label: 'Importe total', type: 'number' },
            { key: 'fecha_pedido', label: 'Fecha pedido', type: 'date' },
            { key: 'fecha_limite_memoria', label: 'Fecha inicio curso', type: 'date' },
            { key: 'fecha_limite_proyecto', label: 'Fecha fin curso', type: 'date' },
        ],
        stringOps: [
            { value: 'contains', label: 'contiene' },
            { value: 'not_contains', label: 'no contiene' },
            { value: 'equals', label: 'es igual a' },
            { value: 'not_equals', label: 'no es igual a' },
            { value: 'starts_with', label: 'empieza por' },
            { value: 'ends_with', label: 'termina en' },
            { value: 'is_empty', label: 'está vacío' },
            { value: 'is_not_empty', label: 'no está vacío' },
        ],
        dateOps: [
            { value: 'on', label: 'es en la fecha' },
            { value: 'before', label: 'es anterior a' },
            { value: 'after', label: 'es posterior a' },
            { value: 'between', label: 'entre' },
            { value: 'is_empty', label: 'está vacío' },
            { value: 'is_not_empty', label: 'no está vacío' },
        ],
        numberOps: [
            { value: 'equals', label: 'es igual a' },
            { value: 'not_equals', label: 'no es igual a' },
            { value: 'greater', label: 'es mayor que' },
            { value: 'greater_or_equal', label: 'es mayor o igual que' },
            { value: 'less', label: 'es menor que' },
            { value: 'less_or_equal', label: 'es menor o igual que' },
            { value: 'between', label: 'entre' },
            { value: 'is_empty', label: 'está vacío' },
            { value: 'is_not_empty', label: 'no está vacío' },
        ],
        advancedMatch: 'all',
        advancedRules: [],
        megaRaw: @js($advanced),
        fieldMeta(key) {
            return this.fields.find(f => f.key === key) || { type: 'string', label: key };
        },
        opsFor(fieldKey) {
            const type = this.fieldMeta(fieldKey).type;
            if (type === 'date') return this.dateOps;
            if (type === 'number') return this.numberOps;
            return this.stringOps;
        },
        usesSecondValue(rule) {
            return rule.operator === 'between';
        },
        hideValue(rule) {
            return rule.operator === 'is_empty' || rule.operator === 'is_not_empty';
        },
        onFieldChange(rule) {
            const ops = this.opsFor(rule.field);
            if (!ops.some(o => o.value === rule.operator)) {
                rule.operator = ops[0] ? ops[0].value : 'contains';
            }
            if (!this.usesSecondValue(rule)) {
                rule.value2 = '';
            }
        },
        addRule() {
            this.advancedRules.push({
                field: 'numero',
                operator: 'contains',
                value: '',
                value2: '',
            });
        },
        removeRule(i) {
            this.advancedRules.splice(i, 1);
            if (this.advancedRules.length === 0) {
                this.addRule();
            }
        },
        init() {
            if (this.megaRaw) {
                try {
                    const parsed = JSON.parse(this.megaRaw);
                    if (parsed && Array.isArray(parsed.rules)) {
                        this.advancedMatch = parsed.match === 'any' ? 'any' : 'all';
                        this.advancedRules = parsed.rules;
                    }
                } catch (e) {}
            }
            if (this.advancedRules.length === 0) {
                this.addRule();
            }
        },
    }"
    x-init="init()"
    class="space-y-6"
>
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Pedidos
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Pedidos confirmados derivados de peticiones ganadas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if($views->count() > 0)
                <form method="GET" action="{{ route('pedidos.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="estado" value="{{ $estado }}">
                    <input type="hidden" name="anio" value="{{ $anio }}">
                    <input type="hidden" name="af" value="{{ $advanced }}">
                    @foreach ($activeColumnKeys as $key)
                        <input type="hidden" name="columns[]" value="{{ $key }}">
                    @endforeach

                    <label class="text-xs text-slate-500">Vista</label>
                    <select
                        name="vista_id"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">Vista por defecto</option>
                        @foreach ($views as $view)
                            <option value="{{ $view->id }}" {{ optional($activeView)->id === $view->id ? 'selected' : '' }}>
                                {{ $view->name }}{{ $view->is_default ? ' (predeterminada)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            @auth
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                    x-data
                    @click="$dispatch('open-save-view')"
                >
                    Guardar vista
                </button>
            @endauth

            <a
                href="{{ route('pedidos.create') }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
            >
                + Nuevo pedido
            </a>
        </div>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
        {{-- Filtros --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('pedidos.index') }}" class="flex flex-1 flex-wrap items-center gap-3">
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
                        placeholder="Buscar por nº pedido, descripción, cuenta..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#9d1872] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>

                <select
                    name="estado"
                    class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Todos los estados</option>
                    <option value="pendiente"  {{ $estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmado" {{ $estado === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="finalizado" {{ $estado === 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                    <option value="borrador"   {{ $estado === 'borrador' ? 'selected' : '' }}>Borrador</option>
                </select>

                <input
                    type="number"
                    name="anio"
                    value="{{ $anio }}"
                    placeholder="Año"
                    class="w-24 rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >

                @foreach ($activeColumnKeys as $key)
                    <input type="hidden" name="columns[]" value="{{ $key }}">
                @endforeach
                <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                <input type="hidden" name="af" value="{{ $advanced }}">

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
                >
                    Aplicar filtros
                </button>
            </form>

            <div>
                <button
                    type="button"
                    @click="advancedOpen = !advancedOpen"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Filtro avanzado
                    <svg class="ml-1 h-3 w-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                    </svg>
                </button>

                <div
                    x-show="advancedOpen"
                    x-cloak
                    class="fixed inset-0 z-40 flex items-center justify-center px-4 py-6"
                >
                    <div class="absolute inset-0 bg-slate-900/30" @click="advancedOpen = false"></div>

                    <div class="relative z-50 w-full max-w-2xl rounded-2xl bg-white p-5 text-xs shadow-xl">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Filtro avanzado</h3>
                                <p class="mt-1 text-xs text-slate-500">Define condiciones avanzadas para filtrar pedidos.</p>
                            </div>
                            <button
                                type="button"
                                class="rounded-full p-2 text-slate-400 hover:bg-slate-100"
                                @click="advancedOpen = false"
                            >
                                ✕
                            </button>
                        </div>

                        <form method="GET" action="{{ route('pedidos.index') }}" class="space-y-3 pt-4">
                            <input type="hidden" name="q" value="{{ $q }}">
                            <input type="hidden" name="estado" value="{{ $estado }}">
                            <input type="hidden" name="anio" value="{{ $anio }}">
                            <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                            @foreach ($activeColumnKeys as $key)
                                <input type="hidden" name="columns[]" value="{{ $key }}">
                            @endforeach

                            <input
                                type="hidden"
                                name="af"
                                :value="JSON.stringify({ match: advancedMatch, rules: advancedRules })"
                            >

                            <div class="mb-2 flex items-center gap-2">
                                <span class="mr-1 text-[11px] text-slate-500">Mostrar pedidos que</span>
                                <button
                                    type="button"
                                    @click="advancedMatch = 'all'"
                                    class="rounded-full px-2 py-1 text-[11px]"
                                    :class="advancedMatch === 'all'
                                        ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold'
                                        : 'bg-slate-100 text-slate-600'"
                                >
                                    Coincidan con todas (Y)
                                </button>
                                <button
                                    type="button"
                                    @click="advancedMatch = 'any'"
                                    class="rounded-full px-2 py-1 text-[11px]"
                                    :class="advancedMatch === 'any'
                                        ? 'bg-[#9d1872]/10 text-[#9d1872] font-semibold'
                                        : 'bg-slate-100 text-slate-600'"
                                >
                                    Coincidan con alguna (O)
                                </button>
                            </div>

                            <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                                <template x-for="(rule, i) in advancedRules" :key="i">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <select
                                            class="w-40 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                            x-model="rule.field"
                                            @change="onFieldChange(rule)"
                                        >
                                            <template x-for="field in fields" :key="field.key">
                                                <option :value="field.key" x-text="field.label"></option>
                                            </template>
                                        </select>

                                        <select
                                            class="w-40 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                            x-model="rule.operator"
                                            @change="onFieldChange(rule)"
                                        >
                                            <template x-for="op in opsFor(rule.field)" :key="op.value">
                                                <option :value="op.value" x-text="op.label"></option>
                                            </template>
                                        </select>

                                        <template x-if="!hideValue(rule)">
                                            <input
                                                class="w-40 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                                :type="fieldMeta(rule.field).type === 'date'
                                                    ? 'date'
                                                    : (fieldMeta(rule.field).type === 'number' ? 'number' : 'text')"
                                                x-model="rule.value"
                                            >
                                        </template>

                                        <template x-if="usesSecondValue(rule)">
                                            <input
                                                class="w-32 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                                :type="fieldMeta(rule.field).type === 'date'
                                                    ? 'date'
                                                    : (fieldMeta(rule.field).type === 'number' ? 'number' : 'text')"
                                                x-model="rule.value2"
                                                placeholder="hasta"
                                            >
                                        </template>

                                        <button
                                            type="button"
                                            class="text-[11px] text-slate-400 hover:text-rose-500"
                                            @click="removeRule(i)"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <button
                                type="button"
                                class="mt-1 text-[11px] text-[#9d1872] hover:underline"
                                @click="addRule()"
                            >
                                + Añadir condición
                            </button>

                            <div class="mt-4 flex justify-end gap-2 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    class="text-[11px] text-slate-500 hover:underline"
                                    @click="advancedRules = []; addRule(); advancedMatch = 'all';"
                                >
                                    Limpiar
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-[#86145f]"
                                >
                                    Aplicar filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div x-data="{ open: false }" class="relative">
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Columnas
                    <svg class="ml-1 h-3 w-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    class="absolute right-0 z-20 mt-2 w-60 max-w-xs rounded-xl border border-slate-200 bg-white p-3 text-xs shadow-lg"
                >
                    <form method="GET" action="{{ route('pedidos.index') }}" class="space-y-2">
                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="estado" value="{{ $estado }}">
                        <input type="hidden" name="anio" value="{{ $anio }}">
                        <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                        <input type="hidden" name="af" value="{{ $advanced }}">

                        <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                            Columnas visibles
                        </p>

                        <div class="max-h-48 space-y-1 overflow-y-auto">
                            @foreach ($availableColumns as $key => $label)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="columns[]"
                                        value="{{ $key }}"
                                        class="h-3.5 w-3.5 text-[#9d1872]"
                                        {{ in_array($key, $activeColumnKeys, true) ? 'checked' : '' }}
                                    >
                                    <span class="text-xs text-slate-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <button
                            type="submit"
                            class="mt-2 w-full rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]"
                        >
                            Aplicar columnas
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        @foreach ($activeColumnKeys as $key)
                            <th class="px-3 py-2 text-left">{{ $availableColumns[$key] ?? $key }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($pedidos as $pedido)
                        <tr class="hover:bg-slate-50">
                            @foreach ($activeColumnKeys as $colKey)
                                <td class="px-3 py-2 text-sm text-slate-700">
                                    @switch($colKey)
                                        @case('numero')
                                            <a href="{{ route('pedidos.show', $pedido) }}" class="font-medium text-slate-900 hover:text-[#9d1872] hover:underline">
                                                {{ $pedido->numero ?: ('PED-'.$pedido->id) }}
                                            </a>
                                            @break
                                        @case('cliente')
                                            @if($pedido->cuenta)
                                                <a href="{{ route('accounts.show', $pedido->cuenta) }}" class="text-[#9d1872] hover:underline">
                                                    {{ $pedido->cuenta->name }}
                                                </a>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                            @break
                                        @case('razon_social')
                                            {{ $pedido->cuenta?->legal_name ?: '—' }}
                                            @break
                                        @case('provincia')
                                            {{ $pedido->cuenta?->provincia ?: '—' }}
                                            @break
                                        @case('descripcion')
                                            {{ $pedido->descripcion ?: '—' }}
                                            @break
                                        @case('dpto_comercial')
                                            {{ $pedido->dpto_comercial ?: '—' }}
                                            @break
                                        @case('dpto_consultor')
                                            {{ $pedido->dpto_consultor ?: '—' }}
                                            @break
                                        @case('subvencion')
                                            {{ $pedido->subvencion ?: '—' }}
                                            @break
                                        @case('cbe')
                                            {{ $pedido->proyecto_externo ?: '—' }}
                                            @break
                                        @case('estado_pedido')
                                            {{ $pedido->estado_pedido ? ucfirst($pedido->estado_pedido) : '—' }}
                                            @break
                                        @case('estado_facturacion')
                                            {{ $pedido->estado_facturacion ?: '—' }}
                                            @break
                                        @case('fecha_proxima_factura')
                                            {{ $pedido->plazos_pago_min_fecha_factura ? \Carbon\Carbon::parse($pedido->plazos_pago_min_fecha_factura)->format('d/m/Y') : '—' }}
                                            @break
                                        @case('importe_total')
                                            {{ $pedido->importe_total ? number_format($pedido->importe_total, 2, ',', '.') . ' ' . $pedido->moneda : '—' }}
                                            @break
                                        @case('pedido_formacion')
                                            {{ $pedido->es_formacion ? 'Sí' : 'No' }}
                                            @break
                                        @case('fecha_inicio_curso')
                                            {{ $pedido->fecha_limite_memoria?->format('d/m/Y') ?? '—' }}
                                            @break
                                        @case('fecha_fin_curso')
                                            {{ $pedido->fecha_limite_proyecto?->format('d/m/Y') ?? '—' }}
                                            @break
                                        @case('tipo_proyecto')
                                            {{ $pedido->tipo_proyecto ?: '—' }}
                                            @break
                                        @default
                                            —
                                    @endswitch
                                </td>
                            @endforeach
                            <td class="px-3 py-2 text-right text-xs">
                                <a href="{{ route('pedidos.show', $pedido) }}" class="text-[#9d1872] hover:underline">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($activeColumnKeys) + 1 }}" class="px-3 py-6 text-center text-xs text-slate-500">
                                No se han encontrado pedidos con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $pedidos->links() }}
        </div>
    </section>

    @auth
        <div
            x-data="{ open: false }"
            x-on:open-save-view.window="open = true"
        >
            <div
                x-show="open"
                x-cloak
                class="fixed inset-0 z-40 flex items-center justify-center"
            >
                <div class="absolute inset-0 bg-slate-900/30" @click="open = false"></div>

                <div class="relative z-50 w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
                    <h2 class="text-sm font-semibold text-slate-900">
                        Guardar vista de pedidos
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Guarda esta combinación de filtros y columnas como una vista reutilizable.
                    </p>

                    <form method="POST" action="{{ route('pedidos.views.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nombre de la vista</label>
                            <input
                                type="text"
                                name="name"
                                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                required
                            >
                        </div>

                        <label class="flex items-center gap-2 text-xs text-slate-600">
                            <input type="checkbox" name="is_default" value="1" class="h-3.5 w-3.5 text-[#9d1872]">
                            Hacer vista predeterminada
                        </label>

                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="estado" value="{{ $estado }}">
                        <input type="hidden" name="anio" value="{{ $anio }}">
                        <input type="hidden" name="af" value="{{ $advanced }}">

                        @foreach ($activeColumnKeys as $key)
                            <input type="hidden" name="columns[]" value="{{ $key }}">
                        @endforeach

                        <input type="hidden" name="sort_column" value="{{ $sortColumn }}">
                        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">

                        <div class="mt-3 flex justify-end gap-2">
                            <button type="button" class="text-xs text-slate-500 hover:underline" @click="open = false">
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]"
                            >
                                Guardar vista
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endauth
</div>
@endsection
