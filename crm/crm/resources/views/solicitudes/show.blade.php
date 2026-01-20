@extends('layouts.app')

@section('title', $solicitud->asunto)

@section('content')
@php
    $activeTab = $tab ?? request('tab', 'resumen');

    $human = function ($value) {
        if ($value === null || $value === '') return '—';
        return ucfirst(str_replace('_', ' ', (string) $value));
    };

    $tareasCount = $solicitud->relationLoaded('tareas') ? $solicitud->tareas->count() : ($solicitud->tareas()->count() ?? 0);
    $docsCount   = $solicitud->relationLoaded('documentos') ? $solicitud->documentos->count() : ($solicitud->documentos()->count() ?? 0);
@endphp

<div class="space-y-6">

    {{-- CABECERA (misma estética que Cuentas) --}}
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $solicitud->titulo }}
                </h1>

                @if($solicitud->estado)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                        Estado: {{ $human($solicitud->estado) }}
                    </span>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                <span>
                    <span class="font-medium">Dpto. Comercial:</span>
                    @if ($solicitud->sales_department )
                        <a href="{{ route('accounts.show', $solicitud->account) }}" class="text-[#9d1872] hover:underline">
                            {{ $solicitud->owner_user_id }}
                        </a>
                    @else
                        <span class="text-slate-400">Sin usuario asignado</span>
                    @endif
                </span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('peticiones.create', ['solicitud_id' => $solicitud->id]) }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white hover:bg-[#86145f]"
            >
                + Crear petición
            </a>
            <a
                href="{{ route('tareas.create', [
                    'solicitud_id' => $solicitud->id,
                    'account_id'   => $solicitud->account_id,
                    'contact_id'   => $solicitud->contact_id,
                ]) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                Nueva actividad
            </a>

            <a
                href="{{ route('solicitudes.edit', $solicitud) }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-sm font-medium text-white shadow hover:bg-[#7b1459]"
            >
                Editar
            </a>

            <form
                method="POST"
                action="{{ route('solicitudes.destroy', $solicitud) }}"
                onsubmit="return confirm('¿Seguro que quieres eliminar esta solicitud?');"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-sm text-rose-700 hover:bg-rose-50"
                >
                    Eliminar
                </button>
            </form>
        </div>
    </header>

    {{-- PESTAÑAS --}}
    <nav class="mt-2 border-b border-slate-200">
        @php
            $tabs = [
                'resumen'    => 'Resumen',
                'actividad'  => 'Actividad',
                'documentos' => 'Documentos',
                'sistema'    => 'Información del sistema',
            ];
        @endphp

        <div class="-mb-px flex flex-wrap gap-4 text-sm">
            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('solicitudes.show', $solicitud) }}?tab={{ $key }}"
                    class="border-b-2 px-3 py-2
                        {{ $activeTab === $key
                            ? 'border-[#9d1872] text-[#9d1872] font-medium'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-200' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- CONTENIDO PESTAÑA: RESUMEN --}}
    @if($activeTab === 'resumen')
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- COLUMNA IZQUIERDA (2/3) --}}
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Detalles
                    </h2>

                    <dl class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-slate-500">Cuenta</dt>
                            <dd class="mt-1">
                                @if ($solicitud->account)
                                    <a href="{{ route('accounts.show', $solicitud->account) }}" class="text-[#9d1872] hover:underline">
                                        {{ $solicitud->account->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin cuenta</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Contacto</dt>
                            <dd class="mt-1">
                                @if ($solicitud->contact)
                                    <a href="{{ route('contacts.show', $solicitud->contact) }}" class="text-[#9d1872] hover:underline">
                                        {{ $solicitud->contact->name
                                            ?? trim(($solicitud->contact->first_name ?? '').' '.($solicitud->contact->last_name ?? ''))
                                        }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Sin contacto</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Origen</dt>
                            <dd class="mt-1 text-slate-900">{{ $human($solicitud->origen) }}</dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Estado</dt>
                            <dd class="mt-1 text-slate-900">{{ $human($solicitud->estado) }}</dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Prioridad</dt>
                            <dd class="mt-1 text-slate-900">{{ $human($solicitud->prioridad) }}</dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Fecha solicitud</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ optional($solicitud->fecha_solicitud ?? $solicitud->created_at)->format('d/m/Y H:i') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-slate-500">Fecha cierre</dt>
                            <dd class="mt-1 text-slate-900">
                                {{ $solicitud->fecha_cierre ? $solicitud->fecha_cierre->format('d/m/Y H:i') : '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                @if ($solicitud->descripcion)
                    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                            Descripción
                        </h2>
                        <p class="text-sm text-slate-700 whitespace-pre-line">
                            {{ $solicitud->descripcion }}
                        </p>
                    </section>
                @endif
            </div>

            {{-- COLUMNA DERECHA (1/3) --}}
            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Resumen
                    </h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Estado</dt>
                            <dd class="font-medium text-slate-900">{{ $human($solicitud->estado) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Prioridad</dt>
                            <dd class="font-medium text-slate-900">{{ $human($solicitud->prioridad) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Origen</dt>
                            <dd class="font-medium text-slate-900">{{ $human($solicitud->origen) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Tareas</dt>
                            <dd class="font-medium text-slate-900">{{ $tareasCount }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Documentos</dt>
                            <dd class="font-medium text-slate-900">{{ $docsCount }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    @endif

    {{-- CONTENIDO PESTAÑA: ACTIVIDAD (tareas relacionadas) --}}
    @if($activeTab === 'actividad')
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                    Actividad
                </h2>

                <a
                    href="{{ route('tareas.create', [
                        'solicitud_id' => $solicitud->id,
                        'account_id'   => $solicitud->account_id,
                        'contact_id'   => $solicitud->contact_id,
                    ]) }}"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-sm font-medium text-white shadow hover:bg-[#7b1459]"
                >
                    + Nueva actividad
                </a>
            </div>

            @if($solicitud->tareas->isEmpty())
                <p class="text-sm text-slate-500">Aún no hay tareas para esta solicitud.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($solicitud->tareas as $tarea)
                        <li class="py-3 flex items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs uppercase tracking-wide text-slate-400">
                                        {{ $human($tarea->tipo) }}
                                    </span>

                                    @if($tarea->estado === 'completada')
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] text-emerald-700">Completada</span>
                                    @elseif($tarea->estado === 'en_proceso')
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] text-amber-700">En proceso</span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700">Pendiente</span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm font-medium text-slate-900">
                                    <a href="{{ route('tareas.edit', $tarea) }}" class="hover:text-[#9d1872]">
                                        {{ $tarea->titulo }}
                                    </a>
                                </p>

                                @if($tarea->descripcion)
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($tarea->descripcion, 140) }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-right text-sm text-slate-600 space-y-1">
                                @if($tarea->fecha_vencimiento)
                                    <div>
                                        <span class="text-slate-500">Vence:</span>
                                        <span class="font-medium text-slate-700">
                                            {{ $tarea->fecha_vencimiento->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @endif

                                @if($tarea->owner)
                                    <div>
                                        <span class="text-slate-500">Propietario:</span>
                                        {{ $tarea->owner->name }}
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    {{-- CONTENIDO PESTAÑA: DOCUMENTOS --}}
    @if($activeTab === 'documentos')
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                    Documentos
                </h2>

                <a
                    href="{{ route('documentos.create', [
                        'solicitud_id' => $solicitud->id,
                        'account_id'   => $solicitud->account_id,
                    ]) }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                >
                    + Nuevo documento
                </a>
            </div>

            @if($solicitud->documentos->isEmpty())
                <p class="text-sm text-slate-500">Esta solicitud todavía no tiene documentos asociados.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($solicitud->documentos as $documento)
                        <li class="flex items-start justify-between gap-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">
                                    <a href="{{ route('documentos.download', $documento) }}" class="hover:text-[#9d1872]">
                                        {{ $documento->titulo }}
                                    </a>
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $documento->tipo ?: 'Documento' }}
                                    @if($documento->fecha_documento)
                                        · {{ $documento->fecha_documento->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>

                            <div class="text-right text-sm text-slate-600 space-y-2">
                                <div>{{ $documento->nombre_original ?: 'Fichero' }}</div>

                                <form
                                    method="POST"
                                    action="{{ route('documentos.destroy', $documento) }}"
                                    onsubmit="return confirm('¿Eliminar este documento?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-rose-700 hover:underline">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    {{-- CONTENIDO PESTAÑA: INFORMACIÓN DEL SISTEMA --}}
    @if($activeTab === 'sistema')
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            {{-- Columna izquierda (2/3): historial de auditoría --}}
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Historial de cambios
                    </h2>

                    @if($solicitud->audits->isNotEmpty())
                        <ul class="divide-y divide-slate-100 text-xs">
                            @foreach($solicitud->audits->sortByDesc('created_at')->take(20) as $audit)
                                <li class="py-2 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-medium text-slate-900">
                                            {{ $audit->field }}
                                        </div>
                                        <div class="mt-0.5 text-slate-500">
                                            {{ $audit->old_value ?? '—' }} → {{ $audit->new_value ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="text-right text-slate-400">
                                        <div>{{ $audit->created_at->format('d/m/Y H:i') }}</div>
                                        @if($audit->user)
                                            <div>por {{ $audit->user->name }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-slate-500">
                            Todavía no hay cambios registrados en esta solicitud.
                        </p>
                    @endif
                </section>
            </div>

            {{-- Columna derecha (1/3): info básica de sistema --}}
            <div class="space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Información del sistema
                    </h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Fecha de alta</dt>
                            <dd class="text-slate-900">
                                {{ optional($solicitud->created_at)->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Última actualización</dt>
                            <dd class="text-slate-900">
                                {{ optional($solicitud->updated_at)->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    @endif


</div>
@endsection
