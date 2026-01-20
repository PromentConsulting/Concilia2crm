@extends('layouts.app')

@section('title', 'Nuevo contacto')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Nuevo contacto</h1>
            <p class="mt-1 text-sm text-slate-500">
                Crea un nuevo contacto y vincúlalo a una o varias cuentas.
            </p>
        </div>
    </header>

    <form method="POST" action="{{ route('contacts.store') }}" class="space-y-6">
        @csrf

        @include('contacts.partials.form', [
            'contact'  => null,
            'accounts' => $accounts,
        ])

        <div class="flex justify-end gap-2">
            <a href="{{ route('contacts.index') }}" class="text-sm text-slate-600 hover:underline">
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]"
            >
                Guardar contacto
            </button>
        </div>
    </form>
</div>
@endsection
