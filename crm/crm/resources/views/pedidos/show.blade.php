@extends('layouts.app')

@section('title', $pedido->numero ?: $pedido->descripcion ?: 'Pedido')

@section('content')
@php
    $activeTab = request('tab', 'resumen');
    $tabs = [
        'resumen'    => 'Resumen',
        'tareas'     => 'Tareas',
        'facturas'   => 'Facturas',
        'documentos' => 'Documentos',
        'sistema'    => 'Información del sistema',
    ];
    
    if ($pedido->es_formacion) {
        $tabs = array_slice($tabs, 0, 2, true)
            + ['docentes' => 'Docentes']
            + array_slice($tabs, 2, null, true);
    }

    $tabs['tareas'] = 'Tareas (' . $pedido->tareas->count() . ')';
    $tabs['facturas'] = 'Facturas (' . $pedido->facturas->count() . ')';
    $tabs['documentos'] = 'Documentos (' . $pedido->documentos->count() . ')';

    // NUEVO: colecciones seguras (para no depender de que existan variables)
    $documentosPedido = $documentosPedido
        ?? ($pedido->relationLoaded('documentos') ? ($pedido->documentos ?? collect()) : collect());

    $documentosCuenta = $documentosCuenta ?? collect();
@endphp

<div
    x-data="{
        abrirModalFactura: false,
        todasSeleccionadas: true,
        totalPendiente: @js($totalPendiente),
        lineas: @js($pedido->lineas->map(fn ($linea) => [
            'id' => $linea->id,
            'referencia' => $linea->referencia,
            'descripcion' => $linea->descripcion,
            'importe' => (float) $linea->importe_con_iva,
        ])->values()),
        seleccionadas: {},
        anticipoTipo: '',
        anticipoValor: '',
        aplicarAnticipo: true,
        popupOpen: false,
        popupMessage: '',
        showPopup(message) {
            this.popupMessage = message;
            this.popupOpen = true;
        },
        openModalFactura() {
            if (Number(this.totalPendiente) <= 0) {
                this.showPopup('Este pedido ya está completamente facturado.');
                return;
            }
            this.abrirModalFactura = true;
        },
        toggleTodas() {
            const nuevo = !this.todasSeleccionadas;
            this.todasSeleccionadas = nuevo;
            this.lineas.forEach(l => { this.seleccionadas[l.id] = nuevo; });
        }
    }"
    x-init="
        lineas.forEach(l => { seleccionadas[l.id] = true; });
        const popupMessage = @js(session('popup_alert'));
        if (popupMessage) { showPopup(popupMessage); }
    "
    class="space-y-6"
>
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ $pedido->numero ?: 'Pedido '.$pedido->id }}
                </h1>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                    Estado: {{ ucfirst($pedido->estado_pedido) }}
                </span>
            </div>
            <p class="text-sm text-slate-600">
                {{ $pedido->descripcion ?: 'Sin descripción' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                @click="openModalFactura()"
            >
                Crear factura
            </button>
            <a
                href="{{ route('tareas.create', ['pedido_id' => $pedido->id, 'account_id' => $pedido->account_id]) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
            >
                Nueva tarea
            </a>
            <a
                href="{{ route('pedidos.edit', $pedido) }}"
                class="inline-flex items-center rounded-lg bg-[#9d1872] px-3 py-2 text-sm font-medium text-white shadow hover:bg-[#7b1459]"
            >
                Editar
            </a>
            <form method="POST" action="{{ route('pedidos.destroy', $pedido) }}"
                  onsubmit="return confirm('¿Seguro que quieres eliminar este pedido?');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg border border-rose-200 px-3 py-2 text-sm text-rose-600 hover:bg-rose-50"
                >
                    Eliminar
                </button>
            </form>
        </div>
    </header>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total pedido</p>
            <p class="mt-2 text-xl font-semibold text-slate-900">
                {{ number_format($totalPedido, 2, ',', '.') }} {{ $pedido->moneda }}
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total facturado</p>
            <p class="mt-2 text-xl font-semibold text-emerald-700">
                {{ number_format($totalFacturado, 2, ',', '.') }} {{ $pedido->moneda }}
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pendiente por facturar</p>
            <p class="mt-2 text-xl font-semibold text-rose-600">
                {{ number_format($totalPendiente, 2, ',', '.') }} {{ $pedido->moneda }}
            </p>
        </div>
    </div>

    <nav class="mt-2 border-b border-slate-200">
        <div class="-mb-px flex flex-wrap gap-4 text-sm">
            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('pedidos.show', $pedido) }}?tab={{ $key }}"
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
        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr),minmax(260px,1.3fr)]">
            <section class="rounded-2xl bg-white p-5 shadow-sm space-y-4">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Datos del pedido
                </h2>
                <dl class="mt-3 space-y-3 text-sm">
                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Número</dt>
                        <dd class="flex-1">{{ $pedido->numero ?: '—' }}</dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Fecha pedido</dt>
                        <dd class="flex-1">
                            {{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y') : '—' }}
                        </dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Cuenta</dt>
                        <dd class="flex-1">
                            @if ($pedido->cuenta)
                                <a href="{{ route('accounts.show', $pedido->cuenta) }}" class="text-[#9d1872] hover:underline">
                                    {{ $pedido->cuenta->name }}
                                </a>
                            @else
                                <span class="text-slate-400">Sin cuenta</span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Contacto</dt>
                        <dd class="flex-1">
                            @if ($pedido->contacto)
                                <a href="{{ route('contacts.show', $pedido->contacto) }}" class="text-[#9d1872] hover:underline">
                                    {{ $pedido->contacto->name ?? ($pedido->contacto->first_name.' '.$pedido->contacto->last_name) }}
                                </a>
                            @else
                                <span class="text-slate-400">Sin contacto</span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Estado</dt>
                        <dd class="flex-1">{{ ucfirst($pedido->estado_pedido) }}</dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Año</dt>
                        <dd class="flex-1">{{ $pedido->anio ?: '—' }}</dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Forma de pago</dt>
                        <dd class="flex-1">{{ $pedido->forma_pago ?: '—' }}</dd>
                    </div>

                    <div class="flex gap-4">
                        <dt class="w-32 text-xs font-medium text-slate-500">Petición origen</dt>
                        <dd class="flex-1">
                            @if ($pedido->peticion)
                                <a href="{{ route('peticiones.show', $pedido->peticion) }}" class="text-[#9d1872] hover:underline">
                                    {{ $pedido->peticion->titulo }}
                                </a>
                            @else
                                <span class="text-slate-400">No vinculada</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($pedido->info_adicional)
                    <div class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-700">
                        <h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Información adicional
                        </h3>
                        <p class="whitespace-pre-line">
                            {{ $pedido->info_adicional }}
                        </p>
                    </div>
                @endif
            </section>

            <section class="rounded-2xl bg-white p-5 shadow-sm space-y-4">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Resumen rápido
                </h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    <li><strong>Estado:</strong> {{ ucfirst($pedido->estado_pedido) }}</li>
                    <li><strong>Importe total:</strong>
                        {{ $pedido->importe_total ? number_format($pedido->importe_total, 2, ',', '.') . ' ' . $pedido->moneda : '—' }}
                    </li>
                    <li><strong>Proyecto justificado:</strong> {{ $pedido->proyecto_justificado ? 'Sí' : 'No' }}</li>
                    <li><strong>Pedido de formación:</strong> {{ $pedido->es_formacion ? 'Sí' : 'No' }}</li>
                    <li><strong>Email facturación:</strong> {{ $pedido->email_facturacion ?: '—' }}</li>
                </ul>

                <div class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-700">
                    <h3 class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Facturación
                    </h3>
                    <p class="whitespace-pre-line">
                        {{ $pedido->info_facturacion ?: 'Sin información específica.' }}
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        Primer plazo: {{ $pedido->facturar_primer_plazo ? 'Sí' : 'No' }} ·
                        Segundo plazo: {{ $pedido->facturar_segundo_plazo ? 'Sí' : 'No' }}
                    </p>
                </div>
            </section>
        </div>

        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Líneas de servicios</h2>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Referencia</th>
                            <th class="px-3 py-2 text-left">Descripción</th>
                            <th class="px-3 py-2 text-right">Cantidad</th>
                            <th class="px-3 py-2 text-right">Precio</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                            <th class="px-3 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($pedido->lineas as $linea)
                            <tr>
                                <td class="px-3 py-2 text-slate-700">{{ $linea->referencia ?: '—' }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $linea->descripcion ?: '—' }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format($linea->cantidad, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ number_format($linea->precio, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $linea->subtotal !== null ? number_format($linea->subtotal, 2, ',', '.') : '—' }}</td>
                                <td class="px-3 py-2 text-right text-slate-700">{{ $linea->importe_con_iva !== null ? number_format($linea->importe_con_iva, 2, ',', '.') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-xs text-slate-500">
                                    No hay líneas registradas en este pedido.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($activeTab === 'tareas')
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Tareas relacionadas
                </h2>
                <a
                    href="{{ route('tareas.create', [
                        'pedido_id' => $pedido->id,
                        'account_id' => $pedido->account_id,
                    ]) }}"
                    class="text-xs font-medium text-[#9d1872] hover:underline"
                >
                    + Nueva tarea
                </a>
            </div>

            @if($pedido->tareas->isEmpty())
                <p class="text-sm text-slate-500">
                    Este pedido todavía no tiene tareas asociadas.
                </p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($pedido->tareas as $tarea)
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

    @if($activeTab === 'facturas')
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Facturas relacionadas
                </h2>
                <a
                    href="#"
                    @click.prevent="openModalFactura()"
                    class="text-xs font-medium text-[#9d1872] hover:underline"
                >
                    + Nueva factura
                </a>
            </div>

            @if($pedido->facturas->isEmpty())
                <p class="mt-3 text-sm text-slate-500">
                    Aún no hay facturas vinculadas a este pedido.
                </p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach($pedido->facturas as $factura)
                        <li class="flex items-start justify-between gap-3 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">
                                    <a href="{{ route('facturas.show', $factura) }}" class="hover:text-[#9d1872]">
                                        {{ $factura->numero ?: 'Factura '.$factura->id }}
                                    </a>
                                </p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $factura->fecha_factura?->format('d/m/Y') ?? 'Sin fecha' }}
                                    · {{ $factura->importe_total !== null ? number_format($factura->importe_total, 2, ',', '.') . ' ' . $factura->moneda : '—' }}
                                </p>
                            </div>
                            <div class="text-right text-xs text-slate-500 space-y-1">
                                <a href="{{ route('facturas.pdf', $factura) }}" class="text-[11px] text-[#9d1872] hover:underline">
                                    Ver PDF
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    @if($activeTab === 'docentes')
        <section class="rounded-2xl bg-white p-5 shadow-sm space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Docentes vinculados
                </h2>
                <a
                    href="{{ route('docentes.calendario.index') }}"
                    class="text-xs font-medium text-[#9d1872] hover:underline"
                >
                    Ver calendario global
                </a>
            </div>

            <form method="POST" action="{{ route('pedidos.docentes.sync', $pedido) }}" class="space-y-4">
                @csrf

                @if($docentesDisponibles->isEmpty())
                    <p class="text-sm text-slate-500">
                        No hay docentes disponibles con rol docente.
                    </p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($docentesDisponibles as $docente)
                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 text-sm text-slate-700 hover:border-[#9d1872]">
                                <input
                                    type="checkbox"
                                    name="docentes[]"
                                    value="{{ $docente->id }}"
                                    @checked($pedido->docentes->contains($docente->id))
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]"
                                >
                                <span class="space-y-1">
                                    <span class="block font-medium text-slate-900">{{ $docente->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $docente->email }}</span>
                                    <a href="{{ route('docentes.calendario.show', $docente) }}" class="text-xs text-[#9d1872] hover:underline">
                                        Ver calendario
                                    </a>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]"
                        >
                            Guardar docentes
                        </button>
                    </div>
                @endif
            </form>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Horario de formación</h3>
                <form method="POST" action="{{ route('pedidos.docentes.horarios.store', $pedido) }}" class="mt-4 grid gap-4 md:grid-cols-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Docente</label>
                        <select name="user_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                            <option value="">Seleccionar</option>
                            @foreach($pedido->docentes as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Inicio</label>
                        <input type="datetime-local" name="inicio" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Fin</label>
                        <input type="datetime-local" name="fin" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Nota</label>
                        <input type="text" name="nota" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit" class="rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]">
                            Añadir horario
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Calendario</h3>
                <div id="pedido-docentes-calendar" class="mt-4 min-h-[500px]"></div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bloques asignados</h3>
                @if($pedido->docenteHorarios->isEmpty())
                    <p class="mt-2 text-sm text-slate-500">No hay horarios asignados.</p>
                @else
                    <ul class="mt-3 divide-y divide-slate-100 text-sm">
                        @foreach($pedido->docenteHorarios as $horario)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-2">
                                <div>
                                    <span class="font-medium text-slate-900">
                                        {{ $horario->docente?->name ?? 'Docente' }}
                                    </span>
                                    <span class="block text-xs text-slate-500">
                                        {{ $horario->inicio->format('d/m/Y H:i') }} → {{ $horario->fin->format('d/m/Y H:i') }}
                                    </span>
                                    @if($horario->nota)
                                        <span class="block text-xs text-slate-500">{{ $horario->nota }}</span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('pedidos.docentes.horarios.destroy', [$pedido, $horario]) }}" onsubmit="return confirm('¿Eliminar este horario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[11px] text-rose-600 hover:underline">Eliminar</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endif


    @if($activeTab === 'documentos')
        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Documentos relacionados
                </h2>
                <a
                    href="{{ route('documentos.create', [
                        'pedido_id'  => $pedido->id,
                        'account_id' => $pedido->account_id,
                    ]) }}"
                    class="text-xs font-medium text-[#9d1872] hover:underline"
                >
                    + Nuevo documento
                </a>
            </div>

            {{-- NUEVO: si no hay ninguno (pedido + cuenta) --}}
            @if($documentosPedido->isEmpty() && $documentosCuenta->isEmpty())
                <p class="text-sm text-slate-500">
                    Este pedido todavía no tiene documentos asociados.
                </p>
            @else
                {{-- Documentos del pedido --}}
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Documentos del pedido
                    </h3>

                    @if($documentosPedido->isEmpty())
                        <p class="text-sm text-slate-500">
                            No hay documentos vinculados directamente a este pedido.
                        </p>
                    @else
                        <ul class="divide-y divide-slate-100 text-sm">
                            @foreach($documentosPedido as $documento)
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
                </div>

                {{-- Documentos generales de la cuenta --}}
                <div class="mt-6 space-y-3 border-t border-slate-100 pt-5">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Documentos generales de la cuenta
                        @if($pedido->cuenta)
                            <span class="normal-case text-slate-400 font-normal">({{ $pedido->cuenta->name }})</span>
                        @endif
                    </h3>

                    @if($documentosCuenta->isEmpty())
                        <p class="text-sm text-slate-500">
                            No hay documentos generales en la cuenta.
                        </p>
                    @else
                        <ul class="divide-y divide-slate-100 text-sm">
                            @foreach($documentosCuenta as $documento)
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
                </div>
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
                            Todavía no hay cambios registrados en este pedido.
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
                                {{ $pedido->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Última actualización</dt>
                            <dd class="text-slate-900">
                                {{ $pedido->updated_at?->format('d/m/Y H:i') ?? '—' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    @endif
    <div
        x-show="abrirModalFactura"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4 py-6"
        @keydown.escape.window="abrirModalFactura = false"
        @click.self="abrirModalFactura = false"
    >
        <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Crear factura</h3>
                    <p class="text-sm text-slate-500">Selecciona las líneas a facturar o crea un anticipo.</p>
                </div>
                <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" @click="abrirModalFactura = false">
                    ✕
                </button>
            </div>
            <form method="GET" action="{{ route('facturas.create') }}" class="space-y-6 px-6 py-5">
                <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Líneas del pedido</h4>
                        <button type="button" class="text-xs font-semibold text-[#9d1872] hover:underline" @click="toggleTodas()">
                            <span x-text="todasSeleccionadas ? 'Deseleccionar todo' : 'Seleccionar todo'"></span>
                        </button>
                    </div>
                    <div class="max-h-60 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 text-sm">
                        <template x-for="linea in lineas" :key="linea.id">
                            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 hover:border-[#9d1872]">
                                <input type="checkbox" :name="'lineas[]'" :value="linea.id" x-model="seleccionadas[linea.id]" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                                <span>
                                    <span class="block font-medium text-slate-900" x-text="linea.referencia || 'Línea'"></span>
                                    <span class="block text-xs text-slate-500" x-text="linea.descripcion"></span>
                                    <span class="block text-xs text-slate-500" x-text="`${linea.importe.toFixed(2)} €`"></span>
                                </span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Anticipo</h4>
                        <select name="anticipo_tipo" x-model="anticipoTipo" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]">
                            <option value="">Sin anticipo</option>
                            <option value="importe">Importe total (sin IVA adicional)</option>
                            <option value="porcentaje">Porcentaje sobre subtotal (aplica IVA)</option>
                        </select>
                        <input
                            type="number"
                            step="0.01"
                            name="anticipo_valor"
                            x-model="anticipoValor"
                            placeholder="Importe o porcentaje"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-[#9d1872] focus:outline-none focus:ring-1 focus:ring-[#9d1872]"
                        >
                        <p class="text-xs text-slate-500">El anticipo se repartirá entre las líneas seleccionadas.</p>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aplicar anticipos previos</h4>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="apply_anticipo" value="1" x-model="aplicarAnticipo" class="h-4 w-4 rounded border-slate-300 text-[#9d1872] focus:ring-[#9d1872]">
                            Restar anticipos ya facturados en este pedido.
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50" @click="abrirModalFactura = false">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]">
                        Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        x-show="popupOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4 py-6"
        @keydown.escape.window="popupOpen = false"
        @click.self="popupOpen = false"
    >
        <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-semibold text-slate-900">Aviso</h3>
                <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" @click="popupOpen = false">
                    ✕
                </button>
            </div>
            <div class="px-6 py-5 text-sm text-slate-700" x-text="popupMessage"></div>
            <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                <button type="button" class="rounded-lg bg-[#9d1872] px-4 py-2 text-xs font-semibold text-white hover:bg-[#86145f]" @click="popupOpen = false">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('pedido-docentes-calendar');
        if (!calendarEl) return;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 'auto',
            firstDay: 1,
            events: @json($docenteEventos),
        });

        calendar.render();
    });
</script>
@endsection
