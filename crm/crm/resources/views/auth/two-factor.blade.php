@extends('layouts.base')

@section('title', 'Verificación en dos pasos')

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
                <h1 class="text-2xl font-semibold text-slate-900">Verificación en dos pasos</h1>
                <p class="text-sm text-slate-500">Introduce el código de 6 dígitos para continuar.</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
            @if (session('status'))
                <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('two_factor:preview_code'))
                <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    <p class="font-semibold">Modo desarrollo / mailer en log:</p>
                    <p>Tu código temporal es <span class="font-mono">{{ session('two_factor:preview_code') }}</span>.</p>
                    <p class="mt-1 text-xs text-amber-700">Configura un mailer SMTP para ocultar este aviso.</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label for="code" class="block text-sm font-semibold text-slate-700">
                        Código de 6 dígitos
                    </label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        required
                        autofocus
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[rgb(157,24,114)] focus:ring-2 focus:ring-[rgb(157,24,114)]/30 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex justify-center items-center rounded-xl bg-[rgb(157,24,114)] px-4 py-3 text-sm font-semibold text-white transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[rgb(157,24,114)]"
                >
                    Confirmar código
                </button>
            </form>

            <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-4">
                @csrf
                <button
                    type="submit"
                    class="w-full inline-flex justify-center items-center rounded-xl border border-[rgb(157,24,114)]/30 bg-white px-4 py-3 text-sm font-semibold text-[rgb(157,24,114)] transition hover:bg-[rgb(157,24,114)]/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[rgb(157,24,114)]"
                >
                    Reenviar código
                </button>
            </form>
        </div>
    </div>
</div>
@endsection