@extends('layouts.app')

@section('title', 'Nueva solicitud')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nueva solicitud</h1>
            <p class="mt-1 text-sm text-slate-500">
                Registra una solicitud recibida por email, teléfono, web, etc.
            </p>
        </div>
    </header>

    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('solicitudes.store') }}" class="space-y-6">
            @csrf
            @include('solicitudes.partials.form', [
                'solicitud'  => null,
                'cuentas'    => $cuentas,
                'contactos'  => $contactos,
            ])

            <div class="flex justify-end gap-2">
                <a href="{{ route('solicitudes.index') }}" class="text-sm text-slate-600 hover:underline">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
                >
                    Guardar solicitud
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
