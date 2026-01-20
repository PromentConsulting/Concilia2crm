@extends('layouts.app')

@section('title', 'Integraciones')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Integraciones y conectores</h1>
            <p class="mt-1 text-sm text-slate-500">
                Opciones de integración del CRM con otras plataformas: Excel/CSV, web corporativa,
                correo electrónico y teléfono.
            </p>
        </div>
    </header>

    {{-- TARJETAS PRINCIPALES --}}
    <section class="grid gap-4 md:grid-cols-3">
        {{-- 1) Importación desde Excel / CSV --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100 flex flex-col">
            <h2 class="text-sm font-semibold text-slate-900 mb-1">
                Importación de cuentas desde Excel / CSV
            </h2>
            <p class="text-xs text-slate-500 mb-3">
                El CRM permite importar empresas desde ficheros Excel/CSV del sistema anterior
                u otras fuentes. El asistente de importación procesa el fichero y crea
                automáticamente las cuentas en la base de datos.
            </p>
            <ul class="text-xs text-slate-500 mb-3 list-disc list-inside space-y-1">
                <li>Lectura de fichero CSV exportado desde Excel.</li>
                <li>Mapeo automático de columnas a los campos de cuenta.</li>
                <li>Creación masiva de registros en el CRM.</li>
            </ul>
            <div class="mt-auto">
                <a
                    href="{{ route('accounts.import.create') }}"
                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]"
                >
                    Abrir asistente de importación
                </a>
            </div>
        </div>

        {{-- 2) Integración con la web / canales de captación --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100 flex flex-col">
            <h2 class="text-sm font-semibold text-slate-900 mb-1">
                Integración con la web corporativa y canales de captación
            </h2>
            <p class="text-xs text-slate-500 mb-3">
                Las solicitudes comerciales admiten diferentes orígenes (web, email,
                teléfono, otros). De esta forma, los leads que llegan desde la página web,
                correo u otros canales quedan identificados dentro del CRM.
            </p>
            <ul class="text-xs text-slate-500 mb-3 list-disc list-inside space-y-1">
                <li>Campo <strong>origen</strong> en las solicitudes (web, email, teléfono, otro).</li>
                <li>Posibilidad de registrar manualmente leads procedentes de formularios web.</li>
                <li>Vinculación de cada solicitud con cuenta y contacto del CRM.</li>
            </ul>
            <div class="mt-auto">
                <a
                    href="{{ route('solicitudes.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Ver solicitudes / leads
                </a>
            </div>
        </div>

        {{-- 3) Correo electrónico y teléfono --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100 flex flex-col">
            <h2 class="text-sm font-semibold text-slate-900 mb-1">
                Integración con correo y teléfono
            </h2>
            <p class="text-xs text-slate-500 mb-3">
                Desde las fichas de cuentas, contactos y solicitudes se puede lanzar
                directamente el cliente de correo o la aplicación de teléfono del
                dispositivo, facilitando la comunicación con el cliente.
            </p>
            <ul class="text-xs text-slate-500 mb-3 list-disc list-inside space-y-1">
                <li>Enlaces <strong>mailto:</strong> para abrir el gestor de correo.</li>
                <li>Enlaces <strong>tel:</strong> para realizar llamadas desde móvil.</li>
                <li>Acceso directo a los datos de contacto desde el CRM.</li>
            </ul>
            <div class="mt-auto">
                <a
                    href="{{ route('accounts.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Ver cuentas y contactos
                </a>
            </div>
        </div>
    </section>

    {{-- BLOQUE EXPLICATIVO PARA LA JUSTIFICACIÓN --}}
    <section class="rounded-2xl bg-white p-5 shadow-sm border border-slate-100">
        <h2 class="text-sm font-semibold text-slate-900 mb-2">
            Uso de las integraciones en el día a día
        </h2>
        <p class="text-xs text-slate-500 mb-2">
            Las integraciones permiten conectar el CRM con otras herramientas ya utilizadas
            por el beneficiario:
        </p>
        <ul class="text-xs text-slate-500 list-disc list-inside space-y-1 mb-2">
            <li><strong>Excel/CSV:</strong> para importar datos históricos de cuentas desde
                hojas de cálculo o sistemas anteriores.</li>
            <li><strong>Web corporativa / canales de captación:</strong> los leads se registran
                como solicitudes indicando su origen (web, email, teléfono…), de forma que
                se pueda analizar la procedencia.</li>
            <li><strong>Correo y teléfono:</strong> las fichas del CRM disponen de enlaces
                directos para enviar emails o realizar llamadas desde el dispositivo.</li>
        </ul>
        <p class="text-xs text-slate-500">
            Para la justificación puedes tomar capturas de:
        </p>
        <ul class="mt-1 text-xs text-slate-500 list-disc list-inside space-y-1">
            <li>Esta pantalla de <strong>Integraciones</strong>.</li>
            <li>La pantalla de <strong>Importar cuentas</strong> desde Excel/CSV.</li>
            <li>Una solicitud donde se vea el campo <strong>origen</strong> con valor
                “web”, “email” o “teléfono”.</li>
            <li>La ficha de una cuenta o contacto donde se vean los enlaces de
                <strong>email</strong> y <strong>teléfono</strong> clicables.</li>
        </ul>
    </section>
</div>
@endsection
