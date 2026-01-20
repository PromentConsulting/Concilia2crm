@extends('layouts.app')

@php
    $displayName = trim(($contact->first_name ?? '').' '.($contact->last_name ?? '')) ?: $contact->name;
    $primaryRelation = $contact->accounts->firstWhere('pivot.es_principal', true);
    $displayAccount = $contact->primaryAccount ?? $primaryRelation ?? $contact->accounts->first();
    $isPrimary = ($contact->is_primary ?? $contact->primary ?? false) || $primaryRelation !== null;
    $displayRole = $contact->job_title ?? $contact->role ?? null;
@endphp

@section('title', $displayName)

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">{{ $displayName }}</h1>
                @if($isPrimary)
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">Principal</span>
                @endif
            </div>
            <p class="text-sm text-slate-500">{{ $displayRole ?? 'Sin cargo' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('contacts.edit', $contact) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Editar</a>
            <form method="post" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('¿Eliminar este contacto?');">
                @csrf
                @method('delete')
                <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white shadow hover:bg-red-700">Eliminar</button>
            </form>
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Datos de contacto</h2>
            <dl class="grid gap-4 md:grid-cols-2 text-sm">
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $contact->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Teléfono</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $contact->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Cuenta</dt>
                    <dd class="mt-1 font-medium text-[#9d1872]">
                        @if ($displayAccount)
                            <a href="{{ route('accounts.show', $displayAccount) }}" class="hover:underline">{{ $displayAccount->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Creado</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ optional($contact->created_at)?->format('d/m/Y') ?? '—' }}</dd>
                </div>
            </dl>
            <div class="mt-6 text-sm text-slate-600">
                <h3 class="font-semibold text-slate-700">Notas</h3>
                <p class="mt-2 whitespace-pre-line">{{ $contact->notes ?? '—' }}</p>
            </div>
        </section>

        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Cuentas vinculadas</h2>
            @forelse ($contact->accounts as $account)
                <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                    <div class="flex items-center justify-between text-sm">
                        <a href="{{ route('accounts.show', $account) }}" class="font-semibold text-[#9d1872] hover:underline">{{ $account->name }}</a>
                        @if($account->pivot?->es_principal)
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Principal</span>
                        @endif
                    </div>
                    @if($account->pivot?->categoria)
                        <p class="text-xs text-slate-500">{{ ucfirst($account->pivot->categoria) }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Sin cuentas asociadas.</p>
            @endforelse
        </section>
    </div>
</div>
@endsection