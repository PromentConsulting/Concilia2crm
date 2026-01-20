@extends('layouts.app')

@section('title', 'Solicitudes')

@section('content')
@php
    $q         = $filtros['q'] ?? null;
    $estado    = $filtros['estado'] ?? null;
    $origen    = $filtros['origen'] ?? null;
    $prioridad = $filtros['prioridad'] ?? null;
    $ownerId   = $filtros['owner_user_id'] ?? null;

    $fEstados = [
        'pendiente_asignacion' => 'Pendiente asignación',
        'asignado' => 'Asignado',
        'en_curso' => 'En curso',
        'en_espera' => 'En espera',
        'ganado' => 'Ganado',
        'perdido' => 'Perdido',
    ];
    $fOrigenes = ['web' => 'Web', 'mautic' => 'Mautic', 'manual' => 'Manual', 'importacion' => 'Importación', 'api' => 'API', 'otro' => 'Otro'];
    $fPrioridades = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'urgente' => 'Urgente'];
@endphp

<div
    x-data="{
        openSaveView: false,

        // -------- ACCIONES MASIVAS --------
        selectedIds: [],
        selectAll: false,
        selectAllAcrossPages: false,
        pageSolicitudIds: @js($solicitudes->pluck('id')->values()),
        totalCount: {{ $solicitudes->total() }},
        bulkAction: '',

        selectedEstado: '',
        selectedPrioridad: '',
        selectedUserId: '',
        selectedTeamId: '',

        // Filtros actuales (para select_all)
        filtersForBulk: {
            q: @js($q),
            estado: @js($estado),
            origen: @js($origen),
            prioridad: @js($prioridad),
            owner_user_id: @js($ownerId),
        },

        toggleSelect(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(v => v !== id);
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
                this.selectedIds = Array.from(new Set([...this.selectedIds, ...this.pageSolicitudIds]));
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.pageSolicitudIds.includes(id));
            }
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        syncSelectAll() {
            this.selectAll = this.pageSolicitudIds.length > 0 && this.pageSolicitudIds.every(id => this.selectedIds.includes(id));
        },

        selectAllResults() {
            if (this.selectedIds.length === 0) {
                this.selectedIds = [...this.pageSolicitudIds];
            }
            this.selectAllAcrossPages = true;
        },

        submitBulk(action) {
            if (this.selectedIds.length === 0) {
                alert('Selecciona al menos una solicitud.');
                return;
            }

            if (action === 'delete') {
                if (!confirm('¿Seguro que quieres eliminar las solicitudes seleccionadas?')) return;
            }

            this.bulkAction = action;
            this.$nextTick(() => this.$refs.bulkForm.submit());
        },

        submitEstado() {
            if (!this.selectedEstado) {
                alert('Selecciona un estado destino.');
                return;
            }
            this.submitBulk('set_estado');
        },

        submitPrioridad() {
            if (!this.selectedPrioridad) {
                alert('Selecciona una prioridad destino.');
                return;
            }
            this.submitBulk('set_prioridad');
        },

        submitOwnerUser() {
            if (!this.selectedUserId) {
                alert('Selecciona un usuario destino.');
                return;
            }
            this.submitBulk('assign_owner_user');
        },

        submitOwnerTeam() {
            if (!this.selectedTeamId) {
                alert('Selecciona un equipo destino.');
                return;
            }
            this.submitBulk('assign_owner_team');
        },
    }"
    class="space-y-6"
>
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Solicitudes</h1>
            <p class="mt-1 text-sm text-slate-500">
                Entradas de clientes por teléfono, email, web u otros canales.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Selector de vistas --}}
            @if($views->count() > 0)
                <form method="GET" action="{{ route('solicitudes.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="estado" value="{{ $estado }}">
                    <input type="hidden" name="origen" value="{{ $origen }}">
                    <input type="hidden" name="prioridad" value="{{ $prioridad }}">
                    <input type="hidden" name="owner_user_id" value="{{ $ownerId }}">

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

                @if ($activeView)
                    <form
                        method="POST"
                        action="{{ route('solicitudes.views.destroy', $activeView) }}"
                        onsubmit="return confirm('¿Eliminar esta vista guardada?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50"
                        >
                            Eliminar vista
                        </button>
                    </form>
                @endif
            @endif

            {{-- Botón guardar vista --}}
            @auth
                <button
                    type="button"
                    class="hidden sm:inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
                    @click="openSaveView = true"
                >
                    Guardar vista
                </button>
            @endauth

            <a
                href="{{ route('solicitudes.reglas.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
            >
                Reglas de asignación
            </a>

            <a
                href="{{ route('solicitudes.create') }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
            >
                + Nueva solicitud
            </a>
        </div>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4">
        {{-- FILTROS --}}
        <form method="GET" action="{{ route('solicitudes.index') }}" class="flex flex-wrap items-center gap-3">
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
                    placeholder="Buscar por título, descripción, cuenta, contacto..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#9d1872] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>

            <select name="estado" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                <option value="">Todos los estados</option>
                @foreach ($fEstados as $value => $label)
                    <option value="{{ $value }}" {{ $estado === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select name="origen" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                <option value="">Todos los orígenes</option>
                @foreach ($fOrigenes as $value => $label)
                    <option value="{{ $value }}" {{ $origen === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select name="prioridad" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                <option value="">Todas las prioridades</option>
                @foreach ($fPrioridades as $value => $label)
                    <option value="{{ $value }}" {{ $prioridad === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select name="owner_user_id" class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                <option value="">Todos los propietarios</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ (int) $ownerId === $usuario->id ? 'selected' : '' }}>
                        {{ $usuario->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]">
                Aplicar filtros
            </button>
        </form>

        {{-- ACCIONES MASIVAS --}}
        <div
            x-show="selectedIds.length > 0"
            x-cloak
            class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm"
        >
            <div class="text-sm font-semibold text-slate-800">
                <template x-if="selectAllAcrossPages">
                    <span>
                        Todas las <span x-text="totalCount"></span> solicitudes de la búsqueda están seleccionadas
                    </span>
                </template>
                <template x-if="!selectAllAcrossPages">
                    <span>
                        <span x-text="selectedIds.length"></span> solicitudes seleccionadas
                    </span>
                </template>
            </div>

            <template x-if="!selectAllAcrossPages && totalCount > selectedIds.length">
                <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-slate-600">
                    <span>Has seleccionado las solicitudes de esta página.</span>
                    <button
                        type="button"
                        class="font-semibold text-[#9d1872] hover:underline"
                        @click="selectAllResults()"
                    >
                        Seleccionar las <span x-text="totalCount"></span> solicitudes
                    </button>
                </div>
            </template>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <label class="text-[11px] text-slate-500">Estado</label>
                <select
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    x-model="selectedEstado"
                >
                    <option value="">Selecciona estado</option>
                    @foreach($fEstados as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitEstado()"
                >
                    Cambiar estado
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <label class="text-[11px] text-slate-500">Prioridad</label>
                <select
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    x-model="selectedPrioridad"
                >
                    <option value="">Selecciona prioridad</option>
                    @foreach($fPrioridades as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitPrioridad()"
                >
                    Cambiar prioridad
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <label class="text-[11px] text-slate-500">Propietario (usuario)</label>
                <select
                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    x-model="selectedUserId"
                >
                    <option value="">Selecciona usuario</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                    @endforeach
                </select>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                    @click="submitOwnerUser()"
                >
                    Reasignar usuario
                </button>
            </div>

            @if(isset($teams) && $teams->count())
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
                        @click="submitOwnerTeam()"
                    >
                        Reasignar equipo
                    </button>
                </div>
            @endif

            <div class="ml-auto">
                <button
                    type="button"
                    class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                    @click="submitBulk('delete')"
                >
                    Eliminar
                </button>
            </div>

            {{-- Form oculto --}}
            <form x-ref="bulkForm" method="POST" action="{{ route('solicitudes.bulk') }}" class="hidden">
                @csrf

                <input type="hidden" name="action" x-model="bulkAction">

                {{-- Valores DESTINO (to_*) --}}
                <input type="hidden" name="to_estado" :value="selectedEstado">
                <input type="hidden" name="to_prioridad" :value="selectedPrioridad">
                <input type="hidden" name="to_owner_user_id" :value="selectedUserId">
                <input type="hidden" name="to_owner_team_id" :value="selectedTeamId">

                {{-- Selección --}}
                <input type="hidden" name="select_all" :value="selectAllAcrossPages ? 1 : 0">

                {{-- Filtros actuales (como en Cuentas) --}}
                <input type="hidden" name="q" :value="filtersForBulk.q ?? ''">
                <input type="hidden" name="estado" :value="filtersForBulk.estado ?? ''">
                <input type="hidden" name="origen" :value="filtersForBulk.origen ?? ''">
                <input type="hidden" name="prioridad" :value="filtersForBulk.prioridad ?? ''">
                <input type="hidden" name="owner_user_id" :value="filtersForBulk.owner_user_id ?? ''">

                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
        </div>

        {{-- TABLA --}}
        <div class="overflow-hidden rounded-xl border border-slate-100 mt-3">
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
                        <th class="px-3 py-2 text-left">Título</th>
                        <th class="px-3 py-2 text-left">Cuenta</th>
                        <th class="px-3 py-2 text-left">Contacto</th>
                        <th class="px-3 py-2 text-left">Estado</th>
                        <th class="px-3 py-2 text-left">Prioridad</th>
                        <th class="px-3 py-2 text-left">Origen</th>
                        <th class="px-3 py-2 text-left">Propietario</th>
                        <th class="px-3 py-2 text-right">Creada</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($solicitudes as $solicitud)
                        <tr
                            class="cursor-pointer hover:bg-slate-50"
                            @click="window.location='{{ route('solicitudes.show', $solicitud) }}'"
                        >
                            <td class="px-3 py-2" @click.stop>
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                    :checked="isSelected({{ $solicitud->id }})"
                                    @click.stop="toggleSelect({{ $solicitud->id }})"
                                >
                            </td>

                            <td class="px-3 py-2 text-sm text-slate-900 font-medium">
                                {{ $solicitud->titulo }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $solicitud->account->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                @if ($solicitud->contact)
                                    {{ trim(($solicitud->contact->first_name ?? '') . ' ' . ($solicitud->contact->last_name ?? '')) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $fEstados[$solicitud->estado] ?? $solicitud->estado }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $fPrioridades[$solicitud->prioridad] ?? $solicitud->prioridad }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $fOrigenes[$solicitud->origen] ?? $solicitud->origen }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $solicitud->owner->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-500 text-right">
                                {{ optional($solicitud->created_at)->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-6 text-center text-xs text-slate-500">
                                No se han encontrado solicitudes con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $solicitudes->links() }}
        </div>
    </section>

    {{-- MODAL GUARDAR VISTA --}}
    @auth
        <div x-show="openSaveView" x-cloak class="fixed inset-0 z-40 flex items-center justify-center" aria-label="Guardar vista de solicitudes">
            <div class="absolute inset-0 bg-slate-900/30" @click="openSaveView = false"></div>

            <div class="relative z-50 w-full max-w-md rounded-2xl bg-white p-5 shadow-xl">
                <h2 class="text-sm font-semibold text-slate-900">Guardar vista de solicitudes</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Guarda esta combinación de filtros como una vista reutilizable.
                </p>

                <form method="POST" action="{{ route('solicitudes.views.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nombre de la vista</label>
                        <input type="text" name="name"
                               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                               required>
                    </div>

                    <label class="flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="is_default" value="1" class="h-3.5 w-3.5 text-[#9d1872]">
                        Hacer vista predeterminada
                    </label>

                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="estado" value="{{ $estado }}">
                    <input type="hidden" name="origen" value="{{ $origen }}">
                    <input type="hidden" name="prioridad" value="{{ $prioridad }}">
                    <input type="hidden" name="owner_user_id" value="{{ $ownerId }}">

                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" class="text-xs text-slate-500 hover:underline" @click="openSaveView = false">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]">
                            Guardar vista
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endauth
</div>
@endsection
