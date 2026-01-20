@extends('layouts.app')

@section('title', 'Configurar alertas')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Configuración de alertas
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Elige qué alertas quieres recibir en el CRM.
            </p>
        </div>

        <div>
            <a
                href="{{ route('alertas.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50"
            >
                Volver a alertas
            </a>
        </div>
    </header>

    <section class="rounded-2xl bg-white p-5 shadow-sm max-w-xl">
        <form method="POST" action="{{ route('alertas.update') }}" class="space-y-4">
            @csrf

            <div class="flex items-start gap-3">
                <input
                    type="checkbox"
                    id="notify_overdue_tasks"
                    name="notify_overdue_tasks"
                    value="1"
                    class="mt-1 h-4 w-4 text-[#9d1872] border-slate-300 rounded"
                    {{ $settings->notify_overdue_tasks ? 'checked' : '' }}
                >
                <div>
                    <label for="notify_overdue_tasks" class="text-sm font-medium text-slate-800">
                        Avisar de tareas vencidas
                    </label>
                    <p class="text-xs text-slate-500">
                        Muestra alertas cuando tengas tareas comerciales asignadas con fecha vencida y sin completar.
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <input
                    type="checkbox"
                    id="notify_open_solicitudes"
                    name="notify_open_solicitudes"
                    value="1"
                    class="mt-1 h-4 w-4 text-[#9d1872] border-slate-300 rounded"
                    {{ $settings->notify_open_solicitudes ? 'checked' : '' }}
                >
                <div>
                    <label for="notify_open_solicitudes" class="text-sm font-medium text-slate-800">
                        Avisar de solicitudes abiertas
                    </label>
                    <p class="text-xs text-slate-500">
                        Muestra alertas cuando tengas solicitudes abiertas o en proceso asignadas como responsable.
                    </p>
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <a
                    href="{{ route('alertas.index') }}"
                    class="text-xs text-slate-500 hover:underline"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
                >
                    Guardar configuración
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
