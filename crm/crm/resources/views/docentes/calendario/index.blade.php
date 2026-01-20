@extends('layouts.app')

@section('title', 'Calendario de docentes')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Calendario global de docentes</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta la disponibilidad registrada por cada docente.</p>
        </div>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div id="docentes-calendar" class="min-h-[600px]"></div>
        @if($docentes->isEmpty())
            <p class="mt-4 text-sm text-slate-500">No hay docentes registrados.</p>
        @endif
    </section>

    <div class="space-y-4">
        @foreach($docentes as $docente)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">{{ $docente->name }}</h2>
                        <p class="text-xs text-slate-500">{{ $docente->email }}</p>
                    </div>
                    <a href="{{ route('docentes.calendario.show', $docente) }}" class="text-xs font-medium text-[#9d1872] hover:underline">
                        Ver calendario
                    </a>
                </div>
            </section>
        @endforeach
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('docentes-calendar');
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
