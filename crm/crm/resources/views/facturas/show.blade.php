@extends('layouts.app')

@section('title', $factura->numero ?: 'Factura')

@section('content')
@php
    $activeTab = request('tab', 'resumen');
    $tabs = [
        'resumen' => 'Resumen',
        'tareas' => 'Tareas',
        'documentos' => 'Documentos',
        'sistema' => 'Información del sistema',
    ];
    $estadoLabels = [
        'borrador' => ['label' => 'Borrador', 'class' => 'bg-amber-100 text-amber-700'],
        'publicada' => ['label' => 'Publicada', 'class' => 'bg-emerald-100 text-emerald-700'],
        'cancelada' => ['label' => 'Cancelada', 'class' => 'bg-rose-100 text-rose-700'],
    ];
    $estadoInfo = $estadoLabels[$factura->estado ?? 'borrador'] ?? ['label' => '—', 'class' => 'bg-slate-100 text-slate-600'];
    $tareaParams = array_filter([
        'pedido_id' => $factura->pedido_id,
        'account_id' => $factura->account_id,
    ]);
    $documentoParams = array_filter([
        'pedido_id' => $factura->pedido_id,
        'account_id' => $factura->account_id,
    ]);
@endphp

<div class="space-y-6">
    <header class="flex flex-wrap items-start justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $factura->numero ?: 'Factura '.$factura->id }}
                </h1>
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $estadoInfo['class'] }}">
                    {{ $estadoInfo['label'] }}
                </span>
            </div>
            <p class="text-sm text-slate-600">
                {{ $factura->descripcion ?: 'Sin descripción' }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('facturas.pdf', $factura) }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
                Crear PDF
            </a>
            @if($factura->estado === 'borrador')
                <form method="POST" action="{{ route('facturas.publish', $factura) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                        Publicar
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('facturas.rectificar', $factura) }}">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-lg border border-amber-200 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                    Crear rectificativa
                </button>
            </form>
            @if($factura->estado === 'borrador')
                <a href="{{ route('facturas.edit', $factura) }}" class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-xs font-semibold text-white hover:bg-[#86145f]">
                    Editar
                </a>
            @endif
            @if($factura->estado === 'borrador')
                <form method="POST" action="{{ route('facturas.destroy', $factura) }}" onsubmit="return confirm('¿Seguro que quieres eliminar esta factura?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                        Eliminar
                    </button>
                </form>
            @endif
            <a href="{{ route('facturas.index') }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Atrás
            </a>
        </div>
    </header>

    <nav class="mt-2 border-b border-slate-200">
        <div class="-mb-px flex flex-wrap gap-4 text-sm">
            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('facturas.show', $factura) }}?tab={{ $key }}"
                    class="border-b-2 px-3 py-2
                        {{ $activeTab === $key
                            ? 'border-[#9d1872] text-[#9d1872] font-medium'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-200' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    @if($activeTab === 'resumen')
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-4">
                <div class="rounded-lg bg-slate-50 p-4 text-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nº Factura</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ $factura->numero ?: '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</div>
                    <div class="mt-1 text-slate-700">{{ $estadoInfo['label'] }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Tipo</div>
                    <div class="mt-1 text-slate-700">{{ ucfirst($factura->tipo ?? '—') }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Descripción</div>
                    <div class="mt-1 text-slate-700">{{ $factura->descripcion ?: '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente</div>
                    <div class="mt-1 text-slate-700">{{ $factura->cuenta?->name ?? '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha factura</div>
                    <div class="mt-1 text-slate-700">{{ $factura->fecha_factura?->format('d/m/Y') ?? '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Pedido de formación</div>
                    <div class="mt-1 text-slate-700">{{ $factura->pedido?->es_formacion ? 'Sí' : 'No' }}</div>
                </div>

                <div class="rounded-lg bg-slate-50 p-4 text-sm">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha de alta</div>
                    <div class="mt-1 text-slate-700">{{ $factura->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Agrupar referencias</div>
                    <div class="mt-1 text-slate-700">{{ $factura->agrupar_referencias ? 'Sí' : 'No' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Dpto. Comercial</div>
                    <div class="mt-1 text-slate-700">{{ $factura->dpto_comercial ?: '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha vencimiento</div>
                    <div class="mt-1 text-slate-700">{{ $factura->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</div>
                </div>

                <div class="rounded-lg bg-slate-50 p-4 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cobrado</span>
                        <span class="text-slate-700">{{ $factura->cobrado ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Estado de pago</div>
                    <div class="mt-1 text-slate-700">{{ ucfirst($factura->payment_state ?? 'pendiente') }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Forma de pago</div>
                    <div class="mt-1 text-slate-700">{{ $factura->forma_pago ?: '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Instrucción de pago</div>
                    <div class="mt-1 text-slate-700">{{ $factura->instruccion_pago ?: '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha cobro</div>
                    <div class="mt-1 text-slate-700">{{ $factura->fecha_cobro?->format('d/m/Y') ?? '—' }}</div>
                </div>

                <div class="rounded-lg bg-slate-50 p-4 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Contabilizado</span>
                        <span class="text-slate-700">{{ $factura->contabilizado ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Nº Pedido</div>
                    <div class="mt-1 text-slate-700">{{ $factura->pedido?->numero ?? '—' }}</div>
                    <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Email de facturación</div>
                    <div class="mt-1 text-slate-700">{{ $factura->email_facturacion ?: '—' }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Detalle de productos</h2>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Referencia</th>
                            <th class="px-3 py-2 text-left">Concepto</th>
                            <th class="px-3 py-2 text-right">Cantidad</th>
                            <th class="px-3 py-2 text-right">Precio</th>
                            <th class="px-3 py-2 text-right">Descuento %</th>
                            <th class="px-3 py-2 text-right">IVA %</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                            <th class="px-3 py-2 text-right">Importe</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($factura->lineas as $linea)
                            <tr>
                                <td class="px-3 py-2 text-slate-700">{{ $linea->referencia ?: '—' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $linea->concepto ?: '—' }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $linea->cantidad }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format($linea->precio, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format($linea->descuento_porcentaje, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format($linea->iva_porcentaje, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $linea->subtotal !== null ? number_format($linea->subtotal, 2, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $linea->importe !== null ? number_format($linea->importe, 2, ',', '.') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-6 text-center text-xs text-slate-500">
                                    No hay líneas registradas en esta factura.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end text-sm">
                <div class="space-y-1 text-right">
                    <div class="text-slate-500">Importe</div>
                    <div class="text-lg font-semibold text-slate-900">
                        {{ $factura->importe_total !== null ? number_format($factura->importe_total, 2, ',', '.') . ' ' . $factura->moneda : '—' }}
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Información adicional</h2>
            <p class="mt-3 text-sm text-slate-700 whitespace-pre-line">
                {{ $factura->info_adicional ?: '—' }}
            </p>
        </section>
    @endif

    @if($activeTab === 'tareas')
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Tareas relacionadas
                </h2>
                <a
                    href="{{ route('tareas.create', $tareaParams) }}"
                    class="text-xs font-medium text-[#9d1872] hover:underline"
                >
                    + Nueva tarea
                </a>
            </div>

            @if($tareas->isEmpty())
                <p class="text-sm text-slate-500">
                    Esta factura todavía no tiene tareas asociadas.
                </p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($tareas as $tarea)
                        <li class="flex items-start justify-between gap-3 py-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs uppercase tracking-wide text-slate-400">
                                        {{ ucfirst($tarea->tipo) }}
                                    </span>

                                    @if($tarea->estado === 'completada')
                                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] text-emerald-700">
                                            Completada
                                        </span>
                                    @elseif($tarea->estado === 'en_proceso')
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] text-amber-700">
                                            En proceso
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700">
                                            Pendiente
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm font-medium text-slate-900">
                                    <a href="{{ route('tareas.edit', $tarea) }}" class="hover:text-[#9d1872]">
                                        {{ $tarea->titulo }}
                                    </a>
                                </p>

                                @if($tarea->descripcion)
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($tarea->descripcion, 120) }}
                                    </p>
                                @endif
                            </div>

                            <div class="space-y-1 text-right text-xs text-slate-500">
                                @if($tarea->fecha_vencimiento)
                                    <div>
                                        Vence:
                                        <span class="font-medium text-slate-700">
                                            {{ $tarea->fecha_vencimiento->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @endif

                                @if($tarea->owner)
                                    <div>Propietario: {{ $tarea->owner->name }}</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    @if($activeTab === 'documentos')
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Documentos relacionados
                </h2>
                <a
                    href="{{ route('documentos.create', $documentoParams) }}"
                    class="text-xs font-medium text-[#9d1872] hover:underline"
                >
                    + Nuevo documento
                </a>
            </div>

            @if($documentos->isEmpty())
                <p class="text-sm text-slate-500">
                    Esta factura todavía no tiene documentos asociados.
                </p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($documentos as $documento)
                        <li class="flex items-start justify-between gap-3 py-2">
                            <div>
                                <p class="text-sm font-medium text-slate-900">
                                    <a href="{{ route('documentos.download', $documento) }}"
                                    class="hover:text-[#9d1872]">
                                        {{ $documento->titulo }}
                                    </a>
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $documento->tipo ?: 'Documento' }}
                                    @if($documento->fecha_documento)
                                        · {{ $documento->fecha_documento->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>

                            <div class="text-right text-xs text-slate-500 space-y-1">
                                <div>{{ $documento->nombre_original ?: 'Fichero' }}</div>
                                <form method="POST" action="{{ route('documentos.destroy', $documento) }}"
                                    onsubmit="return confirm('¿Eliminar este documento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-[11px] text-rose-600 hover:underline"
                                    >
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    @if($activeTab === 'sistema')
        @php
            $formatValue = function($value) {
                if (is_null($value)) return '—';
                if (is_bool($value)) return $value ? '1' : '0';
                if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
                return (string) $value;
            };
        @endphp

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Historial de cambios
                    </h2>

                    @if($logEntries->isNotEmpty())
                        <ul class="divide-y divide-slate-100 text-xs">
                            @foreach($logEntries as $entry)
                                <li class="flex items-start justify-between gap-3 py-2">
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $entry['field'] }}</div>
                                        <div class="mt-0.5 text-slate-500">
                                            {{ $formatValue($entry['old_value']) }} → {{ $formatValue($entry['new_value']) }}
                                        </div>
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
                    @else
                        <p class="text-xs text-slate-500">
                            Todavía no hay cambios registrados en esta factura.
                        </p>
                    @endif
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Información del sistema
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Fecha de alta</dt>
                            <dd class="text-slate-900">
                                {{ $factura->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Última actualización</dt>
                            <dd class="text-slate-900">
                                {{ $factura->updated_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    @endif
</div>
@endsection