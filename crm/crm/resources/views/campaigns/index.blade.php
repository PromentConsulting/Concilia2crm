@extends('layouts.app')

@section('title', 'Campañas')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Campañas</h1>
            <p class="text-sm text-slate-500">Segmentaciones y envíos sincronizados con Mautic.</p>
        </div>
        <a href="{{ route('campaigns.create') }}" class="rounded-lg bg-[#9d1872] px-3 py-2 text-white text-sm font-semibold">Nueva campaña</a>
    </header>

    <div class="overflow-hidden rounded-xl bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Última sync</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $campaign->name }}</td>
                        <td class="px-4 py-3">{{ ucfirst($campaign->estado) }}</td>
                        <td class="px-4 py-3">{{ $campaign->tipo ?? '—' }}</td>
                        <td class="px-4 py-3">{{ optional($campaign->last_sync_at)->diffForHumans() ?? 'Nunca' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('campaigns.show', $campaign) }}" class="text-[#9d1872] hover:underline">Ver</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <a href="{{ route('campaigns.edit', $campaign) }}" class="text-[#9d1872] hover:underline">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No hay campañas creadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection