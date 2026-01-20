@extends('layouts.app')

@section('title', 'Editar campaña ' . $campaign->name)

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Editar campaña</h1>
            <p class="text-sm text-slate-500">Actualiza los datos y filtros de la campaña.</p>
        </div>
    </header>

    @include('campaigns._form', [
        'action' => route('campaigns.update', $campaign),
        'campaign' => $campaign,
    ])
</div>
@endsection