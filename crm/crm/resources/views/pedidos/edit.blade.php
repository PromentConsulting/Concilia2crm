@extends('layouts.app')

@section('title', 'Editar pedido')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Editar pedido
            </h1>
        </div>
    </header>

    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <form method="POST" action="{{ route('pedidos.update', $pedido) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @include('pedidos.partials.form', [
                'pedido'    => $pedido,
                'cuentas'   => $cuentas,
                'contactos' => $contactos,
                'peticion'  => $pedido->peticion,
            ])

            @include('pedidos.partials.lineas', ['pedido' => $pedido, 'services' => $services])

            <div class="flex justify-end gap-2">
                <a href="{{ route('pedidos.show', $pedido) }}" class="text-sm text-slate-600 hover:underline">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
                >
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
