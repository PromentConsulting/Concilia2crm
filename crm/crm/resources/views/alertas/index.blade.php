@extends('layouts.app')

@section('title', 'Alertas')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Alertas
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Centro de alertas comerciales (tareas vencidas, solicitudes abiertas, etc.).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('alertas.edit') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
            >
                Configurar alertas
            </a>
        </div>
    </header>

    {{-- Mensaje flash --}}
    @if(session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-2xl bg-white p-5 shadow-sm">
        @if(empty($alerts))
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="mb-3 rounded-full bg-slate-100 p-3">
                    <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-slate-900">
                    No tienes alertas nuevas
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Cuando tengas tareas vencidas o solicitudes abiertas aparecerán aquí.
                </p>
            </div>
        @else
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach($alerts as $alert)
                    <li class="flex items-start gap-3 py-3">
                        <div class="mt-0.5">
                            @if($alert['tipo'] === 'Tarea vencida')
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-50 text-rose-500 text-[11px] font-semibold">
                                    T
                                </span>
                            @else
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-50 text-amber-500 text-[11px] font-semibold">
                                    S
                                </span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-700">
                                {{ $alert['tipo'] }}
                            </p>
                            <p class="mt-0.5 text-sm text-slate-800">
                                {{ $alert['mensaje'] }}
                            </p>
                            <a
                                href="{{ $alert['url'] }}"
                                class="mt-1 inline-flex text-xs text-[#9d1872] hover:underline"
                            >
                                Ver detalle
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
