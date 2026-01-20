@extends('layouts.app')

@section('title', 'Editar contacto')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Editar contacto
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $contact->name ?? trim(($contact->first_name ?? '').' '.($contact->last_name ?? '')) }}
            </p>
        </div>
    </header>

    <form method="POST" action="{{ route('contacts.update', $contact) }}" class="space-y-6">
        @csrf
        @method('PUT')

        @include('contacts.partials.form', [
            'contact'  => $contact,
            'accounts' => $accounts,
        ])

        <div class="flex justify-end gap-2">
            <a href="{{ route('contacts.show', $contact) }}" class="text-sm text-slate-600 hover:underline">
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
@endsection
