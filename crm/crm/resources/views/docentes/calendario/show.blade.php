@extends('layouts.app')

@section('title', 'Calendario docente')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Calendario de {{ $docente->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Gestiona tus bloques de disponibilidad.</p>
        </div>
        <a href="{{ route('docentes.calendario.index') }}" class="text-sm text-slate-600 hover:underline">Volver al calendario global</a>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Añadir bloque</h2>
        <form method="POST" action="{{ route('docentes.calendario.store', $docente) }}" class="mt-4 grid gap-4 md:grid-cols-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Inicio</label>
                <input
                    type="datetime-local"
                    name="inicio"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    required
                >
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Fin</label>
                <input
                    type="datetime-local"
                    name="fin"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                    required
                >
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo</label>
                <select
                    name="tipo"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
                    <option value="disponible">Disponible</option>
                    <option value="no_disponible">No disponible</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Nota</label>
                <input
                    type="text"
                    name="nota"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                >
            </div>
            <div class="md:col-span-4 flex justify-end">
                <button type="submit" class="rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]">
                    Guardar bloque
                </button>
            </div>
        </form>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Calendario mensual</h2>
        <div id="docente-calendar" class="mt-4 min-h-[500px]"></div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bloques registrados</h2>
        @if($docente->disponibilidades->isEmpty())
            <p class="mt-3 text-sm text-slate-500">Sin bloques registrados.</p>
        @else
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach($docente->disponibilidades as $bloque)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-2">
                        <div>
                            <span class="font-medium text-slate-900">
                                {{ $bloque->inicio->format('d/m/Y H:i') }}
                                → {{ $bloque->fin->format('d/m/Y H:i') }}
                            </span>
                            <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[11px]
                                {{ $bloque->tipo === 'disponible' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $bloque->tipo === 'disponible' ? 'Disponible' : 'No disponible' }}
                            </span>
                            @if($bloque->nota)
                                <p class="text-xs text-slate-500">{{ $bloque->nota }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('docentes.calendario.destroy', $bloque) }}" onsubmit="return confirm('¿Eliminar este bloque?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] text-rose-600 hover:underline">Eliminar</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('docente-calendar');
        if (!calendarEl) return;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 'auto',
            firstDay: 1,
            events: @json($events),
        });

        calendar.render();
    });
</script>
@endsection