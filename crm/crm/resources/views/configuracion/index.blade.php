@extends('layouts.app')

@section('title', 'Configuración')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Configuración</h1>
            <p class="mt-1 text-sm text-slate-500">Gestiona las integraciones y preferencias clave del CRM.</p>
        </div>
    </header>

    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="md:col-span-2 rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Integración</p>
                    <h2 class="text-lg font-semibold text-slate-900">Mautic</h2>
                    <p class="text-sm text-slate-500">Configura la URL de tu instancia y el token de API para sincronizar segmentos y KPIs.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('configuracion.mautic') }}" class="space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">URL base de Mautic</label>
                        <input type="url" name="base_url" value="{{ old('base_url', $mautic['base_url'] ?? '') }}" required
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Token de API (Bearer)</label>
                        <input type="text" name="api_token" value="{{ old('api_token', $mautic['api_token'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872]">
                        <p class="mt-1 text-xs text-slate-500">Puedes usar el token Bearer o, alternativamente, las claves pública y secreta.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Clave pública</label>
                        <input type="text" name="public_key" value="{{ old('public_key', $mautic['public_key'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Clave secreta</label>
                        <input type="text" name="secret_key" value="{{ old('secret_key', $mautic['secret_key'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Segmento por defecto</label>
                        <input type="number" name="default_segment" value="{{ old('default_segment', $mautic['default_segment'] ?? '') }}"
                               class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-[#9d1872] focus:ring-1 focus:ring-[#9d1872]" placeholder="ID de segmento en Mautic">
                        <p class="mt-1 text-xs text-slate-500">Se utilizará para precargar el segmento si no eliges uno en la campaña.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">Usa el token de API (o bearer token) generado en Mautic » Configuración » API.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" formaction="{{ route('configuracion.mautic.connect') }}" formmethod="POST"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                            Conectar con Mautic
                        </button>
                        <button type="submit" formaction="{{ route('configuracion.mautic.test') }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Verificar conexión</button>
                        <button type="submit" class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-sm font-semibold text-white hover:bg-[#86145f]">Guardar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-3">
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100 space-y-2">
                <h3 class="text-sm font-semibold text-slate-900">Otras configuraciones</h3>
                <ul class="text-sm text-slate-700 space-y-1">
                    <li><a class="text-[#9d1872] font-semibold" href="{{ route('integraciones.index') }}">Integraciones y tokens API</a></li>
                    <li><a class="text-[#9d1872] font-semibold" href="{{ route('alertas.edit') }}">Alertas y notificaciones</a></li>
                    <li><a class="text-[#9d1872] font-semibold" href="{{ route('roles.index') }}">Roles y permisos</a></li>
                </ul>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-slate-100 space-y-2">
                <h3 class="text-sm font-semibold text-slate-900">Cómo conectar con Mautic</h3>
                <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1">
                    <li>Accede a Mautic y activa la API (Configuración » API).</li>
                    <li>Crea o recupera un token de acceso con permisos de campañas y segmentos.</li>
                    <li>Copia la URL base de tu instancia (ej. https://mautic.midominio.com) y el token en el formulario.</li>
                    <li>Guarda y vuelve a cualquier campaña para ver los KPIs en la ficha.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection