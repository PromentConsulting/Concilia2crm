@extends('layouts.app')

@section('title', 'Nuevo pedido')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nuevo pedido</h1>
            <p class="mt-1 text-sm text-slate-500">
                Crea un pedido a partir de una petición ganada o desde cero.
            </p>
        </div>
    </header>

    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('pedidos.store') }}" class="space-y-6">
            @csrf

            @if($peticion)
                <input type="hidden" name="peticion_id" value="{{ $peticion->id }}">
            @endif

            @include('pedidos.partials.form', [
                'pedido'    => null,
                'cuentas'   => $cuentas,
                'contactos' => $contactos,
                'peticion'  => $peticion,
            ])

            @include('pedidos.partials.lineas', ['pedido' => null, 'services' => $services])

            <div class="flex justify-end gap-2">
                <a href="{{ route('pedidos.index') }}" class="text-sm text-slate-600 hover:underline">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
                >
                    Guardar pedido
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
