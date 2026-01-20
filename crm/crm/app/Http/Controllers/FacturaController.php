<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacturaRequest;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Documento;
use App\Models\Factura;
use App\Models\FacturaSerie;
use App\Models\FacturaView;
use App\Models\Impuesto;
use App\Models\Pedido;
use App\Models\PedidoLinea;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacturaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', '')) ?: null;
        $advanced = $request->query('af');

        $availableColumns = [
            'numero'         => 'Nº Factura',
            'fecha_factura'  => 'Fecha factura',
            'cliente'        => 'Cliente',
            'descripcion'    => 'Descripción',
            'importe'        => 'Importe',
            'importe_total'  => 'Importe total',
            'vencimiento'    => 'Vencimiento',
            'fecha_cobro'    => 'Fecha cobro',
            'cobrado'        => 'Cobrado',
            'contabilizado'  => 'Contabilizado',
        ];

        $defaultColumns = array_keys($availableColumns);

        $views = FacturaView::query()
            ->where('user_id', optional($request->user())->id)
            ->orderBy('name')
            ->get();

        $activeView = null;
        $vistaId    = $request->query('vista_id');

        if ($vistaId && $views->isNotEmpty()) {
            $activeView = $views->firstWhere('id', (int) $vistaId);
        } elseif ($views->isNotEmpty()) {
            $activeView = $views->firstWhere('is_default', true);
        }

        if (! $q && ! $advanced && $activeView && is_array($activeView->filters)) {
            $q = $activeView->filters['q'] ?? null;
            $advanced = $activeView->filters['af'] ?? null;
        }

        if ($request->filled('columns')) {
            $requestedColumns = array_filter((array) $request->input('columns', []));
            $activeColumnKeys = array_values(
                array_intersect($requestedColumns, array_keys($availableColumns))
            );
        } elseif ($activeView && is_array($activeView->columns)) {
            $activeColumnKeys = array_values(
                array_intersect($activeView->columns, array_keys($availableColumns))
            );
        } else {
            $activeColumnKeys = $defaultColumns;
        }

        if (empty($activeColumnKeys)) {
            $activeColumnKeys = $defaultColumns;
        }

        $facturasQuery = Factura::query()
            ->with(['cuenta:id,name', 'pedido:id,numero'])
            ->when($q, function ($query) use ($q) {
                $query->where('numero', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%")
                    ->orWhereHas('cuenta', fn ($sub) => $sub->where('name', 'like', "%{$q}%"));
            })
            ->orderByDesc('fecha_factura')
            ->orderByDesc('id');

        $facturas = $this->applyAdvancedFilters($facturasQuery, $advanced)
            ->paginate(25)
            ->withQueryString();

        return view('facturas.index', [
            'facturas' => $facturas,
            'filters' => [
                'q' => $q,
                'af' => $advanced,
            ],
            'availableColumns' => $availableColumns,
            'activeColumnKeys' => $activeColumnKeys,
            'views'            => $views,
            'activeView'       => $activeView,
            'sortColumn'       => 'fecha_factura',
            'sortDirection'    => 'desc',
        ]);
    }

    public function create(Request $request): View
    {
        $pedido = null;
        $lineasPreset = [];
        $anticipoSeleccionado = [];
        $applyAnticipo = $request->boolean('apply_anticipo');

        if ($request->filled('pedido_id')) {
            $pedido = Pedido::query()
                ->with(['lineas', 'facturas'])
                ->find($request->query('pedido_id'));

            if ($pedido) {
                $totalPedido = (float) ($pedido->importe_total ?? 0);
                $totalFacturado = (float) $pedido->facturas->sum('importe_total');
                $totalPendiente = $totalPedido - $totalFacturado;

                if ($totalPendiente <= 0) {
                    return redirect()
                        ->route('pedidos.show', $pedido)
                        ->with('popup_alert', 'Este pedido ya está completamente facturado.');
                }
            }
        }

        $cuentas = Account::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $pedidos = Pedido::query()
            ->orderByDesc('fecha_pedido')
            ->get(['id', 'numero']);

        $categories = ServiceCategory::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'parent_id']);

        $services = Service::query()
            ->with('category:id,nombre')
            ->where('estado', '!=', 'inactivo')
            ->orderBy('referencia')
            ->get(['id', 'referencia', 'descripcion', 'precio', 'service_category_id']);

        if ($pedido) {
            $lineasPedido = $pedido->lineas;
            $seleccionadas = collect((array) $request->query('lineas', []))
                ->map(fn ($id) => (int) $id)
                ->filter();

            if ($seleccionadas->isNotEmpty()) {
                $lineasPedido = $lineasPedido->whereIn('id', $seleccionadas);
            }

            $lineasPreset = $lineasPedido->map(function ($linea) {
                return [
                    'service_id' => $linea->service_id,
                    'referencia' => $linea->referencia,
                    'concepto' => $linea->descripcion,
                    'cantidad' => (float) $linea->cantidad,
                    'precio' => (float) $linea->precio,
                    'descuento_porcentaje' => (float) $linea->descuento_porcentaje,
                    'iva_porcentaje' => (float) $linea->iva_porcentaje,
                ];
            })->values()->toArray();

            $tipoAnticipo = $request->query('anticipo_tipo');
            $valorAnticipo = (float) $request->query('anticipo_valor', 0);

            if ($tipoAnticipo && $valorAnticipo > 0) {
                $baseSubtotal = $lineasPedido->sum(function ($linea) {
                    if ($linea->subtotal !== null) {
                        return (float) $linea->subtotal;
                    }
                    $cantidad = (float) ($linea->cantidad ?? 0);
                    $precio = (float) ($linea->precio ?? 0);
                    $dto = (float) ($linea->descuento_porcentaje ?? 0);
                    return $cantidad * $precio * (1 - ($dto / 100));
                });

                if ($tipoAnticipo === 'porcentaje') {
                    $base = $baseSubtotal * ($valorAnticipo / 100);
                } else {
                    $base = $valorAnticipo;
                }

                $count = max($lineasPedido->count(), 1);
                $importePorLinea = round($base / $count, 2);
                $ivaPorDefecto = $tipoAnticipo === 'porcentaje' ? null : 0;

                $lineasPreset = $lineasPedido->map(function ($linea) use ($importePorLinea, $ivaPorDefecto) {
                    return [
                        'service_id' => $linea->service_id,
                        'referencia' => $linea->referencia,
                        'concepto' => 'Anticipo · ' . ($linea->descripcion ?: 'Línea'),
                        'cantidad' => 1,
                        'precio' => $importePorLinea,
                        'descuento_porcentaje' => 0,
                        'iva_porcentaje' => $ivaPorDefecto ?? (float) $linea->iva_porcentaje,
                    ];
                })->values()->toArray();
            }

            if ($applyAnticipo) {
                $anticipoSeleccionado = Factura::query()
                    ->where('pedido_id', $pedido->id)
                    ->with('lineas')
                    ->get()
                    ->flatMap(fn ($factura) => $factura->lineas)
                    ->filter(fn ($linea) => str_starts_with((string) $linea->concepto, 'Anticipo'));

                $totalAnticipo = round((float) $anticipoSeleccionado->sum('importe'), 2);
                if ($totalAnticipo > 0 && ! empty($lineasPreset)) {
                    // Se añade como línea negativa para descontar el anticipo ya facturado
                    $lineasPreset[] = [
                        'service_id' => null,
                        'referencia' => null,
                        'concepto' => 'Anticipo aplicado',
                        'cantidad' => 1,
                        'precio' => -1 * $totalAnticipo,
                        'descuento_porcentaje' => 0,
                        'iva_porcentaje' => 0,
                    ];
                }
            }
        }

        $factura = new Factura([
            'account_id' => $pedido?->account_id,
            'pedido_id' => $pedido?->id,
        ]);

        return view('facturas.create', [
            'cuentas' => $cuentas,
            'pedidos' => $pedidos,
            'services' => $services,
            'categories' => $categories,
            'pedido' => $pedido,
            'factura' => $factura,
            'lineasPreset' => $lineasPreset,
        ]);
    }

    public function store(FacturaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $idempotencyKey = $request->input('idempotency_key');
        if ($idempotencyKey) {
            $existingFactura = Factura::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existingFactura) {
                return redirect()
                    ->route('facturas.show', $existingFactura)
                    ->with('status', 'Factura ya creada. Se ha evitado un duplicado.');
            }
        }

        $lineas = collect($request->input('lineas', []));
        $hasLineas = $lineas->contains(function ($linea) {
            $concepto = $linea['concepto'] ?? '';
            $precio = (float) ($linea['precio'] ?? 0);
            return trim((string) $concepto) !== '' || $precio != 0.0 || ! empty($linea['service_id']);
        });

        if (! $hasLineas) {
            throw ValidationException::withMessages([
                'lineas' => 'Debes añadir al menos una línea para crear la factura.',
            ]);
        }

        try {
            $factura = DB::transaction(function () use ($data, $request, $idempotencyKey) {
                if ($request->filled('pedido_id') && empty($data['numero'])) {
                    $data['numero'] = null;
                }

                $data['estado'] = $data['estado'] ?? Factura::ESTADO_BORRADOR;
                $data['tipo'] = $data['tipo'] ?? Factura::TIPO_NORMAL;
                $data['payment_state'] = $data['payment_state'] ?? Factura::PAYMENT_PENDIENTE;

                if ($idempotencyKey) {
                    $data['idempotency_key'] = $idempotencyKey;
                }

                /** @var Factura $factura */
                $factura = Factura::create(collect($data)->except('lineas')->all());

                $sumSubtotal = 0;
                $sumTotal = 0;
                $lineasCreadas = 0;

                $lineas = collect($request->input('lineas', []));
                $impuestosTotales = [];

                foreach ($lineas as $idx => $l) {
                    $serviceId = $l['service_id'] ?? null;
                    $service = $serviceId ? Service::find($serviceId) : null;

                    $conceptoLinea = $l['concepto'] ?? $service?->descripcion ?? '';
                    $precioLinea = $l['precio'] ?? $service?->precio ?? 0;

                    $hasContent = $service || trim((string) $conceptoLinea) !== '' || (float) $precioLinea != 0.0;
                    if (! $hasContent) {
                        continue;
                    }

                    $cantidad = (float) ($l['cantidad'] ?? 0);
                    $precio = (float) $precioLinea;
                    $dto = (float) ($l['descuento_porcentaje'] ?? 0);
                    $iva = (float) ($l['iva_porcentaje'] ?? 21);

                    // Permitimos una línea negativa solo para "Anticipo aplicado" en facturas normales.
                    $isAnticipoAplicado = str_starts_with(trim((string) $conceptoLinea), 'Anticipo aplicado');
                    if ($precio < 0 && $factura->tipo !== Factura::TIPO_RECTIFICATIVA && ! $isAnticipoAplicado) {
                        throw ValidationException::withMessages([
                            "lineas.{$idx}.precio" => 'No se permiten importes negativos en facturas normales.',
                        ]);
                    }

                    $subtotal = $cantidad * $precio * (1 - ($dto / 100));
                    $importe = $subtotal * (1 + ($iva / 100));

                    $facturaLinea = $factura->lineas()->create([
                        'service_id' => $service?->id,
                        'referencia' => $l['referencia'] ?? $service?->referencia,
                        'concepto' => $conceptoLinea,
                        'cantidad' => $cantidad,
                        'precio' => $precio,
                        'descuento_porcentaje' => $dto,
                        'iva_porcentaje' => $iva,
                        'subtotal' => $subtotal,
                        'importe' => $importe,
                        'orden' => $idx,
                    ]);
                    $lineasCreadas++;

                    $pedidoLineaId = $l['pedido_linea_id'] ?? null;
                    if ($pedidoLineaId) {
                        $pedidoLinea = PedidoLinea::find($pedidoLineaId);
                        if ($pedidoLinea) {
                            if ($factura->pedido_id && $pedidoLinea->pedido_id !== $factura->pedido_id) {
                                throw ValidationException::withMessages([
                                    "lineas.{$idx}.pedido_linea_id" => 'La línea no pertenece al pedido seleccionado.',
                                ]);
                            }

                            $pendiente = max(0, (float) $pedidoLinea->cantidad - (float) $pedidoLinea->qty_facturada);
                            if ($cantidad > $pendiente) {
                                throw ValidationException::withMessages([
                                    "lineas.{$idx}.cantidad" => 'No puedes facturar más cantidad de la pendiente.',
                                ]);
                            }

                            $baseAsignacion = $subtotal;
                            $importeAsignacion = $importe;

                            $facturaLinea->asignaciones()->create([
                                'pedido_linea_id' => $pedidoLinea->id,
                                'cantidad' => $cantidad,
                                'base' => $baseAsignacion,
                                'importe' => $importeAsignacion,
                            ]);

                            $pedidoLinea->update([
                                'qty_facturada' => (float) $pedidoLinea->qty_facturada + $cantidad,
                                'base_facturada' => (float) $pedidoLinea->base_facturada + $baseAsignacion,
                                'importe_facturado' => (float) $pedidoLinea->importe_facturado + $importeAsignacion,
                            ]);
                        }
                    }

                    $impuestosLinea = collect($l['impuestos'] ?? []);
                    if ($impuestosLinea->isEmpty() && $service) {
                        $impuestosLinea = $service->impuestos->pluck('id');
                    }

                    foreach ($impuestosLinea as $impuestoId) {
                        $impuesto = Impuesto::find($impuestoId);
                        if (! $impuesto) {
                            continue;
                        }

                        $importeImpuesto = $subtotal * ((float) $impuesto->porcentaje / 100);

                        $facturaLinea->impuestos()->attach($impuesto->id, [
                            'base' => $subtotal,
                            'importe' => $importeImpuesto,
                        ]);

                        $impuestosTotales[$impuesto->id]['base'] = ($impuestosTotales[$impuesto->id]['base'] ?? 0) + $subtotal;
                        $impuestosTotales[$impuesto->id]['importe'] = ($impuestosTotales[$impuesto->id]['importe'] ?? 0) + $importeImpuesto;
                    }

                    $sumSubtotal += $subtotal;
                    $sumTotal += $importe;
                }

                if ($lineasCreadas === 0) {
                    throw ValidationException::withMessages([
                        'lineas' => 'Debes añadir al menos una línea para crear la factura.',
                    ]);
                }

                $factura->update([
                    'importe' => $sumSubtotal - ($data['descuento_global'] ?? 0),
                    'importe_total' => $sumTotal - ($data['descuento_global'] ?? 0) + ($data['redondeo'] ?? 0),
                    'moneda' => $data['moneda'] ?? 'EUR',
                ]);

                foreach ($impuestosTotales as $impuestoId => $totales) {
                    DB::table('factura_impuesto_totales')->updateOrInsert(
                        ['factura_id' => $factura->id, 'impuesto_id' => $impuestoId],
                        ['base' => $totales['base'], 'importe' => $totales['importe'], 'updated_at' => now(), 'created_at' => now()]
                    );
                }

                return $factura;
            });
        } catch (UniqueConstraintViolationException $exception) {
            if ($idempotencyKey) {
                $existingFactura = Factura::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existingFactura) {
                    return redirect()
                        ->route('facturas.show', $existingFactura)
                        ->with('status', 'Factura ya creada. Se ha evitado un duplicado.');
                }
            }
            throw $exception;
        } catch (QueryException $exception) {
            if ($idempotencyKey) {
                $existingFactura = Factura::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existingFactura) {
                    return redirect()
                        ->route('facturas.show', $existingFactura)
                        ->with('status', 'Factura ya creada. Se ha evitado un duplicado.');
                }
            }
            throw $exception;
        }

        AuditLog::create([
            'user_id' => optional($request->user())->id,
            'model_type' => Factura::class,
            'model_id' => $factura->id,
            'event' => 'created',
            'old_values' => [],
            'new_values' => $factura->fresh()->getAttributes(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('facturas.show', $factura)
            ->with('status', 'Factura creada correctamente.');
    }

    public function edit(Factura $factura): View
    {
        if (! $factura->isEditable()) {
            abort(403, 'La factura no se puede editar una vez publicada o cancelada.');
        }

        $factura->load(['lineas' => fn ($q) => $q->orderBy('orden')]);

        $cuentas = Account::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $pedidos = Pedido::query()
            ->orderByDesc('fecha_pedido')
            ->get(['id', 'numero']);

        $categories = ServiceCategory::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'parent_id']);

        $services = Service::query()
            ->with('category:id,nombre')
            ->where('estado', '!=', 'inactivo')
            ->orderBy('referencia')
            ->get(['id', 'referencia', 'descripcion', 'precio', 'service_category_id']);

        return view('facturas.edit', [
            'factura' => $factura,
            'cuentas' => $cuentas,
            'pedidos' => $pedidos,
            'services' => $services,
            'categories' => $categories,
        ]);
    }

    public function update(FacturaRequest $request, Factura $factura): RedirectResponse
    {
        if (! $factura->isEditable()) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('status', 'La factura no se puede editar una vez publicada o cancelada.');
        }

        $data = $request->validated();
        $oldValues = $factura->getOriginal();

        DB::transaction(function () use ($data, $request, $factura) {
            $factura->update(collect($data)->except('lineas')->all());

            $factura->load('lineas.asignaciones');
            foreach ($factura->lineas as $lineaExistente) {
                foreach ($lineaExistente->asignaciones as $asignacion) {
                    $pedidoLinea = $asignacion->pedidoLinea;
                    if (! $pedidoLinea) {
                        continue;
                    }
                    $pedidoLinea->update([
                        'qty_facturada' => max(0, (float) $pedidoLinea->qty_facturada - (float) $asignacion->cantidad),
                        'base_facturada' => max(0, (float) $pedidoLinea->base_facturada - (float) $asignacion->base),
                        'importe_facturado' => max(0, (float) $pedidoLinea->importe_facturado - (float) $asignacion->importe),
                    ]);
                }
            }

            $factura->lineas()->delete();
            DB::table('factura_impuesto_totales')->where('factura_id', $factura->id)->delete();

            $sumSubtotal = 0;
            $sumTotal = 0;
            $lineas = collect($request->input('lineas', []));
            $impuestosTotales = [];
            $lineasCreadas = 0;

            foreach ($lineas as $idx => $l) {
                $serviceId = $l['service_id'] ?? null;
                $service = $serviceId ? Service::find($serviceId) : null;

                $conceptoLinea = $l['concepto'] ?? $service?->descripcion ?? '';
                $precioLinea = $l['precio'] ?? $service?->precio ?? 0;

                $hasContent = $service || trim((string) $conceptoLinea) !== '' || (float) $precioLinea != 0.0;
                if (! $hasContent) {
                    continue;
                }

                $cantidad = (float) ($l['cantidad'] ?? 0);
                $precio = (float) $precioLinea;
                $dto = (float) ($l['descuento_porcentaje'] ?? 0);
                $iva = (float) ($l['iva_porcentaje'] ?? 21);

                $isAnticipoAplicado = str_starts_with(trim((string) $conceptoLinea), 'Anticipo aplicado');
                if ($precio < 0 && $factura->tipo !== Factura::TIPO_RECTIFICATIVA && ! $isAnticipoAplicado) {
                    throw ValidationException::withMessages([
                        "lineas.{$idx}.precio" => 'No se permiten importes negativos en facturas normales.',
                    ]);
                }

                $subtotal = $cantidad * $precio * (1 - ($dto / 100));
                $importe = $subtotal * (1 + ($iva / 100));

                $facturaLinea = $factura->lineas()->create([
                    'service_id' => $service?->id,
                    'referencia' => $l['referencia'] ?? $service?->referencia,
                    'concepto' => $conceptoLinea,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento_porcentaje' => $dto,
                    'iva_porcentaje' => $iva,
                    'subtotal' => $subtotal,
                    'importe' => $importe,
                    'orden' => $idx,
                ]);
                $lineasCreadas++;

                $pedidoLineaId = $l['pedido_linea_id'] ?? null;
                if ($pedidoLineaId) {
                    $pedidoLinea = PedidoLinea::find($pedidoLineaId);
                    if ($pedidoLinea) {
                        if ($factura->pedido_id && $pedidoLinea->pedido_id !== $factura->pedido_id) {
                            throw ValidationException::withMessages([
                                "lineas.{$idx}.pedido_linea_id" => 'La línea no pertenece al pedido seleccionado.',
                            ]);
                        }
                        $pendiente = max(0, (float) $pedidoLinea->cantidad - (float) $pedidoLinea->qty_facturada);
                        if ($cantidad > $pendiente) {
                            throw ValidationException::withMessages([
                                "lineas.{$idx}.cantidad" => 'No puedes facturar más cantidad de la pendiente.',
                            ]);
                        }
                        $baseAsignacion = $subtotal;
                        $importeAsignacion = $importe;
                        $facturaLinea->asignaciones()->create([
                            'pedido_linea_id' => $pedidoLinea->id,
                            'cantidad' => $cantidad,
                            'base' => $baseAsignacion,
                            'importe' => $importeAsignacion,
                        ]);

                        $pedidoLinea->update([
                            'qty_facturada' => (float) $pedidoLinea->qty_facturada + $cantidad,
                            'base_facturada' => (float) $pedidoLinea->base_facturada + $baseAsignacion,
                            'importe_facturado' => (float) $pedidoLinea->importe_facturado + $importeAsignacion,
                        ]);
                    }
                }

                $impuestosLinea = collect($l['impuestos'] ?? []);
                if ($impuestosLinea->isEmpty() && $service) {
                    $impuestosLinea = $service->impuestos->pluck('id');
                }

                foreach ($impuestosLinea as $impuestoId) {
                    $impuesto = Impuesto::find($impuestoId);
                    if (! $impuesto) {
                        continue;
                    }
                    $importeImpuesto = $subtotal * ((float) $impuesto->porcentaje / 100);
                    $facturaLinea->impuestos()->attach($impuesto->id, [
                        'base' => $subtotal,
                        'importe' => $importeImpuesto,
                    ]);

                    $impuestosTotales[$impuesto->id]['base'] = ($impuestosTotales[$impuesto->id]['base'] ?? 0) + $subtotal;
                    $impuestosTotales[$impuesto->id]['importe'] = ($impuestosTotales[$impuesto->id]['importe'] ?? 0) + $importeImpuesto;
                }

                $sumSubtotal += $subtotal;
                $sumTotal += $importe;
            }

            if ($lineasCreadas === 0) {
                throw ValidationException::withMessages([
                    'lineas' => 'Debes añadir al menos una línea para actualizar la factura.',
                ]);
            }

            $factura->update([
                'importe' => $sumSubtotal - ($data['descuento_global'] ?? 0),
                'importe_total' => $sumTotal - ($data['descuento_global'] ?? 0) + ($data['redondeo'] ?? 0),
            ]);

            foreach ($impuestosTotales as $impuestoId => $totales) {
                DB::table('factura_impuesto_totales')->updateOrInsert(
                    ['factura_id' => $factura->id, 'impuesto_id' => $impuestoId],
                    ['base' => $totales['base'], 'importe' => $totales['importe'], 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        $factura->refresh();

        AuditLog::create([
            'user_id' => optional($request->user())->id,
            'model_type' => Factura::class,
            'model_id' => $factura->id,
            'event' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $factura->getAttributes(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('facturas.show', $factura)
            ->with('status', 'Factura actualizada correctamente.');
    }

    public function show(Factura $factura): View
    {
        $factura->load([
            'cuenta',
            'pedido',
            'lineas' => fn ($q) => $q->orderBy('orden'),
        ]);

        $tareas = collect();
        $documentos = collect();

        if ($factura->pedido) {
            $factura->pedido->load([
                'tareas' => fn ($q) => $q->orderBy('fecha_vencimiento')->orderByDesc('created_at'),
                'tareas.owner',
                'documentos',
            ]);
            $tareas = $factura->pedido->tareas;
            $documentos = $factura->pedido->documentos;
        }

        $logs = AuditLog::query()
            ->where('model_type', Factura::class)
            ->where('model_id', $factura->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        $logEntries = $logs
            ->flatMap(function (AuditLog $log) {
                $oldValues = (array) ($log->old_values ?? []);
                $newValues = (array) ($log->new_values ?? []);

                $fields = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

                return collect($fields)
                    ->map(function (string $field) use ($oldValues, $newValues, $log) {
                        $old = $oldValues[$field] ?? null;
                        $new = $newValues[$field] ?? null;

                        if ($old == $new) {
                            return null;
                        }

                        return [
                            'field' => $field,
                            'old_value' => $old,
                            'new_value' => $new,
                            'created_at' => $log->created_at,
                            'user' => $log->user,
                        ];
                    })
                    ->filter();
            })
            ->sortByDesc('created_at')
            ->values();

        return view('facturas.show', [
            'factura' => $factura,
            'tareas' => $tareas,
            'documentos' => $documentos,
            'logs' => $logs,
            'logEntries' => $logEntries,
        ]);
    }

    public function destroy(Factura $factura): RedirectResponse
    {
        if (! $factura->isEditable()) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('status', 'La factura no se puede eliminar una vez publicada o cancelada.');
        }

        $factura->delete();

        return redirect()
            ->route('facturas.index')
            ->with('status', 'Factura eliminada.');
    }

    public function pdf(Factura $factura)
    {
        $factura->load([
            'cuenta',
            'pedido',
            'lineas' => fn ($q) => $q->orderBy('orden'),
        ]);

        try {
            $pdf = app()->make('dompdf.wrapper');
        } catch (BindingResolutionException $exception) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('status', 'PDF no disponible. Instala y registra barryvdh/laravel-dompdf.');
        }

        $pdf->loadView('facturas.pdf', [
            'factura' => $factura,
        ])->setPaper('a4');

        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
        ]);

        $fileName = 'factura-' . ($factura->numero ?: $factura->id) . '.pdf';
        $path = 'documentos/facturas/factura-' . $factura->id . '.pdf';

        Storage::disk('public')->put($path, $pdf->output());

        Documento::updateOrCreate(
            ['ruta' => $path],
            [
                'titulo' => 'Factura ' . ($factura->numero ?: $factura->id),
                'tipo' => 'Factura',
                'descripcion' => $factura->descripcion,
                'fecha_documento' => $factura->fecha_factura,
                'account_id' => $factura->account_id,
                'pedido_id' => $factura->pedido_id,
                'owner_user_id' => optional(request()->user())->id,
                'nombre_original' => $fileName,
                'mime' => 'application/pdf',
                'tamano' => Storage::disk('public')->size($path),
            ]
        );

        return $pdf->stream($fileName);
    }

    public function publish(Request $request, Factura $factura): RedirectResponse
    {
        if (! $factura->isEditable()) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('status', 'La factura ya está publicada o cancelada.');
        }

        DB::transaction(function () use ($factura) {
            $serie = FacturaSerie::query()
                ->where('activa', true)
                ->lockForUpdate()
                ->first();

            if (! $serie) {
                $serie = FacturaSerie::create([
                    'nombre' => 'Serie principal',
                    'prefijo' => 'F',
                    'siguiente_numero' => 1,
                    'padding' => 4,
                    'activa' => true,
                ]);
            }

            $numero = $serie->siguiente_numero;
            $serie->update(['siguiente_numero' => $numero + 1]);

            $formatted = ($serie->prefijo ?? '') . str_pad((string) $numero, $serie->padding, '0', STR_PAD_LEFT);

            $factura->update([
                'serie_id' => $serie->id,
                'numero_serie' => $formatted,
                'numero' => $factura->numero ?: $formatted,
                'estado' => Factura::ESTADO_PUBLICADA,
                'publicada_en' => now(),
            ]);
        });

        return redirect()
            ->route('facturas.show', $factura)
            ->with('status', 'Factura publicada correctamente.');
    }

    public function rectificar(Request $request, Factura $factura): RedirectResponse
    {
        $factura->load(['lineas.asignaciones']);

        $rectificativa = DB::transaction(function () use ($factura) {
            $rectificativa = Factura::create([
                'account_id' => $factura->account_id,
                'pedido_id' => $factura->pedido_id,
                'descripcion' => 'Rectificativa de ' . ($factura->numero ?: $factura->id),
                'fecha_factura' => now()->toDateString(),
                'estado' => Factura::ESTADO_BORRADOR,
                'tipo' => Factura::TIPO_RECTIFICATIVA,
                'factura_rectificada_id' => $factura->id,
                'moneda' => $factura->moneda ?? 'EUR',
            ]);

            $sumSubtotal = 0;
            $sumTotal = 0;

            foreach ($factura->lineas as $idx => $linea) {
                $subtotal = -1 * (float) $linea->subtotal;
                $importe = -1 * (float) $linea->importe;

                $nuevaLinea = $rectificativa->lineas()->create([
                    'service_id' => $linea->service_id,
                    'referencia' => $linea->referencia,
                    'concepto' => 'Rectificación · ' . $linea->concepto,
                    'cantidad' => $linea->cantidad,
                    'precio' => -1 * (float) $linea->precio,
                    'descuento_porcentaje' => $linea->descuento_porcentaje,
                    'iva_porcentaje' => $linea->iva_porcentaje,
                    'subtotal' => $subtotal,
                    'importe' => $importe,
                    'orden' => $idx,
                ]);

                foreach ($linea->asignaciones as $asignacion) {
                    $nuevaLinea->asignaciones()->create([
                        'pedido_linea_id' => $asignacion->pedido_linea_id,
                        'cantidad' => -1 * (float) $asignacion->cantidad,
                        'base' => -1 * (float) $asignacion->base,
                        'importe' => -1 * (float) $asignacion->importe,
                    ]);
                }

                $sumSubtotal += $subtotal;
                $sumTotal += $importe;
            }

            $rectificativa->update([
                'importe' => $sumSubtotal,
                'importe_total' => $sumTotal,
            ]);

            return $rectificativa;
        });

        return redirect()
            ->route('facturas.edit', $rectificativa)
            ->with('status', 'Rectificativa creada. Revisa y publica cuando esté lista.');
    }

    private function applyAdvancedFilters(Builder $query, ?string $raw): Builder
    {
        if (! $raw) {
            return $query;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || ! isset($decoded['rules']) || ! is_array($decoded['rules'])) {
            return $query;
        }

        $match = ($decoded['match'] ?? 'all') === 'any' ? 'or' : 'and';
        $rules = $decoded['rules'];

        $query->where(function (Builder $builder) use ($rules, $match) {
            foreach ($rules as $rule) {
                if (! is_array($rule) || empty($rule['field']) || empty($rule['operator'])) {
                    continue;
                }

                $method = $match === 'or' ? 'orWhere' : 'where';

                $builder->{$method}(function (Builder $sub) use ($rule) {
                    $this->applyRule($sub, $rule);
                });
            }
        });

        return $query;
    }

    private function applyRule(Builder $query, array $rule): void
    {
        $field    = $rule['field'];
        $operator = $rule['operator'];
        $value    = $rule['value'] ?? null;
        $value2   = $rule['value2'] ?? null;

        if ($field === 'cliente') {
            $this->applyStringRuleToRelation($query, 'cuenta', 'name', $operator, $value, $value2);
            return;
        }

        if (in_array($field, ['fecha_factura', 'fecha_vencimiento', 'fecha_cobro'], true)) {
            $this->applyDateRule($query, $field, $operator, $value, $value2);
            return;
        }

        if (in_array($field, ['importe', 'importe_total'], true)) {
            $this->applyNumberRule($query, $field, $operator, $value, $value2);
            return;
        }

        if (in_array($field, ['cobrado', 'contabilizado'], true)) {
            $normalized = $value === 'si' ? 1 : ($value === 'no' ? 0 : null);
            if ($normalized !== null) {
                $query->where($field, $normalized);
            }
            return;
        }

        $stringFields = [
            'numero',
            'descripcion',
            'forma_pago',
            'instruccion_pago',
            'dpto_comercial',
            'email_facturacion',
        ];

        if (in_array($field, $stringFields, true)) {
            $this->applyStringRule($query, $field, $operator, $value, $value2);
        }
    }

    private function applyStringRule(Builder $query, string $column, string $operator, ?string $value, ?string $value2): void
    {
        $value = $value ?? '';

        match ($operator) {
            'contains'      => $query->where($column, 'like', "%{$value}%"),
            'not_contains'  => $query->where($column, 'not like', "%{$value}%"),
            'equals'        => $query->where($column, '=', $value),
            'not_equals'    => $query->where($column, '!=', $value),
            'starts_with'   => $query->where($column, 'like', "{$value}%"),
            'ends_with'     => $query->where($column, 'like', "%{$value}"),
            'is_empty'      => $query->whereNull($column)->orWhere($column, ''),
            'is_not_empty'  => $query->whereNotNull($column)->where($column, '!=', ''),
            default => null,
        };
    }

    private function applyStringRuleToRelation(
        Builder $query,
        string $relation,
        string $column,
        string $operator,
        ?string $value,
        ?string $value2
    ): void {
        $value = $value ?? '';

        $query->whereHas($relation, function (Builder $rel) use ($column, $operator, $value, $value2) {
            $this->applyStringRule($rel, $column, $operator, $value, $value2);
        });
    }

    private function applyDateRule(Builder $query, string $column, string $operator, ?string $value, ?string $value2): void
    {
        match ($operator) {
            'on'          => $query->whereDate($column, $value),
            'before'      => $query->whereDate($column, '<', $value),
            'after'       => $query->whereDate($column, '>', $value),
            'between'     => $query->whereBetween($column, [$value, $value2]),
            'is_empty'    => $query->whereNull($column),
            'is_not_empty'=> $query->whereNotNull($column),
            default => null,
        };
    }

    private function applyNumberRule(Builder $query, string $column, string $operator, ?string $value, ?string $value2): void
    {
        $value = $value ?? '';

        match ($operator) {
            'equals'            => $query->where($column, '=', $value),
            'not_equals'        => $query->where($column, '!=', $value),
            'greater'           => $query->where($column, '>', $value),
            'greater_or_equal'  => $query->where($column, '>=', $value),
            'less'              => $query->where($column, '<', $value),
            'less_or_equal'     => $query->where($column, '<=', $value),
            'between'           => $query->whereBetween($column, [$value, $value2]),
            'is_empty'          => $query->whereNull($column),
            'is_not_empty'      => $query->whereNotNull($column),
            default => null,
        };
    }
}
