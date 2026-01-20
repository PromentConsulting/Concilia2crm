@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Roles</h1>
            <p class="mt-1 text-sm text-slate-500">
                Define qué puede hacer cada tipo de usuario en el CRM.
            </p>
        </div>

        <a
            href="{{ route('roles.create') }}"
            class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
        >
            + Nuevo rol
        </a>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Nombre</th>
                        <th class="px-3 py-2 text-left">Descripción</th>
                        <th class="px-3 py-2 text-left">Usuarios</th>
                        <th class="px-3 py-2 text-left">Predeterminado</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-3 py-2 text-sm font-medium text-slate-900">
                                {{ $role->name }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $role->description ?: '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $role->users_count }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                @if ($role->is_default)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                        Predeterminado
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right text-xs">
                                <a
                                    href="{{ route('roles.edit', $role) }}"
                                    class="rounded-lg border border-slate-200 px-3 py-1 text-xs text-slate-700 hover:bg-slate-50"
                                >
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-xs text-slate-500">
                                No hay roles definidos todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
