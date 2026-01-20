@extends('layouts.app')

@section('title', 'Reglas de asignación de solicitudes')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Reglas de asignación de solicitudes
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Define reglas automáticas para asignar solicitudes a comerciales según origen, estado, prioridad o datos de la cuenta.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('solicitudes.index') }}"
               class="text-sm text-slate-600 hover:text-[#9d1872] hover:underline">
                ← Volver a solicitudes
            </a>
        </div>
    </header>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- Reglas existentes --}}
    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-slate-800">
            Reglas actuales
        </h2>

        @if ($rules->isEmpty())
            <p class="text-xs text-slate-500">
                De momento no hay reglas definidas. Crea tu primera regla más abajo para asignar automáticamente las solicitudes.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Prioridad</th>
                            <th class="px-3 py-2 text-left">Nombre</th>
                            <th class="px-3 py-2 text-left">Condición</th>
                            <th class="px-3 py-2 text-left">Propietario</th>
                            <th class="px-3 py-2 text-left">Activa</th>
                            <th class="px-3 py-2 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($rules as $regla)
                            <tr class="bg-white">
                                <form method="POST" action="{{ route('solicitudes.reglas.update', $regla) }}">
                                    @csrf
                                    @method('PUT')
                                    <td class="px-3 py-2 align-middle">
                                        <input
                                            type="number"
                                            name="priority"
                                            value="{{ old('priority', $regla->priority) }}"
                                            class="w-16 rounded-lg border border-slate-200 px-2 py-1 text-[11px] focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                        >
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ old('name', $regla->name) }}"
                                            class="w-40 rounded-lg border border-slate-200 px-2 py-1 text-[11px] focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                        >
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <div class="flex flex-wrap items-center gap-1">
                                            <select
                                                name="field"
                                                class="w-36 rounded-lg border border-slate-200 px-2 py-1 text-[11px] focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                            >
                                                @foreach ($availableFields as $key => $label)
                                                    <option value="{{ $key }}" @selected($regla->field === $key)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <select
                                                name="operator"
                                                class="w-32 rounded-lg border border-slate-200 px-2 py-1 text-[11px] focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                            >
                                                @foreach ($operators as $key => $label)
                                                    <option value="{{ $key }}" @selected($regla->operator === $key)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <input
                                                type="text"
                                                name="value"
                                                value="{{ old('value', $regla->value) }}"
                                                placeholder="Valor"
                                                class="w-40 rounded-lg border border-slate-200 px-2 py-1 text-[11px] focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                            >
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <select
                                            name="owner_user_id"
                                            class="w-40 rounded-lg border border-slate-200 px-2 py-1 text-[11px] focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                                        >
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" @selected($regla->owner_user_id === $user->id)>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 align-middle">
                                        <label class="inline-flex items-center gap-1 text-[11px] text-slate-600">
                                            <input
                                                type="checkbox"
                                                name="active"
                                                value="1"
                                                class="h-3.5 w-3.5 text-[#9d1872]"
                                                @checked($regla->active)
                                            >
                                            Activa
                                        </label>
                                    </td>
                                    <td class="px-3 py-2 align-middle text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                type="submit"
                                                class="rounded-lg bg-[#9d1872] px-3 py-1 text-[11px] font-semibold text-white hover:bg-[#86145f]"
                                            >
                                                Guardar
                                            </button>
                                </form>
                                            <form
                                                method="POST"
                                                action="{{ route('solicitudes.reglas.destroy', $regla) }}"
                                                onsubmit="return confirm('¿Eliminar esta regla?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="rounded-lg border border-rose-200 px-3 py-1 text-[11px] font-semibold text-rose-600 hover:bg-rose-50"
                                                >
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Nueva regla --}}
    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-sm font-semibold text-slate-800">
            Nueva regla de asignación
        </h2>

        <form method="POST" action="{{ route('solicitudes.reglas.store') }}" class="space-y-4 text-xs">
            @csrf

            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-700">
                        Nombre de la regla
                    </label>
                    <input
                        type="text"
                        name="name"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        placeholder="Ej: Leads web España"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-700">
                        Prioridad
                    </label>
                    <input
                        type="number"
                        name="priority"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        placeholder="1 = más prioridad"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-700">
                        Propietario
                    </label>
                    <select
                        name="owner_user_id"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        required
                    >
                        <option value="">Selecciona usuario…</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-[11px] text-slate-600">
                        <input type="checkbox" name="active" value="1" class="h-3.5 w-3.5 text-[#9d1872]" checked>
                        Regla activa
                    </label>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-[1fr,1fr,2fr]">
                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-700">
                        Campo
                    </label>
                    <select
                        name="field"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        required
                    >
                        @foreach ($availableFields as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-700">
                        Operador
                    </label>
                    <select
                        name="operator"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        required
                    >
                        @foreach ($operators as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium text-slate-700">
                        Valor
                    </label>
                    <input
                        type="text"
                        name="value"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        placeholder="Ej: web, alta, España…"
                    >
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]"
                >
                    Crear regla
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
