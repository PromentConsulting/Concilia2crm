@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Usuarios</h1>
            <p class="mt-1 text-sm text-slate-500">
                Gestiona los usuarios del CRM y sus permisos.
            </p>
        </div>

        <a
            href="{{ route('usuarios.create') }}"
            class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#86145f]"
        >
            + Nuevo usuario
        </a>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="overflow-hidden rounded-xl border border-slate-100">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Nombre</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">Rol</th>
                        <th class="px-3 py-2 text-left">Admin</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-3 py-2 text-sm font-medium text-slate-900">
                                {{ $user->name }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $user->email }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $user->role->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                @if ($user->is_admin)
                                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                                        Admin
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right text-xs">
                                <a
                                    href="{{ route('usuarios.edit', $user) }}"
                                    class="rounded-lg border border-slate-200 px-3 py-1 text-xs text-slate-700 hover:bg-slate-50"
                                >
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-xs text-slate-500">
                                No hay usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $users->links() }}
        </div>
    </section>
</div>
@endsection
