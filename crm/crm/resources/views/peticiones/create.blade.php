@extends('layouts.app')

@section('title', 'Nueva petición')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nueva petición</h1>
            <p class="mt-1 text-sm text-slate-500">
                Crea una propuesta a partir de una solicitud ganada.
            </p>
        </div>
    </header>

    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('peticiones.store') }}" class="space-y-6">
            @csrf

            @if($solicitud)
                <input type="hidden" name="solicitud_id" value="{{ $solicitud->id }}">
            @endif

            @include('peticiones.partials.form', [
                'peticion'  => null,
                'cuentas'   => $cuentas,
                'contactos' => $contactos,
                'solicitud' => $solicitud,
                'usuarios'  => $usuarios,
                'subvenciones' => $subvenciones,
                'tiposProyecto' => $tiposProyecto,
            ])

            <div class="flex justify-end gap-2">
                <a href="{{ route('peticiones.index') }}" class="text-sm text-slate-600 hover:underline">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
                >
                    Guardar petición
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
