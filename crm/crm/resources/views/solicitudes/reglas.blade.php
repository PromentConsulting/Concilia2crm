@extends('layouts.app')

@section('title', 'Reglas de asignación de solicitudes')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Reglas de asignación de solicitudes</h1>
            <p class="mt-1 text-sm text-slate-500">
                Define cómo se asignan automáticamente las nuevas solicitudes (leads) a los comerciales según origen, prioridad y estado.
            </p>
        </div>
        <a href="{{ route('solicitudes.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
            Volver a solicitudes
        </a>
    </header>

    {{-- Mensajes de estado --}}
    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- REGLAS EXISTENTES --}}
    <section class="rounded-2xl bg-white p-4 shadow-sm border border-slate-200">
        <h2 class="text-sm font-semibold text-slate-800 mb-3">Reglas actuales</h2>

        @if($reglas->isEmpty())
            <p class="text-xs text-slate-500">
                Todavía no hay reglas definidas. Crea la primera regla con el formulario de abajo.
            </p>
        @else
            <div class="overflow-hidden rounded-xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Orden</th>
                            <th class="px-3 py-2 text-left">Nombre</th>
                            <th class="px-3 py-2 text-left">Criterios</th>
                            <th class="px-3 py-2 text-left">Asignar a</th>
                            <th class="px-3 py-2 text-left">Activo</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white text-xs">
                        @foreach ($reglas as $regla)
                            <tr>
                                <td class="px-3 py-2 align-top">
                                    {{ $regla->orden }}
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="font-semibold text-slate-900">{{ $regla->nombre }}</div>
                                    @if($regla->descripcion)
                                        <div class="mt-0.5 text-[11px] text-slate-500">
                                            {{ $regla->descripcion }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <ul class="space-y-0.5 text-[11px] text-slate-600">
                                        @if($regla->origen)
                                            <li>Origen: <span class="font-medium">{{ $origenes[$regla->origen] ?? $regla->origen }}</span></li>
                                        @endif
                                        @if($regla->prioridad)
                                            <li>Prioridad: <span class="font-medium">{{ $prioridades[$regla->prioridad] ?? $regla->prioridad }}</span></li>
                                        @endif
                                        @if($regla->estado)
                                            <li>Estado: <span class="font-medium">{{ $estados[$regla->estado] ?? $regla->estado }}</span></li>
                                        @endif
                                        @if(!$regla->origen && !$regla->prioridad && !$regla->estado)
                                            <li class="text-slate-400">Sin criterios definidos (aplica a todas)</li>
                                        @endif
                                    </ul>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    @if($regla->owner)
                                        <div class="text-[11px] font-medium text-slate-800">
                                            {{ $regla->owner->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $regla->owner->email }}
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400">Sin comercial asignado</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top">
                                    @if($regla->activo)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                            Activa
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">
                                            Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-top text-right">
                                    <form method="POST" action="{{ route('solicitudes.reglas.destroy', $regla) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="text-[11px] text-rose-600 hover:underline"
                                            onclick="return confirm('¿Eliminar esta regla?')"
                                        >
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- FORMULARIO NUEVA REGLA --}}
    <section class="rounded-2xl bg-white p-4 shadow-sm border border-slate-200">
        <h2 class="text-sm font-semibold text-slate-800 mb-3">Nueva regla de asignación</h2>

        <form method="POST" action="{{ route('solicitudes.reglas.store') }}" class="space-y-4 text-xs">
            @csrf

            <div class="grid gap-4 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700">Nombre de la regla</label>
                    <input
                        type="text"
                        name="nombre"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        required
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Orden de prioridad</label>
                    <input
                        type="number"
                        name="orden"
                        value="0"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                    <p class="mt-1 text-[10px] text-slate-400">
                        Se evaluarán de menor a mayor.
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700">Descripción (opcional)</label>
                <textarea
                    name="descripcion"
                    rows="2"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                ></textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Origen</label>
                    <select
                        name="origen"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">(Cualquiera)</option>
                        @foreach ($origenes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Prioridad</label>
                    <select
                        name="prioridad"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">(Cualquiera)</option>
                        @foreach ($prioridades as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700">Estado</label>
                    <select
                        name="estado"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">(Cualquiera)</option>
                        @foreach ($estados as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700">Asignar a comercial</label>
                    <select
                        name="owner_user_id"
                        class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    >
                        <option value="">(Sin asignar)</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 mt-6">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" name="activo" value="1" checked
                               class="h-3.5 w-3.5 text-[#9d1872]">
                        Activa
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]"
                >
                    Guardar regla
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
