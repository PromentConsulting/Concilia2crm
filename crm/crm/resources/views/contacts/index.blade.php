@extends('layouts.app')

@section('title', 'Contactos')

@section('content')
@php
    $q            = $filters['q'] ?? null;
    $accountId    = $filters['account_id'] ?? null;
    $advancedRaw  = $filters['af'] ?? null;
@endphp

<div
    x-data="{
        advancedOpen: false,
        fields: [
            { key: 'name', label: 'Nombre', type: 'string' },
            { key: 'email', label: 'Email', type: 'string' },
            { key: 'phone', label: 'Teléfono', type: 'string' },
            { key: 'role', label: 'Cargo', type: 'string' },
            { key: 'account', label: 'Cuenta', type: 'string' },
            { key: 'created_at', label: 'Creado el', type: 'date' },
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
        advancedMatch: 'all',
        advancedRules: [],
        megaRaw: @js($advancedRaw),

        selectedIds: [],
        selectAll: false,
        selectAllAcrossPages: false,
        pageContactIds: @js($contacts->pluck('id')->values()),
        totalCount: {{ $contacts->total() }},
        bulkAction: '',
        selectedAccountId: '',
        exportFormat: 'csv',
        filtersForBulk: {
            q: @js($q),
            account_id: @js($accountId),
            af: @js($advancedRaw),
        },

        fieldMeta(key) {
            return this.fields.find(f => f.key === key) || { type: 'string', label: key };
        },
        opsFor(fieldKey) {
            const type = this.fieldMeta(fieldKey).type;
            if (type === 'date') return this.dateOps;
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
                value2: '',
            });
        },
        removeRule(i) {
            this.advancedRules.splice(i, 1);
            if (this.advancedRules.length === 0) {
                this.addRule();
            }
        },
        serializeAdvanced() {
            const payload = {
                match: this.advancedMatch,
                rules: this.advancedRules,
            };
            return JSON.stringify(payload);
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
                this.selectedIds = Array.from(new Set([...this.selectedIds, ...this.pageContactIds]));
            } else {
                this.selectedIds = this.selectedIds.filter((id) => !this.pageContactIds.includes(id));
            }
        },
        isSelected(id) {
            return this.selectedIds.includes(id);
        },
        syncSelectAll() {
            this.selectAll = this.pageContactIds.length > 0 && this.pageContactIds.every((id) => this.selectedIds.includes(id));
        },
        selectAllResults() {
            if (this.selectedIds.length === 0) {
                this.selectedIds = [...this.pageContactIds];
            }
            this.selectAllAcrossPages = true;
        },
        submitBulk(action) {
            if (this.selectedIds.length === 0 && !this.selectAllAcrossPages) {
                alert('Selecciona al menos un contacto.');
                return;
            }
            if (action === 'assign_account' && !this.selectedAccountId) {
                alert('Selecciona la cuenta destino.');
                return;
            }
            this.bulkAction = action;
            this.$nextTick(() => this.$refs.bulkForm.submit());
        },
        submitExport(format) {
            if (this.selectedIds.length === 0 && !this.selectAllAcrossPages) {
                alert('Selecciona al menos un contacto.');
                return;
            }
            this.exportFormat = format;
            this.$nextTick(() => this.$refs.exportForm.submit());
        }
    }"
    x-init="init()"
    class="space-y-6"
>
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Contactos</h1>
            <p class="mt-1 text-sm text-slate-500">Gestiona los contactos con la estética y filtros de cuentas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($views->count() > 0)
                <form method="GET" action="{{ route('contacts.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="account_id" value="{{ $accountId }}">
                    <input type="hidden" name="af" value="{{ $advancedRaw }}">

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
                    class="hidden sm:inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    @click="$dispatch('open-save-contact-view')"
                >
                    Guardar vista
                </button>
            @endauth
            <a
                href="{{ route('contacts.create') }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
            >
                + Nuevo contacto
            </a>
        </div>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form x-ref="filterForm" method="GET" action="{{ route('contacts.index') }}" class="flex flex-1 flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[220px]">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14z" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Buscar por nombre, email o teléfono"
                        class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#9d1872] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                </div>

                <select
                    name="account_id"
                    class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="">Todas las cuentas</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                            {{ $account->name }}
                        </option>
                    @endforeach
                </select>

                <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">

                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    @click="advancedOpen = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                    </svg>
                    Filtros avanzados
                </button>

               <button type="submit" class="ml-auto inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]">
                    Aplicar filtros
                </button>
            </form>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                    :checked="selectAll"
                    @change="toggleSelectAll($event.target.checked)"
                >
                <span class="text-sm text-slate-700">Seleccionar página</span>
                <button type="button" class="text-xs text-[#9d1872] hover:underline" @click="selectAllResults">Seleccionar todos ({{ $contacts->total() }})</button>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[11px] text-slate-500">Acciones:</span>
                <select
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    x-model="selectedAccountId"
                >
                    <option value="">Asignar a cuenta...</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50" @click="submitBulk('assign_account')">Asignar cuenta</button>
                <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 font-semibold text-red-700 hover:bg-red-100" @click="submitBulk('delete')">Borrar</button>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[11px] text-slate-500">Descargar selección:</span>
                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50" @click="submitExport('csv')">CSV</button>
                <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50" @click="submitExport('xlsx')">Excel</button>
            </div>

            <form x-ref="bulkForm" method="POST" action="{{ route('contacts.bulk') }}" class="hidden">
                @csrf
                <input type="hidden" name="action" x-model="bulkAction">
                <input type="hidden" name="target_account_id" :value="selectedAccountId">
                <input type="hidden" name="select_all" :value="selectAllAcrossPages ? 1 : 0">
                <input type="hidden" name="q" :value="filtersForBulk.q ?? ''">
                <input type="hidden" name="account_id" :value="filtersForBulk.account_id ?? ''">
                <input type="hidden" name="af" :value="serializeAdvanced()">
                <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>

            <form x-ref="exportForm" method="GET" action="{{ route('contacts.export') }}" class="hidden">
                <input type="hidden" name="format" x-model="exportFormat">
                <input type="hidden" name="select_all" :value="selectAllAcrossPages ? 1 : 0">
                <input type="hidden" name="q" :value="filtersForBulk.q ?? ''">
                <input type="hidden" name="account_id" :value="filtersForBulk.account_id ?? ''">
                <input type="hidden" name="af" :value="serializeAdvanced()">
                <input type="hidden" name="vista_id" value="{{ optional($activeView)->id }}">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
        </div>

        <div class="space-y-2 md:hidden">
            @forelse($contacts as $contact)
                @php
                    $displayName = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) ?: $contact->name;
                    $displayRole = $contact->job_title ?? $contact->role ?? '—';
                    $displayAccount = $contact->primaryAccount ?? $contact->accounts->firstWhere('pivot.es_principal', true) ?? $contact->accounts->first();
                @endphp
                <div class="rounded-xl border border-slate-100 bg-white px-3 py-3 shadow-sm">
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                            :checked="isSelected({{ $contact->id }})"
                            @click.stop="toggleSelect({{ $contact->id }})"
                        >
                        <a href="{{ route('contacts.show', $contact) }}" class="flex flex-1 items-center justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $displayName }}</div>
                                <div class="text-xs text-slate-500">{{ $contact->email ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $contact->phone ?? '—' }}</div>
                            </div>
                            <div class="text-right text-[11px] text-slate-500 space-y-1">
                                <div>{{ $displayRole }}</div>
                                @if($displayAccount)
                                    <div class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700">{{ $displayAccount->name }}</div>
                                @endif
                            </div>
                        </a>
                    </div>
                </div>
            @empty
                <p class="px-3 py-4 text-center text-xs text-slate-500">No se han encontrado contactos con los filtros aplicados.</p>
            @endforelse
        </div>

        <div class="hidden md:block">
            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                    :checked="selectAll"
                                    @change="toggleSelectAll($event.target.checked)"
                                >
                            </th>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Teléfono</th>
                            <th class="px-4 py-3 text-left">Cargo</th>
                            <th class="px-4 py-3 text-left">Cuenta</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @forelse ($contacts as $contact)
                            @php
                                $displayName = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')) ?: $contact->name;
                                $displayRole = $contact->job_title ?? $contact->role ?? '—';
                                $displayAccount = $contact->primaryAccount ?? $contact->accounts->firstWhere('pivot.es_principal', true) ?? $contact->accounts->first();
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                        :checked="isSelected({{ $contact->id }})"
                                        @click.stop="toggleSelect({{ $contact->id }})"
                                    >
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $displayName }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $contact->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $contact->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $displayRole }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if ($displayAccount)
                                        <a href="{{ route('accounts.show', $displayAccount) }}" class="text-[#9d1872] hover:underline">{{ $displayAccount->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('contacts.show', $contact) }}" class="text-[#9d1872] hover:underline">Ver</a>
                                        <a href="{{ route('contacts.edit', $contact) }}" class="text-[#9d1872] hover:underline">Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-500">Sin contactos con estos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pt-2">
            {{ $contacts->withQueryString()->links() }}
        </div>
    </section>

    <div
        x-show="advancedOpen"
        style="display:none"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
    >
        <div class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Filtros avanzados</h2>
                    <p class="text-sm text-slate-500">Combina condiciones como en cuentas.</p>
                </div>
                <button class="text-slate-400 hover:text-slate-600" @click="advancedOpen = false">✕</button>
            </div>

            <div class="mt-4 space-y-3">
                <div class="flex items-center gap-3 text-sm text-slate-600">
                    <span>Mostrar resultados que cumplan</span>
                    <select x-model="advancedMatch" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                        <option value="all">todas las condiciones</option>
                        <option value="any">cualquiera de las condiciones</option>
                    </select>
                </div>

                <template x-for="(rule, index) in advancedRules" :key="index">
                    <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 p-3 sm:grid-cols-4 sm:items-center">
                        <select
                            x-model="rule.field"
                            @change="onFieldChange(rule)"
                            class="rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        >
                            <template x-for="field in fields" :key="field.key">
                                <option :value="field.key" x-text="field.label"></option>
                            </template>
                        </select>

                        <select
                            x-model="rule.operator"
                            class="rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        >
                            <template x-for="op in opsFor(rule.field)" :key="op.value">
                                <option :value="op.value" x-text="op.label"></option>
                            </template>
                        </select>

                        <div class="flex gap-2" :class="{'col-span-2': usesSecondValue(rule)}">
                            <input
                                x-show="!hideValue(rule)"
                                type="text"
                                x-model="rule.value"
                                class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                :placeholder="fieldMeta(rule.field).type === 'date' ? 'YYYY-MM-DD' : 'Valor'"
                            >
                            <input
                                x-show="usesSecondValue(rule)"
                                type="text"
                                x-model="rule.value2"
                                class="w-full rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                placeholder="Hasta"
                            >
                        </div>

                        <button type="button" class="justify-self-end text-xs text-red-500 hover:underline" @click="removeRule(index)">Eliminar</button>
                    </div>
                </template>

                <button type="button" class="rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-400" @click="addRule()">+ Añadir condición</button>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="advancedOpen = false">Cancelar</button>
                <button
                    type="button"
                    class="rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
                    @click="advancedOpen = false; $refs.filterForm.submit();"
                >
                    Aplicar filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Modal guardar vista de contactos --}}
    @auth
        <div
            x-data="{ open: false }"
            x-on:open-save-contact-view.window="open = true"
            x-cloak
        >
            <div
                x-show="open"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
                style="display: none"
                @click.self="open = false"
            >
                <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Guardar vista de contactos</h2>
                            <p class="text-sm text-slate-500">Guarda esta combinación de filtros como una vista reutilizable.</p>
                        </div>
                        <button class="text-slate-400 hover:text-slate-600" @click="open = false">✕</button>
                    </div>

                    <form method="POST" action="{{ route('contacts.views.store') }}" class="mt-4 space-y-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-slate-700">Nombre de la vista</label>
                            <input
                                type="text"
                                name="name"
                                required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                            >
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                name="is_default"
                                value="1"
                                class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                            >
                            <span class="text-sm text-slate-700">Hacer vista predeterminada</span>
                        </div>

                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="account_id" value="{{ $accountId }}">
                        <input type="hidden" name="af" :value="serializeAdvanced()">

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                type="button"
                                class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                @click="open = false"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
                            >
                                Guardar vista
                            </button>
                        </div>
                    </form>

                    @if($views->count() > 0)
                        <div class="mt-6 border-t border-slate-100 pt-4">
                            <h3 class="text-sm font-semibold text-slate-800 mb-2">Vistas guardadas</h3>
                            <div class="space-y-2 text-sm text-slate-700">
                                @foreach($views as $view)
                                    <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                                        <div>
                                            <div class="font-medium">{{ $view->name }}</div>
                                            @if($view->is_default)
                                                <div class="text-[11px] text-slate-500">Predeterminada</div>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('contacts.views.destroy', $view) }}" onsubmit="return confirm('¿Eliminar vista?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:underline">Eliminar</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endauth
</div>
@endsection