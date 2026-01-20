@extends('layouts.app')

@section('title', $peticion->titulo)

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $peticion->codigo ?? 'Sin código' }}</span>
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $peticion->titulo }}
                </h1>
            </div>
            <p class="mt-1 text-sm text-slate-500 flex flex-wrap items-center gap-3">
                <span class="uppercase tracking-wide text-xs">Petición {{ $peticion->estado }}</span>
                <span>Importe: {{ $peticion->importe_total ? number_format($peticion->importe_total, 2, ',', '.') . ' ' . $peticion->moneda : 'Sin importe' }}</span>
                <span>Alta: {{ $peticion->fecha_alta ? $peticion->fecha_alta->format('d/m/Y') : '—' }}</span>
                <span>Oferta límite: {{ $peticion->fecha_limite_oferta ? $peticion->fecha_limite_oferta->format('d/m/Y') : '—' }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Botón Generar PDF (solo visual, sin funcionalidad real por ahora) --}}
            <button
                type="button"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-400 bg-slate-50 cursor-not-allowed"
                title="Generación de PDF en desarrollo"
            >
                Generar PDF
            </button>

            <a href="{{ route('peticiones.edit', $peticion) }}"
               class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">
                Editar
            </a>

            <form method="POST" action="{{ route('peticiones.destroy', $peticion) }}"
                  onsubmit="return confirm('¿Seguro que quieres eliminar esta petición?');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-xs text-rose-600 hover:bg-rose-50"
                >
                    Eliminar
                </button>
            </form>
        </div>
    </header>

    <div class="rounded-2xl bg-white shadow-sm">
        <div class="flex flex-wrap gap-2 border-b border-slate-100 px-4 pt-4">
            <button type="button" data-tab-target="datos" class="tab-trigger active inline-flex items-center gap-2 rounded-t-lg bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700">
                Datos
            </button>
            <button type="button" data-tab-target="documentos" class="tab-trigger inline-flex items-center gap-2 rounded-t-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 hover:text-slate-700">
                Documentos
            </button>
            <button type="button" data-tab-target="privado" class="tab-trigger inline-flex items-center gap-2 rounded-t-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500 hover:text-slate-700">
                Privado
            </button>
        </div>

        <div class="tab-panel" data-tab-panel="datos">
            <section class="space-y-6 p-5">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Detalle de la petición</h2>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 text-sm">
                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Cuenta</p>
                        <p class="mt-1 text-slate-800">
                            @if ($peticion->cuenta)
                                <a href="{{ route('accounts.show', $peticion->cuenta) }}" class="text-[#9d1872] hover:underline">
                                    {{ $peticion->cuenta->name }}
                                </a>
                            @else
                                <span class="text-slate-400">Sin cuenta</span>
                            @endif
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Contacto</p>
                        <p class="mt-1 text-slate-800">
                            @if ($peticion->contacto)
                                <a href="{{ route('contacts.show', $peticion->contacto) }}" class="text-[#9d1872] hover:underline">
                                    {{ $peticion->contacto->name ?? ($peticion->contacto->first_name . ' ' . $peticion->contacto->last_name) }}
                                </a>
                            @else
                                <span class="text-slate-400">Sin contacto</span>
                            @endif
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Solicitud origen</p>
                        <p class="mt-1 text-slate-800">
                            @if ($peticion->solicitud)
                                <a href="{{ route('solicitudes.show', $peticion->solicitud) }}" class="text-[#9d1872] hover:underline">
                                    {{ $peticion->solicitud->asunto }}
                                </a>
                            @else
                                <span class="text-slate-400">No vinculada</span>
                            @endif
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Código</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->codigo ?? '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Año</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->anio ?? '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Estado</p>
                        <p class="mt-1 text-slate-800">{{ ucfirst($peticion->estado) }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Importe total</p>
                        <p class="mt-1 text-slate-800">
                            {{ $peticion->importe_total ? number_format($peticion->importe_total, 2, ',', '.') . ' ' . $peticion->moneda : '—' }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Alta</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->fecha_alta ? $peticion->fecha_alta->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Oferta límite</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->fecha_limite_oferta ? $peticion->fecha_limite_oferta->format('d/m/Y') : '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Comercial</p>
                        <p class="mt-1 text-slate-800">{{ optional($peticion->owner)->name ?? '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Creado por</p>
                        <p class="mt-1 text-slate-800">{{ optional($peticion->creador)->name ?? '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Memoria</p>
                        <p class="mt-1 text-slate-800">
                            @if($peticion->memoria)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] text-emerald-700">Sí</span>
                            @else
                                <span class="text-slate-400">No</span>
                            @endif
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Subvención</p>
                        <p class="mt-1 text-slate-800">{{ $subvenciones[$peticion->subvencion_id] ?? '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Tipo proyecto</p>
                        <p class="mt-1 text-slate-800">{{ $tiposProyecto[$peticion->tipo_proyecto] ?? '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Gasto subcontratado</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->gasto_subcontratado ?: '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3 md:col-span-2">
                        <p class="text-xs font-medium text-slate-500">Información del cliente</p>
                        <p class="mt-1 whitespace-pre-line text-slate-800">{{ $peticion->info_cliente ?: '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Fecha envío</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->fecha_envio ? $peticion->fecha_envio->format('d/m/Y H:i') : '—' }}</p>
                    </div>

                    <div class="rounded-lg border border-slate-100 p-3">
                        <p class="text-xs font-medium text-slate-500">Fecha respuesta</p>
                        <p class="mt-1 text-slate-800">{{ $peticion->fecha_respuesta ? $peticion->fecha_respuesta->format('d/m/Y H:i') : '—' }}</p>
                    </div>
                </div>

                @if ($peticion->descripcion)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-800">
                        <h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Descripción
                        </h3>
                        <p class="whitespace-pre-line">
                            {{ $peticion->descripcion }}
                        </p>
                    </div>
                @endif

                {{-- LÍNEAS DE PRESUPUESTO / OFERTA --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Líneas de presupuesto / oferta
                        </h3>
                    </div>

                    @if($peticion->lineas && $peticion->lineas->count())
                        <div class="overflow-x-auto rounded-lg border border-slate-100">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Concepto</th>
                                        <th class="px-3 py-2 text-left">Servicio catálogo</th>
                                        <th class="px-3 py-2 text-left">Descripción</th>
                                        <th class="px-3 py-2 text-right">Cantidad</th>
                                        <th class="px-3 py-2 text-right">Precio unit.</th>
                                        <th class="px-3 py-2 text-right">Dto. %</th>
                                        <th class="px-3 py-2 text-right">Importe</th>
                                        <th class="px-3 py-2 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 bg-white">
                                    @foreach($peticion->lineas as $linea)
                                        <tr>
                                            <td class="px-3 py-2 align-top">
                                                <div class="font-medium text-slate-900">
                                                    {{ $linea->concepto }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 align-top text-slate-700">
                                                @if($linea->service)
                                                    <div class="text-sm font-medium text-slate-800">{{ $linea->service->referencia }}</div>
                                                    <p class="text-xs text-slate-500">{{ optional($linea->service->category)->nombre ?? 'Sin categoría' }}</p>
                                                @else
                                                    <span class="text-slate-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 align-top text-slate-700">
                                                {{ $linea->descripcion ?: '—' }}
                                            </td>
                                            <td class="px-3 py-2 align-top text-right">
                                                {{ number_format($linea->cantidad, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 align-top text-right">
                                                {{ number_format($linea->precio_unitario, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 align-top text-right">
                                                {{ number_format($linea->descuento_porcentaje, 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 align-top text-right font-semibold">
                                                {{ number_format($linea->importe_total, 2, ',', '.') }} {{ $peticion->moneda }}
                                            </td>
                                            <td class="px-3 py-2 align-top text-right">
                                                <form method="POST" action="{{ route('peticiones.lineas.destroy', [$peticion, $linea]) }}"
                                                      onsubmit="return confirm('¿Eliminar esta línea?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="text-xs text-rose-600 hover:text-rose-700 hover:underline"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-slate-50">
                                        <td colspan="6" class="px-3 py-2 text-right text-xs font-semibold text-slate-600">
                                            Total
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900">
                                            {{ $peticion->importe_total ? number_format($peticion->importe_total, 2, ',', '.') . ' ' . $peticion->moneda : '—' }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 mb-3">
                            Esta petición todavía no tiene líneas de presupuesto.
                        </p>
                    @endif

                    {{-- Añadir línea manualmente --}}
                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Añadir servicio manual</h4>

                        <form method="POST" action="{{ route('peticiones.lineas.store', $peticion) }}" class="mt-3 space-y-3">
                            @csrf
                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Concepto *</label>
                                    <input type="text" name="concepto" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                                    <input type="text" name="descripcion" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Cantidad *</label>
                                    <input type="number" name="cantidad" value="1" min="0" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Precio unitario *</label>
                                    <input type="number" name="precio_unitario" value="0" min="0" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Descuento (%)</label>
                                    <input type="number" name="descuento_porcentaje" value="0" min="0" max="100" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-slate-900">
                                    Añadir manualmente
                                </button>
                            </div>
                        </form>
                    </div>


                    {{-- Añadir línea desde el catálogo de servicios --}}
                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Añadir servicio del catálogo</h4>
                            <a href="{{ route('catalogo.servicios.index') }}" class="text-xs text-[#9d1872] hover:underline">Ir al catálogo</a>
                        </div>

                        <form method="POST" action="{{ route('peticiones.lineas.store', $peticion) }}" class="space-y-3">
                            @csrf
                            <div class="grid gap-3 md:grid-cols-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Servicio</label>
                                    <select name="service_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                                        <option value="">Selecciona un servicio</option>
                                        @foreach ($categoriasServicios as $categoria)
                                            <optgroup label="{{ $categoria->nombre }}">
                                                @foreach ($servicios->where('service_category_id', $categoria->id) as $svc)
                                                    <option value="{{ $svc->id }}">{{ $svc->referencia }} — {{ Str::limit($svc->descripcion, 60) }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                        @foreach ($servicios->whereNull('service_category_id') as $svc)
                                            <option value="{{ $svc->id }}">{{ $svc->referencia }} — {{ Str::limit($svc->descripcion, 60) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Cantidad</label>
                                        <input type="number" name="cantidad" value="1" min="0" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Precio unitario *</label>
                                        <input type="number" name="precio_unitario" value="0" min="0" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Descuento (%)</label>
                                    <input type="number" name="descuento_porcentaje" value="0" min="0" max="100" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-[#86145f]"
                                >
                                    Añadir línea
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2 text-sm">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Información adicional</p>
                        <p class="mt-2 whitespace-pre-line text-slate-800">{{ $peticion->info_adicional ?: '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Información de facturación</p>
                        <p class="mt-2 whitespace-pre-line text-slate-800">{{ $peticion->info_facturacion ?: '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Comentarios</p>
                        <p class="mt-2 whitespace-pre-line text-slate-800">{{ $peticion->comentarios ?: '—' }}</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="tab-panel hidden" data-tab-panel="documentos">
            <section class="p-5">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Documentos</h2>
                        <p class="text-xs text-slate-500">Gestión documental vinculada a la petición.</p>
                    </div>
                    <a href="{{ route('documentos.create', ['peticion_id' => $peticion->id]) }}" class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-1.5 text-xs font-semibold text-white hover:bg-[#86145f]">+ Añadir documento</a>
                </div>

                @if($peticion->documentos->isEmpty())
                    <p class="text-sm text-slate-500">Aún no hay documentos vinculados.</p>
                @else
                    <div class="overflow-x-auto rounded-lg border border-slate-100">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Título</th>
                                    <th class="px-3 py-2 text-left">Tipo</th>
                                    <th class="px-3 py-2 text-left">Fecha</th>
                                    <th class="px-3 py-2 text-left">Propietario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 bg-white">
                                @foreach($peticion->documentos as $documento)
                                    <tr>
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-slate-900">{{ $documento->titulo }}</div>
                                            @if($documento->descripcion)
                                                <p class="text-xs text-slate-500">{{ Str::limit($documento->descripcion, 80) }}</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">{{ $documento->tipo ?? '—' }}</td>
                                        <td class="px-3 py-2 text-slate-700">{{ $documento->fecha_documento?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="px-3 py-2 text-slate-700">{{ optional($documento->owner)->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <div class="tab-panel hidden" data-tab-panel="privado">
            <section class="space-y-3 bg-slate-50 p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Historial de cambios</h2>
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-[11px] font-semibold text-slate-700">Privado</span>
                </div>

                @php
                    $formatValue = function($value) {
                        if (is_null($value)) return '—';
                        if (is_bool($value)) return $value ? '1' : '0';
                        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
                        return (string) $value;
                    };
                @endphp

                @if($logEntries->isEmpty())
                    <p class="mt-1 text-sm text-slate-500">Sin movimientos registrados.</p>
                @else
                    <ul class="divide-y divide-slate-100 text-xs">
                        @foreach($logEntries as $entry)
                            <li class="flex items-start justify-between gap-3 py-2">
                                <div>
                                    <div class="font-medium text-slate-900">{{ $entry['field'] }}</div>
                                    <div class="mt-0.5 text-slate-500">{{ $formatValue($entry['old_value']) }} → {{ $formatValue($entry['new_value']) }}</div>
                                </div>
                                <div class="text-right text-slate-400">
                                    <div>{{ optional($entry['created_at'])->format('d/m/Y H:i') }}</div>
                                    @if(!empty($entry['user']?->name))
                                        <div>por {{ $entry['user']->name }}</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const triggers = Array.from(document.querySelectorAll('.tab-trigger'));
            const panels = Array.from(document.querySelectorAll('.tab-panel'));

            triggers.forEach(trigger => {
                trigger.addEventListener('click', () => {
                    const target = trigger.getAttribute('data-tab-target');

                    triggers.forEach(btn => btn.classList.remove('active', 'bg-white', 'text-slate-700', 'shadow'));
                    triggers.forEach(btn => btn.classList.add('text-slate-500'));

                    trigger.classList.add('active', 'bg-white', 'text-slate-700', 'shadow');
                    trigger.classList.remove('text-slate-500');

                    panels.forEach(panel => {
                        panel.classList.add('hidden');
                        if (panel.getAttribute('data-tab-panel') === target) {
                            panel.classList.remove('hidden');
                        }
                    });
                });
            });
        });
    </script>
</div>
@endsection