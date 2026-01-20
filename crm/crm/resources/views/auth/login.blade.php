@extends('layouts.base')

@section('title', 'Acceso')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="flex flex-col items-center gap-4 text-center mb-10">
            <img
                src="{{ asset('assets/logo-full.webp') }}"
                alt="Logo Concilia2"
                class="h-12 object-contain"
            >
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Accede al ERP de Concilia2</h1>
                <p class="text-sm text-slate-500">Por una igualdad REAL y efectiva.</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-slate-700">
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[rgb(157,24,114)] focus:ring-2 focus:ring-[rgb(157,24,114)]/30 focus:outline-none"
                    >
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-slate-700">
                        Contraseña
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[rgb(157,24,114)] focus:ring-2 focus:ring-[rgb(157,24,114)]/30 focus:outline-none"
                    >
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input
                        type="checkbox"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                        class="rounded border-slate-300 text-[rgb(157,24,114)] focus:ring-[rgb(157,24,114)]"
                    >
                    <span>Recuérdame</span>
                </label>

                <button
                    type="submit"
                    class="w-full inline-flex justify-center items-center rounded-xl bg-[rgb(157,24,114)] px-4 py-3 text-sm font-semibold text-white transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[rgb(157,24,114)]"
                >
                    Entrar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection