@extends('layouts.app')

@section('title', 'Campaña '.$campaign->name)

@section('content')
<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-semibold text-slate-900">{{ $campaign->name }}</h1>
        <p class="text-sm text-slate-500">Estado: {{ ucfirst($campaign->estado) }} · Tipo: {{ $campaign->tipo ? ucfirst($campaign->tipo) : '—' }}</p>
        <p class="text-xs text-slate-500">Número: {{ $campaign->campaign_number ?? '—' }}</p>
    </header>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm md:col-span-2 space-y-3">
            <h2 class="text-sm font-semibold text-slate-700">Segmentación</h2>
            <div class="rounded-lg bg-slate-50 p-3 text-sm text-slate-700 space-y-1">
                @forelse ($filterSummary as $campo => $valor)
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ $campo }}</span>
                        <span>{{ $valor }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Sin filtros definidos (se incluyen todos los contactos permitidos).</p>
                @endforelse
            </div>
            <p class="text-sm text-slate-500">Audiencia estimada: <strong>{{ $audienciaCount }}</strong> contactos.</p>
            <div class="space-y-2">
                <p class="text-xs uppercase text-slate-500">Ejemplos</p>
                <ul class="divide-y divide-slate-100 rounded-lg border border-slate-100 bg-slate-50">
                    @foreach ($previewContacts as $contact)
                        <li class="px-3 py-2 text-sm">
                            {{ $contact->first_name }} {{ $contact->last_name }} — {{ $contact->email }} ({{ optional($contact->primaryAccount)->name ?? 'Sin cuenta' }})
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="space-y-3">
            <div class="rounded-xl bg-white p-4 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">KPIs</h2>
                    <a href="{{ route('campaigns.edit', $campaign) }}" class="text-xs font-semibold text-[#9d1872]">Editar</a>
                </div>
                <ul class="text-sm text-slate-700 space-y-1">
                    <li>Contactos en campaña: {{ $campaign->contacts->count() }}</li>
                    <li>Eventos registrados: {{ $campaign->events->count() }}</li>
                    <li>Última sync: {{ optional($campaign->last_sync_at)->diffForHumans() ?? 'Nunca' }}</li>
                    <li>Snapshot estático: {{ $campaign->static_snapshot ? 'Sí' : 'No' }}</li>
                </ul>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm space-y-2">
                <h2 class="text-sm font-semibold text-slate-700">Detalles</h2>
                <dl class="text-sm text-slate-700 space-y-1">
                    <div class="flex items-center justify-between"><dt class="font-medium">Inicio</dt><dd>{{ optional($campaign->planned_start_at)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Fin</dt><dd>{{ optional($campaign->planned_end_at)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Confirmación email</dt><dd>{{ $campaign->email_confirmation_required ? 'Sí' : 'No' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Tamaño empresa</dt><dd>{{ $campaign->company_size ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Plan de igualdad</dt><dd>{{ $campaign->equality_plan_preference ? ucfirst($campaign->equality_plan_preference) : 'Indiferente' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Vigencia plan</dt><dd>{{ optional($campaign->equality_plan_valid_until)->format('d/m/Y') ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Distintivo igualdad</dt><dd>{{ $campaign->equality_mark_preference ? ucfirst($campaign->equality_mark_preference) : 'Indiferente' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Habitantes</dt><dd>{{ $campaign->habitantes ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Origen</dt><dd>{{ $campaign->origen ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Mautic Campaign</dt><dd>{{ $campaign->mautic_campaign_id ?? '—' }}</dd></div>
                    <div class="flex items-center justify-between"><dt class="font-medium">Mautic Segment</dt><dd>{{ $campaign->mautic_segment_id ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        @if (! empty($mauticPreview))
            <div class="md:col-span-3 rounded-xl bg-white p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-700">Campaña seleccionada en Mautic</h2>
                        <p class="text-xs text-slate-500">{{ $mauticPreview['name'] ?? 'Campaña' }} · ID {{ $mauticPreview['id'] ?? $campaign->mautic_campaign_id }}</p>
                    </div>
                    @if (array_key_exists('is_published', $mauticPreview))
                        <span class="text-xs font-semibold {{ $mauticPreview['is_published'] ? 'text-green-700' : 'text-amber-700' }}">
                            {{ $mauticPreview['is_published'] ? 'Publicada' : 'Borrador' }}
                        </span>
                    @endif
                </div>

                @if (! empty($mauticPreview['preview_html']))
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        {!! $mauticPreview['preview_html'] !!}
                    </div>
                @elseif (! empty($mauticPreview['description']))
                    <p class="text-sm text-slate-700">{{ $mauticPreview['description'] }}</p>
                @else
                    <p class="text-sm text-slate-500">No hay previsualización disponible para esta campaña.</p>
                @endif
            </div>
        @endif
        <div class="md:col-span-3 rounded-xl bg-white p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Métricas desde Mautic</h2>
                <p class="text-xs text-slate-500">{{ $mauticMetrics['status'] ?? 'Actualizado' }}</p>
            </div>
            <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Segmento total</p>
                    <p class="text-xl font-semibold text-slate-900">{{ $mauticMetrics['segment_total'] ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Correos enviados</p>
                    <p class="text-xl font-semibold text-slate-900">{{ $mauticMetrics['emails_sent'] ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Correos abiertos</p>
                    <p class="text-xl font-semibold text-slate-900">{{ $mauticMetrics['emails_opened'] ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Clics</p>
                    <p class="text-xl font-semibold text-slate-900">{{ $mauticMetrics['emails_clicked'] ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Correos rebotados</p>
                    <p class="text-xl font-semibold text-slate-900">{{ $mauticMetrics['emails_bounced'] ?? '—' }}</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-[11px] uppercase tracking-wide text-slate-500">Bajas</p>
                    <p class="text-xl font-semibold text-slate-900">{{ $mauticMetrics['unsubscribed'] ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection