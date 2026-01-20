@extends('layouts.app')

@section('title', 'Nueva campaña')

@section('content')
<div class="space-y-6">
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Crear campaña</h1>
            <p class="text-sm text-slate-500">Define los filtros de segmentación y el tipo de envío.</p>
        </div>
    </header>

    @include('campaigns._form', [
        'action' => route('campaigns.store'),
    ])
</div>
@endsection