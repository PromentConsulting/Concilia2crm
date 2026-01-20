@extends('layouts.app')

@section('title', 'Logs de acceso')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                Logs de acceso
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Histórico de accesos de usuarios a la plataforma.
            </p>
        </div>

        <form method="GET" action="{{ route('logs-accesos.index') }}" class="flex flex-wrap items-center gap-3 text-sm">
            <select
                name="user_id"
                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >
                <option value="">Todos los usuarios</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                @endforeach
            </select>

            <input
                type="month"
                name="month"
                value="{{ request('month') }}"
                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700 focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
            >

            <button
                type="submit"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
            >
                Filtrar
            </button>
        </form>
    </header>

    <section class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="-mx-4 overflow-x-auto sm:mx-0">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Fecha acceso</th>
                        <th class="px-3 py-2 text-left">Usuario</th>
                        <th class="px-3 py-2 text-left">Email</th>
                        <th class="px-3 py-2 text-left">IP</th>
                        <th class="px-3 py-2 text-left">Ruta / acción</th>
                        <th class="px-3 py-2 text-left hidden lg:table-cell">User-Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50">
                            <td class="px-3 py-2 text-sm text-slate-700 whitespace-nowrap">
                                {{ $log->logged_in_at ? $log->logged_in_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $log->user?->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $log->email ?? $log->user?->email ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-700">
                                {{ $log->route ?? 'login' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-slate-500 hidden lg:table-cell">
                                {{ Str::limit($log->user_agent, 80) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-xs text-slate-500">
                                No hay accesos registrados con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </section>
</div>
@endsection
