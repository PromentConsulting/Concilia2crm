@extends('layouts.app')

@section('title', 'Tareas comerciales')

@section('content')
@php
    $tipo   = $filters['tipo']   ?? null;
    $estado = $filters['estado'] ?? null;
    $owner  = $filters['owner']  ?? null;
@endphp

<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Tareas comerciales</h1>
            <p class="mt-1 text-sm text-slate-500">
                Gestiona llamadas, reuniones y otras acciones comerciales vinculadas a tus cuentas, contactos y solicitudes.
            </p>
        </div>

        <a
            href="{{ route('tareas.create') }}"
            class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
        >
            + Nueva tarea
        </a>
    </header>

    @if (session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl bg-white p-4 shadow-sm space-y-4 border border-slate-200">
        <form method="GET" action="{{ route('tareas.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <select
                name="tipo"
                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Todos los tipos</option>
                @foreach (['tarea' => 'Tarea', 'llamada' => 'Llamada', 'reunion' => 'Reunión', 'email' => 'Email'] as $value => $label)
                    <option value="{{ $value }}" {{ $tipo === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select
                name="estado"
                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Todos los estados</option>
                @foreach (['pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completada' => 'Completada', 'cancelada' => 'Cancelada'] as $value => $label)
                    <option value="{{ $value }}" {{ $estado === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select
                name="owner"
                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-xs text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Todos los comerciales</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}" {{ (string) $owner === (string) $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                @endforeach
            </select>

            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
            >
                Aplicar filtros
            </button>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Tipo</th>
                        <th class="px-3 py-2 text-left">Título</th>
                        <th class="px-3 py-2 text-left">Vencimiento</th>
                        <th class="px-3 py-2 text-left">Comercial</th>
                        <th class="px-3 py-2 text-left">Relacionado con</th>
                        <th class="px-3 py-2 text-left">Estado</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($tareas as $tarea)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 text-xs">
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                    {{ ucfirst($tarea->tipo) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-800">
                                {{ $tarea->titulo }}
                                @if($tarea->descripcion)
                                    <div class="mt-0.5 text-xs text-slate-500 line-clamp-1">
                                        {{ $tarea->descripcion }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-700">
                                {{ optional($tarea->fecha_vencimiento)->format('d/m/Y H:i') ?: '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-700">
                                {{ optional($tarea->owner)->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-700 space-y-0.5">
                                @if($tarea->account)
                                    <div>Cuenta: <span class="font-medium">{{ $tarea->account->name }}</span></div>
                                @endif
                                @if($tarea->contact)
                                    <div>Contacto: <span class="font-medium">{{ $tarea->contact->name }}</span></div>
                                @endif
                                @if($tarea->solicitud)
                                    <div>Solicitud #{{ $tarea->solicitud->id }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-xs">
                                @php
                                    $label = [
                                        'pendiente'   => 'Pendiente',
                                        'en_progreso' => 'En progreso',
                                        'completada'  => 'Completada',
                                        'cancelada'   => 'Cancelada',
                                    ][$tarea->estado] ?? $tarea->estado;
                                @endphp
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right text-xs">
                                <a
                                    href="{{ route('tareas.edit', $tarea) }}"
                                    class="text-[#9d1872] hover:underline mr-2"
                                >
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('tareas.destroy', $tarea) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-rose-600 hover:underline"
                                        onclick="return confirm('¿Eliminar esta tarea?')"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-xs text-slate-500">
                                No hay tareas con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $tareas->links() }}
        </div>
    </section>
</div>
@endsection
