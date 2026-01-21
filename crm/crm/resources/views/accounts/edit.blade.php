@extends('layouts.app')

@section('title', 'Editar cuenta: ' . $account->name)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-900">
                Editar cuenta
            </h1>

            <a href="{{ route('accounts.show', $account) }}" class="text-sm text-slate-600 hover:text-slate-900">
                Volver a la ficha
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('accounts.update', $account) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @include('accounts.partials.form', [
                'account' => $account,
                'groupParents' => $groupParents ?? collect(),
            ])

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('accounts.show', $account) }}" class="text-sm text-slate-600 hover:text-slate-900">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-medium text-white shadow hover:bg-[#7b1459]"
                >
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection
