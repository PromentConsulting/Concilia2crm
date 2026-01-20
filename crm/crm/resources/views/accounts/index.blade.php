@extends('layouts.app')

@section('title', 'Cuentas')

@section('content')
@php
    $q         = $filters['q'] ?? null;
    $lifecycle = $filters['lifecycle'] ?? null;
    $country   = $filters['country'] ?? null;

    // Mega filtro avanzado en JSON (viene por query ?af=...)
    $megaJson = request('af');
@endphp

<div
    x-data="{
        // -------- VISTA RÁPIDA --------
        quickOpen: false,
        quickAccount: null,
        quickLoading: false,
        quickError: null,
        async openQuickView(data) {
            this.quickAccount = data;
            this.quickOpen = true;

            // Cargar relaciones (solicitudes/peticiones/pedidos) al abrir la vista rápida
            this.quickLoading = true;
            this.quickError = null;

            try {
                if (data?.quick_url) {
                    const res = await fetch(data.quick_url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const payload = await res.json();
                    this.quickAccount = { ...this.quickAccount, related: payload };
                }
            } catch (e) {
                this.quickError = 'No se han podido cargar los datos relacionados.';
            } finally {
                this.quickLoading = false;
            }
        },
        closeQuickView() {
            this.quickOpen = false;
            this.quickAccount = null;
            this.quickLoading = false;
            this.quickError = null;
        },

        // -------- MEGA FILTRO --------
        advancedOpen: false,
        fields: [
            { key: 'name',           label: 'Nombre',        type: 'string' },
            { key: 'email',          label: 'Email',         type: 'string' },
            { key: 'phone',          label: 'Teléfono',      type: 'string' },
            { key: 'website',        label: 'Web',           type: 'string' },
            { key: 'tax_id',         label: 'CIF/NIF',       type: 'string' },
            { key: 'lifecycle',      label: 'Estado',        type: 'enum'   },
            { key: 'country',        label: 'País',          type: 'enum'   },
            { key: 'created_at',     label: 'Fecha creación',type: 'date'   },
            { key: 'contacts_count', label: 'Contactos',     type: 'number' },
        ],
        stringOps: [
            { value: 'contains',      label: 'contiene' },
            { value: 'not_contains',  label: 'no contiene' },
            { value: 'equals',        label: 'es igual a' },
            { value: 'not_equals',    label: 'no es igual a' },
            { value: 'starts_with',   label: 'empieza por' },
            { value: 'ends_with',     label: 'termina en' },
            { value: 'is_empty',      label: 'está vacío' },
            { value: 'is_not_empty',  label: 'no está vacío' },
        ],
        enumOps: [
            { value: 'equals',       label: 'es igual a' },
            { value: 'not_equals',   label: 'no es igual a' },
            { value: 'is_empty',     label: 'está vacío' },
            { value: 'is_not_empty', label: 'no está vacío' },
        ],
        dateOps: [
            { value: 'on',           label: 'es en la fecha' },
            { value: 'before',       label: 'es anterior a' },
            { value: 'after',        label: 'es posterior a' },
            { value: 'between',      label: 'entre' },
            { value: 'is_empty',     label: 'está vacío' },
            { value: 'is_not_empty', label: 'no está vacío' },
        ],
        numberOps: [
            { value: 'equals',         label: 'es igual a' },
            { value: 'not_equals',     label: 'no es igual a' },
            { value: 'greater',        label: 'es mayor que' },
            { value: 'greater_or_equal',label: 'es mayor o igual que' },
            { value: 'less',           label: 'es menor que' },
            { value: 'less_or_equal',  label: 'es menor o igual que' },
            { value: 'between',        label: 'entre' },
            { value: 'is_empty',       label: 'está vacío' },
            { value: 'is_not_empty',   label: 'no está vacío' },
        ],
        advancedMatch: 'all',
        advancedRules: [],
        megaRaw: @js($megaJson),

        fieldMeta(key) {
            return this.fields.find(f => f.key === key) || { type: 'string', label: key };
        },
        opsFor(fieldKey) {
            const type = this.fieldMeta(fieldKey).type;
            if (type === 'date')   return this.dateOps;
            if (type === 'number') return this.numberOps;
            if (type === 'enum')   return this.enumOps;
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
                field: 'name',
                operator: 'contains',
                value: '',
                value2: ''
            });
        },
        removeRule(i) {
            this.advancedRules.splice(i, 1);
            if (this.advancedRules.length === 0) {
                this.addRule();
            }
        },

        // -------- COLUMNAS DRAG & DROP (solo desktop) --------
        columns: @js($activeColumnKeys),
        params: {
            q: @js($q),
            lifecycle: @js($lifecycle),
            country: @js($country),
            vista_id: @js(optional($activeView)->id),
            af: @js($megaJson),
        },
        dragSource: null,
        dragOver: null,

        // -------- ACCIONES MASIVAS --------
        selectedIds: [],
        selectAll: false,
        selectAllAcrossPages: false,
        pageAccountIds: @js($accounts->pluck('id')->values()),
        totalCount: {{ $accounts->total() }},
        bulkAction: '',
        selectedUserId: '',
        selectedTeamId: '',
        exportFormat: 'csv',
        filtersForBulk: {
            q: @js($q),
            lifecycle: @js($lifecycle),
            country: @js($country),
        },
        startDrag(key) {
            this.dragSource = key;
            this.dragOver = null;
        },
        dragEnter(key) {
            if (!this.dragSource || this.dragSource === key) return;
            this.dragOver = key;
        },
        dragLeave(key) {
            if (this.dragOver === key) {
                this.dragOver = null;
            }
        },
        dropOn(targetKey) {
            const from = this.columns.indexOf(this.dragSource);
            const to   = this.columns.indexOf(targetKey);
            if (from === -1 || to === -1 || from === to) return;

            const updated = [...this.columns];
            updated.splice(to, 0, updated.splice(from, 1)[0]);

            const form = document.createElement('form');
            form.method = 'GET';
            form.action = '{{ route('accounts.index') }}';

            for (const [k, v] of Object.entries(this.params)) {
                if (v === null || v === undefined || v === '') continue;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = k;
                input.value = v;
                form.appendChild(input);
            }

            for (const col of updated) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'columns[]';
                input.value = col;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            this.dragOver = null;
            form.submit();
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

        toggleSelect(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter((value) => value !== id);
            } else {
                this.selectedIds = [...this.selectedIds, id];
            }

            this.selectAllAcrossPages = false;
            this.syncSelectAll();
        },

        toggleSelectAll(checked) {
            this.selectAll = checked;
            this.selectAllAcrossPages = false;

            if (checked) {
                this.selectedIds = Array.from(new Set([...this.selectedIds, ...this.pageAccountIds]));
            } else {
                this.selectedIds = this.selectedIds.filter((id) => !this.pageAccountIds.includes(id));
            }
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        syncSelectAll() {
            this.selectAll = this.pageAccountIds.length > 0 && this.pageAccountIds.every((id) => this.selectedIds.includes(id));
        },

        selectAllResults() {
            if (this.selectedIds.length === 0) {
                this.selectedIds = [...this.pageAccountIds];
            }

            this.selectAllAcrossPages = true;
        },
        submitBulk(action) {
            if (this.selectedIds.length === 0) {
                alert('Selecciona al menos una cuenta.');
                return;
            }

            this.bulkAction = action;
            this.$nextTick(() => this.$refs.bulkForm.submit());
        },

        submitOwner(action) {
            if (action === 'assign_owner_user' && !this.selectedUserId) {
                alert('Selecciona un usuario destino.');
                return;
            }

            if (action === 'assign_owner_team' && !this.selectedTeamId) {
                alert('Selecciona un equipo destino.');
                return;
            }

            this.submitBulk(action);
        },

        submitExport(format) {
            if (this.selectedIds.length === 0) {
                alert('Selecciona al menos una cuenta.');
                return;
            }

            this.exportFormat = format;
            this.$nextTick(() => this.$refs.exportForm.submit());
        }
    }"
    x-init="init()"
    class="space-y-6"
>
    {{-- CABECERA --}}
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Cuentas</h1>
            <p class="mt-1 text-sm text-slate-500">
                Gestiona las empresas, contactos y actividad comercial.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Selector de vistas --}}
            @if($views->count() > 0)
                <form method="GET" action="{{ route('accounts.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="lifecycle" value="{{ $lifecycle }}">
                    <input type="hidden" name="country" value="{{ $country }}">
                    <input type="hidden" name="af" value="{{ $megaJson }}">

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

            {{-- Botón guardar vista --}}
            @auth
                <button
                    type="button"
                    class="hidden sm:inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    @click="$dispatch('open-save-view')"
                >
                    Guardar vista
                </button>
            @endauth

            <a
                href="{{ route('accounts.import.create') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                Importar
            </a>

            <a
                href="{{ route('accounts.create') }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
            >
                + Nueva cuenta
            </a>
        </div>
    </header>

    {{-- FILTROS + CONFIGURAR COLUMNAS --}}
    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Filtros rápidos --}}
            <form method="GET" action="{{ route('accounts.index') }}" class="flex flex-1 flex-wrap items-center gap-3">
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
                        placeholder="Buscar por nombre, email, teléfono, CIF..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#9d1872] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>

                    <select
                        name="lifecycle"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">Todos los tipos</option>
                        <option value="prospect" {{ $lifecycle === 'prospect' ? 'selected' : '' }}>Cliente potencial</option>
                        <option value="customer" {{ $lifecycle === 'customer' ? 'selected' : '' }}>Cliente</option>
                    </select>

               <!-- <select
                    name="country"
                    class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Todos los países</option>
                    @foreach ($countries as $c)
                        <option value="{{ $c }}" {{ $country === $c ? 'selected' : '' }}>
                            {{ $c }}
                        </option>
                    @endforeach
                </select>-->

                {{-- Mega filtro actual --}}
                <input type="hidden" name="af" value="{{ $megaJson }}">

                {{-- columnas actuales --}}
                @foreach ($activeColumnKeys as $key)
                    <input type="hidden" name="columns[]" value="{{ $key }}">
                @endforeach

                <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">

                <!--<button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
                >
                    Aplicar filtros
                </button>-->
            </form>

            {{-- MEGA FILTRO (popup) --}}
            <div class="relative">
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
                    @click.outside="advancedOpen = false"
                    class="absolute right-0 z-30 mt-2 w-full max-w-sm sm:w-[420px] rounded-xl border border-slate-200 bg-white p-4 text-xs shadow-xl"
                >
                    <h3 class="text-xs font-semibold text-slate-800 mb-2">Filtro avanzado</h3>

                    <form method="GET" action="{{ route('accounts.index') }}" class="space-y-3">
                        {{-- básicos --}}
                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="lifecycle" value="{{ $lifecycle }}">
                        <input type="hidden" name="country" value="{{ $country }}">
                        <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                        {{-- columnas actuales --}}
                        @foreach ($activeColumnKeys as $key)
                            <input type="hidden" name="columns[]" value="{{ $key }}">
                        @endforeach

                        {{-- mega filtro en JSON --}}
                        <input
                            type="hidden"
                            name="af"
                            :value="JSON.stringify({ match: advancedMatch, rules: advancedRules })"
                        >

                        {{-- selector ALL / ANY --}}
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[11px] text-slate-500 mr-1">Mostrar cuentas que</span>
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

                        {{-- reglas --}}
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            <template x-for="(rule, i) in advancedRules" :key="i">
                                <div class="flex items-center gap-2">
                                    {{-- campo --}}
                                    <select
                                        class="w-32 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                        x-model="rule.field"
                                        @change="onFieldChange(rule)"
                                    >
                                        <template x-for="field in fields" :key="field.key">
                                            <option :value="field.key" x-text="field.label"></option>
                                        </template>
                                    </select>

                                    {{-- operador --}}
                                    <select
                                        class="w-36 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                        x-model="rule.operator"
                                        @change="onFieldChange(rule)"
                                    >
                                        <template x-for="op in opsFor(rule.field)" :key="op.value">
                                            <option :value="op.value" x-text="op.label"></option>
                                        </template>
                                    </select>

                                    {{-- valor 1 --}}
                                    <template x-if="!hideValue(rule)">
                                        <input
                                            class="w-32 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                            :type="fieldMeta(rule.field).type === 'date'
                                                ? 'date'
                                                : (fieldMeta(rule.field).type === 'number' ? 'number' : 'text')"
                                            x-model="rule.value"
                                        >
                                    </template>

                                    {{-- valor 2 (between) --}}
                                    <template x-if="usesSecondValue(rule)">
                                        <input
                                            class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
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

                        <div class="mt-3 flex justify-end gap-2">
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
                                Aplicar filtro
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Configurar columnas (visibles) --}}
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
                    class="absolute right-0 z-20 mt-2 w-56 max-w-xs rounded-xl border border-slate-200 bg-white p-3 text-xs shadow-lg"
                >
                    <form method="GET" action="{{ route('accounts.index') }}" class="space-y-2">
                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="lifecycle" value="{{ $lifecycle }}">
                        <input type="hidden" name="country" value="{{ $country }}">
                        <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                        <input type="hidden" name="af" value="{{ $megaJson }}">

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

       {{-- ACCIONES MASIVAS --}}
        <div
            x-show="selectedIds.length > 0"
            x-cloak
            class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm"
        >
            <div class="text-sm font-semibold text-slate-800">
                <template x-if="selectAllAcrossPages">
                    <span>
                        Todas las <span x-text="totalCount"></span> cuentas de la búsqueda están seleccionadas
                    </span>
                </template>
                <template x-if="!selectAllAcrossPages">
                    <span>
                        <span x-text="selectedIds.length"></span> cuentas seleccionadas
                    </span>
                </template>
            </div>
            <template x-if="!selectAllAcrossPages && totalCount > selectedIds.length">
                <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
                    <span>Has seleccionado las cuentas de esta página.</span>
                    <button
                        type="button"
                        class="font-semibold text-[#9d1872] hover:underline"
                        @click="selectAllResults()"
                    >
                        Seleccionar las <span x-text="totalCount"></span> cuentas
                    </button>
                </div>
            </template>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitBulk('activate')"
                >
                    Activar
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitBulk('deactivate')"
                >
                    Desactivar
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitBulk('set_payment_issue')"
                >
                    Marcar con problemas en cobro
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitBulk('clear_payment_issue')"
                >
                    Quitar problemas en cobro
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <label class="text-[11px] text-slate-500">Propietario (usuario)</label>
                <select
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    x-model="selectedUserId"
                >
                    <option value="">Selecciona usuario</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitOwner('assign_owner_user')"
                >
                    Reasignar usuario
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <label class="text-[11px] text-slate-500">Propietario (equipo)</label>
                <select
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    x-model="selectedTeamId"
                >
                    <option value="">Selecciona equipo</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitOwner('assign_owner_team')"
                >
                    Reasignar equipo
                </button>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[11px] text-slate-500">Exportar selección:</span>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitExport('csv')"
                >
                    CSV
                </button>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitExport('xlsx')"
                >
                    Excel
                </button>
            </div>

            {{-- Formularios ocultos para enviar las acciones --}}
            <form x-ref="bulkForm" method="POST" action="{{ route('accounts.bulk') }}" class="hidden">
                @csrf
                <input type="hidden" name="action" x-model="bulkAction">
                <input type="hidden" name="owner_user_id" :value="selectedUserId">
                <input type="hidden" name="owner_team_id" :value="selectedTeamId">
                <input type="hidden" name="select_all" :value="selectAllAcrossPages ? 1 : 0">
                <input type="hidden" name="q" :value="filtersForBulk.q ?? ''">
                <input type="hidden" name="lifecycle" :value="filtersForBulk.lifecycle ?? ''">
                <input type="hidden" name="country" :value="filtersForBulk.country ?? ''">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>

            <form x-ref="exportForm" method="GET" action="{{ route('accounts.export') }}" class="hidden">
                <input type="hidden" name="format" x-model="exportFormat">
                <input type="hidden" name="select_all" :value="selectAllAcrossPages ? 1 : 0">
                <input type="hidden" name="q" :value="filtersForBulk.q ?? ''">
                <input type="hidden" name="lifecycle" :value="filtersForBulk.lifecycle ?? ''">
                <input type="hidden" name="country" :value="filtersForBulk.country ?? ''">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
        </div>

        {{-- LISTA RESPONSIVE (MÓVIL / TABLET PEQUEÑA) --}}
        <div class="space-y-2 md:hidden">
            @forelse($accounts as $account)
                <div class="rounded-xl border border-slate-100 bg-white px-3 py-3 shadow-sm">
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                            :checked="isSelected({{ $account->id }})"
                            @click.stop="toggleSelect({{ $account->id }})"
                        >

                        <a
                            href="{{ route('accounts.show', $account) }}"
                            class="flex flex-1 items-center justify-between gap-2"
                        >
                            <div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $account->name }}
                                </div>
                                @if($account->email)
                                    <div class="text-xs text-slate-500">
                                        {{ $account->email }}
                                    </div>
                                @endif
                                @if($account->phone)
                                    <div class="text-xs text-slate-500">
                                        {{ $account->phone }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right text-[11px] text-slate-500 space-y-1">
                                @if($account->billing_country ?? $account->country)
                                    <div>{{ $account->billing_country ?? $account->country }}</div>
                                @endif
                                @if($account->lifecycle)
                                    <div class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700">
                                        {{ $account->lifecycle === 'customer' ? 'Cliente' : 'Cliente potencial' }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <p class="px-3 py-4 text-center text-xs text-slate-500">
                    No se han encontrado cuentas con los filtros aplicados.
                </p>
            @endforelse
        </div>

        {{-- TABLA (SOLO DESKTOP) --}}
        <div class="hidden md:block">
            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                    :checked="selectAll"
                                    @change="toggleSelectAll($event.target.checked)"
                                >
                            </th>
                            @foreach ($activeColumnKeys as $colKey)
                                <th
                                    class="px-3 py-2 text-left select-none relative cursor-move whitespace-nowrap"
                                    draggable="true"
                                    @dragstart="startDrag('{{ $colKey }}')"
                                    @dragenter.prevent="dragEnter('{{ $colKey }}')"
                                    @dragover.prevent
                                    @dragleave="dragLeave('{{ $colKey }}')"
                                    @drop="dropOn('{{ $colKey }}')"
                                    :class="dragOver === '{{ $colKey }}' && dragSource !== '{{ $colKey }}'
                                        ? 'bg-[#fdf2f8] border-x-2 border-[#9d1872]'
                                        : ''"
                                >
                                    <span
                                        x-show="dragOver === '{{ $colKey }}' && dragSource !== '{{ $colKey }}'"
                                        class="pointer-events-none absolute inset-y-1 left-0 w-[3px] rounded-full bg-[#9d1872]"
                                    ></span>

                                    {{ $availableColumns[$colKey] ?? $colKey }}
                                </th>
                            @endforeach
                            <th class="px-3 py-2 text-right text-xs whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @forelse ($accounts as $account)
                            <tr
                                class="group cursor-pointer hover:bg-slate-50"
                                @click="window.location = '{{ route('accounts.show', $account) }}'"
                            >
                                <td class="px-3 py-2">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                        :checked="isSelected({{ $account->id }})"
                                        @click.stop="toggleSelect({{ $account->id }})"
                                    >
                                </td>
                                @foreach ($activeColumnKeys as $colKey)
                                    <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                        @switch($colKey)
                                            @case('name')
                                                <div class="font-semibold text-slate-900">
                                                    {{ $account->name }}
                                                </div>
                                                @break

                                            @case('email')
                                                {{ $account->email ?: '—' }}
                                                @break

                                            @case('phone')
                                                {{ $account->phone ?: '—' }}
                                                @break

                                            @case('website')
                                                @if ($account->website)
                                                    <a href="{{ str_starts_with($account->website, 'http') ? $account->website : 'https://' . $account->website }}"
                                                    target="_blank"
                                                    class="text-[#9d1872] hover:underline">
                                                        {{ $account->website }}
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                                @break

                                            @case('country')
                                                {{ $account->billing_country ?? $account->country ?? '—' }}
                                                @break

                                            @case('lifecycle')
                                                @if ($account->lifecycle)
                                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                                        {{ $account->lifecycle === 'customer' ? 'Cliente' : 'Cliente potencial' }}
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                                @break

                                            @case('contacts_count')
                                                {{ $account->contacts_count ?? 0 }}
                                                @break

                                            @case('tax_id')
                                                {{ $account->tax_id ?: '—' }}
                                                @break
                                            
                                            @case('customer_code')
                                                {{ $account->customer_code ?: '—' }}
                                                @break

                                            @case('is_billable')
                                                @if($account->is_billable)
                                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                                        Sí
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                                        No
                                                    </span>
                                                @endif
                                                @break

                                            @case('billing_has_payment_issues')
                                                @if($account->billing_has_payment_issues)
                                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-700">
                                                        Sí
                                                    </span>
                                                @else
                                                    <span>—</span>
                                                @endif
                                                @break


                                            @case('created_at')
                                                {{ optional($account->created_at)->format('d/m/Y') ?: '—' }}
                                                @break

                                            @default
                                                —
                                        @endswitch
                                    </td>
                                @endforeach

                                <td class="px-3 py-2 text-right text-xs whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="hidden rounded-lg border border-slate-200 px-2 py-1 text-[11px] font-medium text-slate-700 hover:bg-slate-50 group-hover:inline-flex"
                                        @click.stop="openQuickView({
                                            id: {{ $account->id }},
                                            name: @js($account->name),
                                            email: @js($account->email),
                                            phone: @js($account->phone),
                                            website: @js($account->website),
                                            country: @js($account->billing_country ?? $account->country),
                                            lifecycle: @js($account->lifecycle),
                                            tax_id: @js($account->tax_id),
                                            contacts_count: {{ (int) ($account->contacts_count ?? 0) }},
                                            show_url: @js(route('accounts.show', $account)),
                                            edit_url: @js(route('accounts.edit', $account)),
                                            quick_url: @js(route('accounts.quick', $account)),
                                        })"
                                    >
                                        Vista rápida
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeColumnKeys) + 2 }}" class="px-3 py-6 text-center text-xs text-slate-500">
                                    No se han encontrado cuentas con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-2">
            {{ $accounts->links() }}
        </div>
    </section>

    {{-- MODAL GUARDAR VISTA --}}
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
                        Guardar vista de cuentas
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Guarda esta combinación de filtros y columnas como una vista reutilizable.
                    </p>

                    <form method="POST" action="{{ route('accounts.views.store') }}" class="mt-4 space-y-4">
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

                        {{-- filtros actuales --}}
                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="lifecycle" value="{{ $lifecycle }}">
                        <input type="hidden" name="country" value="{{ $country }}">
                        {{-- <input type="hidden" name="af" value="{{ $megaJson }}"> --}}

                        {{-- columnas actuales --}}
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

    {{-- VISTA RÁPIDA: SLIDE-OVER DERECHA --}}
    <div
        x-show="quickOpen"
        x-cloak
        class="fixed inset-0 z-40 flex justify-end"
        @keydown.escape.window="closeQuickView()"
    >
        <div class="absolute inset-0 bg-slate-900/30" @click="closeQuickView()"></div>

        <div
            class="relative z-50 flex h-full w-full max-w-md flex-col bg-white shadow-xl"
            x-transition:enter="transform transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-900" x-text="quickAccount?.name ?? 'Cuenta'"></h2>
                <button
                    type="button"
                    class="rounded-full p-1 text-slate-500 hover:bg-slate-100"
                    @click="closeQuickView()"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-4 text-sm">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Email</dt>
                        <dd class="text-slate-800" x-text="quickAccount?.email || '—'"></dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                        <dd class="text-slate-800" x-text="quickAccount?.phone || '—'"></dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">Web</dt>
                        <dd class="text-slate-800">
                            <template x-if="quickAccount?.website">
                                <a :href="quickAccount.website.startsWith('http') ? quickAccount.website : 'https://' + quickAccount.website"
                                   class="text-[#9d1872] hover:underline" target="_blank"
                                   x-text="quickAccount.website"></a>
                            </template>
                            <template x-if="!quickAccount?.website">
                                <span>—</span>
                            </template>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">País</dt>
                        <dd class="text-slate-800" x-text="quickAccount?.country || '—'"></dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">Estado</dt>
                        <dd class="text-slate-800">
                            <span
                                x-show="quickAccount?.lifecycle"
                                class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700"
                                x-text="quickAccount.lifecycle === 'customer' ? 'Cliente' : 'Cliente potencial'"
                            ></span>
                            <span x-show="!quickAccount?.lifecycle">—</span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">CIF/NIF</dt>
                        <dd class="text-slate-800" x-text="quickAccount?.tax_id || '—'"></dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-slate-500">Contactos</dt>
                        <dd class="text-slate-800" x-text="quickAccount?.contacts_count ?? 0"></dd>
                    </div>
                </dl>

                {{-- Actividad relacionada (cuando la cuenta está enlazada a solicitudes/peticiones/pedidos) --}}
                <div class="mt-6 border-t border-slate-200 pt-4">
                    <h3 class="text-xs font-semibold text-slate-700">Actividad relacionada</h3>

                    <template x-if="quickLoading">
                        <p class="mt-2 text-xs text-slate-500">Cargando…</p>
                    </template>

                    <template x-if="quickError">
                        <p class="mt-2 text-xs text-rose-600" x-text="quickError"></p>
                    </template>

                    <template x-if="!quickLoading && !quickError">
                        <div class="mt-3 space-y-4">
                            {{-- Solicitudes --}}
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-medium text-slate-700">
                                        Solicitudes
                                        <span class="text-slate-400" x-text="`(${quickAccount?.related?.solicitudes?.total ?? 0})`"></span>
                                    </p>
                                </div>

                                <template x-if="(quickAccount?.related?.solicitudes?.total ?? 0) === 0">
                                    <p class="mt-1 text-xs text-slate-500">Sin solicitudes vinculadas.</p>
                                </template>

                                <ul class="mt-2 space-y-1" x-show="(quickAccount?.related?.solicitudes?.total ?? 0) > 0">
                                    <template x-for="item in (quickAccount?.related?.solicitudes?.items ?? [])" :key="`sol-${item.id}`">
                                        <li class="flex items-start justify-between gap-3">
                                            <a class="text-xs text-[#9d1872] hover:underline truncate" :href="item.url" x-text="item.titulo ? item.titulo : `Solicitud #${item.id}`"></a>
                                            <span class="shrink-0 text-[11px] text-slate-500" x-text="item.estado"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Peticiones --}}
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-medium text-slate-700">
                                        Peticiones
                                        <span class="text-slate-400" x-text="`(${quickAccount?.related?.peticiones?.total ?? 0})`"></span>
                                    </p>
                                </div>

                                <template x-if="(quickAccount?.related?.peticiones?.total ?? 0) === 0">
                                    <p class="mt-1 text-xs text-slate-500">Sin peticiones vinculadas.</p>
                                </template>

                                <ul class="mt-2 space-y-1" x-show="(quickAccount?.related?.peticiones?.total ?? 0) > 0">
                                    <template x-for="item in (quickAccount?.related?.peticiones?.items ?? [])" :key="`pet-${item.id}`">
                                        <li class="flex items-start justify-between gap-3">
                                            <a class="text-xs text-[#9d1872] hover:underline truncate" :href="item.url" x-text="item.titulo ? item.titulo : `Petición #${item.id}`"></a>
                                            <span class="shrink-0 text-[11px] text-slate-500" x-text="item.estado"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            {{-- Pedidos --}}
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-medium text-slate-700">
                                        Pedidos
                                        <span class="text-slate-400" x-text="`(${quickAccount?.related?.pedidos?.total ?? 0})`"></span>
                                    </p>
                                </div>

                                <template x-if="(quickAccount?.related?.pedidos?.total ?? 0) === 0">
                                    <p class="mt-1 text-xs text-slate-500">Sin pedidos vinculados.</p>
                                </template>

                                <ul class="mt-2 space-y-1" x-show="(quickAccount?.related?.pedidos?.total ?? 0) > 0">
                                    <template x-for="item in (quickAccount?.related?.pedidos?.items ?? [])" :key="`ord-${item.id}`">
                                        <li class="flex items-start justify-between gap-3">
                                            <a class="text-xs text-[#9d1872] hover:underline truncate" :href="item.url" x-text="item.numero ? `Pedido ${item.numero}` : `Pedido #${item.id}`"></a>
                                            <span class="shrink-0 text-[11px] text-slate-500" x-text="item.estado"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>

            </div>

            <div class="border-t border-slate-200 px-4 py-3 flex items-center justify-between">
                <a
                    href="#"
                    class="text-xs text-slate-600 hover:text-[#9d1872] hover:underline"
                    x-show="quickAccount?.edit_url"
                    :href="quickAccount?.edit_url"
                >
                    Editar cuenta
                </a>

                <a
                    href="#"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]"
                    x-show="quickAccount?.show_url"
                    :href="quickAccount?.show_url"
                >
                    Ver ficha completa
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
