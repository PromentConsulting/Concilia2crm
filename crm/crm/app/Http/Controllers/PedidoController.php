<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\PedidoLinea;
use App\Http\Requests\PedidoRequest;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\Pedido;
use App\Models\User;
use App\Models\PedidoDocenteHorario;
use App\Models\PedidoView;
use App\Models\Peticion;
use App\Models\ServiceCategory;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', '')) ?: null;
        $estado   = trim((string) $request->query('estado', '')) ?: null;
        $anio     = $request->query('anio');
        $advanced = $request->query('af');

        $availableColumns = [
            'numero'                => 'Nº Pedido',
            'cliente'               => 'Cliente',
            'razon_social'          => 'Razón social',
            'provincia'             => 'Provincia',
            'descripcion'           => 'Descripción',
            'dpto_comercial'        => 'Dpto. Comercial',
            'dpto_consultor'        => 'Dpto. Consultor',
            'subvencion'            => 'Subvención',
            'cbe'                   => 'CBE',
            'estado_pedido'         => 'Estado del pedido',
            'estado_facturacion'    => 'Estado de facturación',
            'fecha_proxima_factura' => 'Fecha próxima factura',
            'importe_total'         => 'Importe total',
            'pedido_formacion'      => 'Pedido de formación',
            'fecha_inicio_curso'    => 'Fecha inicio del curso',
            'fecha_fin_curso'       => 'Fecha fin del curso',
            'tipo_proyecto'         => 'Tipo de proyecto',
        ];

        $defaultColumns = [
            'numero',
            'cliente',
            'razon_social',
            'provincia',
            'descripcion',
            'dpto_comercial',
            'dpto_consultor',
            'subvencion',
            'cbe',
            'estado_pedido',
            'estado_facturacion',
            'fecha_proxima_factura',
            'importe_total',
            'pedido_formacion',
            'fecha_inicio_curso',
            'fecha_fin_curso',
            'tipo_proyecto',
        ];

        $views = PedidoView::query()
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

        if (! $busqueda && ! $estado && ! $anio && ! $advanced && $activeView && is_array($activeView->filters)) {
            $busqueda = $activeView->filters['q'] ?? null;
            $estado   = $activeView->filters['estado'] ?? null;
            $anio     = $activeView->filters['anio'] ?? null;
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

        $pedidosQuery = Pedido::query()
            ->with([
                'cuenta:id,name,legal_name,provincia',
                'contacto',
                'peticion:id,titulo',
            ])
            ->withMin('plazosPago', 'fecha_factura')
            ->when($busqueda, function (Builder $query) use ($busqueda) {
                $query->where(function (Builder $sub) use ($busqueda) {
                    $sub->where('numero', 'like', "%{$busqueda}%")
                        ->orWhere('descripcion', 'like', "%{$busqueda}%")
                        ->orWhereHas('cuenta', fn (Builder $q) => $q->where('name', 'like', "%{$busqueda}%"))
                        ->orWhereHas('contacto', function (Builder $q) use ($busqueda) {
                            $q->where(function (Builder $q2) use ($busqueda) {
                                $q2->where('first_name', 'like', "%{$busqueda}%")
                                   ->orWhere('last_name', 'like', "%{$busqueda}%")
                                   ->orWhere('email', 'like', "%{$busqueda}%");
                            });
                        });
                });
            })
            ->when($estado, fn (Builder $q) => $q->where('estado_pedido', $estado))
            ->when($anio, fn (Builder $q) => $q->where('anio', (int) $anio))
            ->orderByDesc('fecha_pedido')
            ->orderByDesc('id');

        $pedidos = $this->applyAdvancedFilters($pedidosQuery, $advanced)
            ->paginate(25)
            ->withQueryString();

        $sortColumn    = 'fecha_pedido';
        $sortDirection = 'desc';

        return view('pedidos.index', [
            'pedidos' => $pedidos,
            'filtros' => [
                'q'      => $busqueda,
                'estado' => $estado,
                'anio'   => $anio,
                'af'     => $advanced,
            ],
            'availableColumns' => $availableColumns,
            'activeColumnKeys' => $activeColumnKeys,
            'views'            => $views,
            'activeView'       => $activeView,
            'sortColumn'       => $sortColumn,
            'sortDirection'    => $sortDirection,
        ]);
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

        if ($field === 'cuenta') {
            $this->applyStringRuleToRelation($query, 'cuenta', 'name', $operator, $value, $value2);
            return;
        }

        if ($field === 'razon_social') {
            $this->applyStringRuleToRelation($query, 'cuenta', 'legal_name', $operator, $value, $value2);
            return;
        }

        if ($field === 'provincia') {
            $this->applyStringRuleToRelation($query, 'cuenta', 'provincia', $operator, $value, $value2);
            return;
        }

        if (in_array($field, ['fecha_pedido', 'fecha_limite_memoria', 'fecha_limite_proyecto'], true)) {
            $this->applyDateRule($query, $field, $operator, $value, $value2);
            return;
        }

        if (in_array($field, ['importe_total', 'anio'], true)) {
            $this->applyNumberRule($query, $field, $operator, $value, $value2);
            return;
        }

        if ($field === 'es_formacion') {
            $normalized = $value === 'si' ? 1 : ($value === 'no' ? 0 : null);
            if ($normalized !== null) {
                $query->where('es_formacion', $normalized);
            }
            return;
        }

        $stringFields = [
            'numero',
            'descripcion',
            'dpto_comercial',
            'dpto_consultor',
            'subvencion',
            'estado_pedido',
            'estado_facturacion',
            'tipo_proyecto',
            'proyecto_externo',
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
            'on'           => $query->whereDate($column, $value),
            'before'       => $query->whereDate($column, '<', $value),
            'after'        => $query->whereDate($column, '>', $value),
            'between'      => $query->whereBetween($column, [$value, $value2]),
            'is_empty'     => $query->whereNull($column),
            'is_not_empty' => $query->whereNotNull($column),
            default => null,
        };
    }

    private function applyNumberRule(Builder $query, string $column, string $operator, ?string $value, ?string $value2): void
    {
        $value = $value ?? '';

        match ($operator) {
            'equals'           => $query->where($column, '=', $value),
            'not_equals'       => $query->where($column, '!=', $value),
            'greater'          => $query->where($column, '>', $value),
            'greater_or_equal' => $query->where($column, '>=', $value),
            'less'             => $query->where($column, '<', $value),
            'less_or_equal'    => $query->where($column, '<=', $value),
            'between'          => $query->whereBetween($column, [$value, $value2]),
            'is_empty'         => $query->whereNull($column),
            'is_not_empty'     => $query->whereNotNull($column),
            default => null,
        };
    }

    public function create(Request $request): View
    {
        $peticionId = $request->integer('peticion_id') ?: null;
        $peticion   = $peticionId
            ? Peticion::query()->with(['cuenta', 'contacto'])->find($peticionId)
            : null;

        $cuentas = Account::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $contactos = Contact::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $categories = ServiceCategory::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'parent_id']);

        $services = Service::query()
            ->with('category:id,nombre')
            ->where('estado', '!=', 'inactivo')
            ->orderBy('referencia')
            ->get(['id', 'referencia', 'descripcion', 'precio', 'service_category_id']);

        return view('pedidos.create', [
            'cuentas'    => $cuentas,
            'contactos'  => $contactos,
            'peticion'   => $peticion,
            'services'   => $services,
            'categories' => $categories,
        ]);
    }

    public function store(PedidoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use (&$pedido, $data, $request) {
            // Creamos pedido sin totales (se recalculan)
            $pedido = Pedido::create(collect($data)->except('lineas')->all());

            $sum = 0;
            $lineas = collect($request->input('lineas', []));

            foreach ($lineas as $idx => $l) {
                $serviceId = $l['service_id'] ?? null;
                $service   = $serviceId ? Service::find($serviceId) : null;

                // saltar líneas vacías
                $descripcionLinea = $l['descripcion'] ?? $service?->descripcion ?? '';
                $precioLinea = $l['precio'] ?? $service?->precio ?? 0;
                $hasContent = $service || trim((string) $descripcionLinea) !== '' || (float) $precioLinea > 0;
                if (! $hasContent) {
                    continue;
                }

                $cantidad = (float) ($l['cantidad'] ?? 0);
                $precio   = (float) ($precioLinea);
                $dto      = (float) ($l['descuento_porcentaje'] ?? 0);
                $iva      = (float) ($l['iva_porcentaje'] ?? 21);

                $subtotal = $cantidad * $precio * (1 - ($dto / 100));
                $total    = $subtotal * (1 + ($iva / 100));

                $pedido->lineas()->create([
                    'service_id'           => $service?->id,
                    'referencia'           => $l['referencia'] ?? $service?->referencia,
                    'descripcion'          => $descripcionLinea,
                    'cantidad'             => $cantidad,
                    'precio'               => $precio,
                    'descuento_porcentaje' => $dto,
                    'iva_porcentaje'       => $iva,
                    'fecha_limite_factura' => $l['fecha_limite_factura'] ?? null,
                    'subtotal'             => $subtotal,
                    'importe_con_iva'      => $total,
                    'orden'                => $idx,
                ]);

                $sum += $total;
            }

            $pedido->update([
                'importe_total' => $sum,
                'moneda'        => $data['moneda'] ?? 'EUR',
            ]);
        });

        AuditLog::create([
            'user_id'    => optional($request->user())->id,
            'model_type' => Pedido::class,
            'model_id'   => $pedido->id,
            'event'      => 'created',
            'old_values' => [],
            'new_values' => $pedido->getAttributes(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('status', 'Pedido creado correctamente.');
    }

    public function show(Pedido $pedido): View
    {
        $pedido->load([
            'facturas' => fn ($q) => $q->orderByDesc('fecha_factura')->orderByDesc('id'),
            'docentes',
            'docenteHorarios' => fn ($q) => $q->orderBy('inicio'),
            'lineas' => fn ($q) => $q->orderBy('orden'),
            'tareas' => fn ($q) => $q
                ->orderBy('fecha_vencimiento')
                ->orderBy('created_at', 'desc'),
            'tareas.owner',

            // NUEVO: para mostrar docs generales de la cuenta desde el pedido
            'cuenta:id,name',

            // NUEVO: documentos del pedido
            'documentos' => fn ($q) => $q
                ->with(['owner:id,name'])
                ->orderByDesc('fecha_documento')
                ->orderByDesc('id'),

            // NUEVO: documentos generales de la cuenta (no vinculados a pedido/solicitud/petición)
            'cuenta.documentos' => fn ($q) => $q
                ->whereNull('pedido_id')
                ->whereNull('peticion_id')
                ->whereNull('solicitud_id')
                ->with(['owner:id,name'])
                ->orderByDesc('fecha_documento')
                ->orderByDesc('id'),
        ]);

        $docentesDisponibles = User::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'docente'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $logs = AuditLog::query()
            ->where('model_type', Pedido::class)
            ->where('model_id', $pedido->id)
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
                            'field'      => $field,
                            'old_value'  => $old,
                            'new_value'  => $new,
                            'created_at' => $log->created_at,
                            'user'       => $log->user,
                        ];
                    })
                    ->filter();
            })
            ->sortByDesc('created_at')
            ->values();

        $totalPedido = (float) ($pedido->importe_total ?? 0);
        $totalFacturado = (float) $pedido->facturas->sum('importe_total');
        $totalPendiente = $totalPedido - $totalFacturado;

        $docenteEventos = $pedido->docenteHorarios->map(function (PedidoDocenteHorario $horario) {
            return [
                'title' => ($horario->docente?->name ?? 'Docente') . ' · Formación',
                'start' => $horario->inicio->toIso8601String(),
                'end' => $horario->fin->toIso8601String(),
                'color' => '#2563eb',
            ];
        })->values();

        return view('pedidos.show', [
            'pedido' => $pedido,
            'logs' => $logs,
            'logEntries' => $logEntries,
            'documentosPedido' => $pedido->documentos ?? collect(),
            'documentosCuenta' => $pedido->cuenta?->documentos ?? collect(),
            'docentesDisponibles' => $docentesDisponibles,
            'totalPedido' => $totalPedido,
            'totalFacturado' => $totalFacturado,
            'totalPendiente' => $totalPendiente,
            'docenteEventos' => $docenteEventos,
        ]);
    }

    public function syncDocentes(Request $request, Pedido $pedido): RedirectResponse
    {
        $data = $request->validate([
            'docentes'   => ['sometimes', 'array'],
            'docentes.*' => ['integer', 'exists:users,id'],
        ]);

        $docentes = $data['docentes'] ?? [];

        $pedido->docentes()->sync($docentes);

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('status', 'Docentes actualizados.');
    }

    public function storeDocenteHorario(Request $request, Pedido $pedido): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:inicio'],
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $pedido->docentes->contains($data['user_id'])) {
            return redirect()
                ->route('pedidos.show', $pedido)
                ->with('status', 'Selecciona primero al docente para asignar horarios.');
        }

        $data['pedido_id'] = $pedido->id;

        PedidoDocenteHorario::create($data);

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('status', 'Horario añadido.');
    }

    public function destroyDocenteHorario(
        Request $request,
        Pedido $pedido,
        PedidoDocenteHorario $horario
    ): RedirectResponse {
        if ($horario->pedido_id !== $pedido->id) {
            return redirect()
                ->route('pedidos.show', $pedido)
                ->with('status', 'Horario no válido.');
        }

        $horario->delete();

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('status', 'Horario eliminado.');
    }
    
    public function edit(Pedido $pedido): View|RedirectResponse
    {
        if ($pedido->facturas()->exists()) {
            return redirect()
                ->route('pedidos.show', $pedido)
                ->with('popup_alert', 'No se puede editar un pedido con facturas asociadas.');
        }

        $cuentas = Account::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $contactos = Contact::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $categories = ServiceCategory::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'parent_id']);

        $services = Service::query()
            ->with('category:id,nombre')
            ->where('estado', '!=', 'inactivo')
            ->orderBy('referencia')
            ->get(['id', 'referencia', 'descripcion', 'precio', 'service_category_id']);

        return view('pedidos.edit', [
            'pedido'     => $pedido,
            'cuentas'    => $cuentas,
            'contactos'  => $contactos,
            'services'   => $services,
            'categories' => $categories,
        ]);
    }

    public function update(PedidoRequest $request, Pedido $pedido): RedirectResponse
    {
        if ($pedido->facturas()->exists()) {
            return redirect()
                ->route('pedidos.show', $pedido)
                ->with('status', 'No se puede modificar un pedido con facturas asociadas.');
        }
        $data = $request->validated();
        $oldValues = $pedido->getOriginal();

        DB::transaction(function () use ($pedido, $data, $request) {
            $pedido->update(collect($data)->except('lineas')->all());

            // recreamos líneas de forma simple (también podrías hacer upsert)
            $pedido->lineas()->delete();

            $sum = 0;
            $lineas = collect($request->input('lineas', []));

            foreach ($lineas as $idx => $l) {
                $serviceId = $l['service_id'] ?? null;
                $service   = $serviceId ? Service::find($serviceId) : null;

                $descripcionLinea = $l['descripcion'] ?? $service?->descripcion ?? '';
                $precioLinea = $l['precio'] ?? $service?->precio ?? 0;
                $hasContent = $service || trim((string) $descripcionLinea) !== '' || (float) $precioLinea > 0;
                if (! $hasContent) {
                    continue;
                }

                $cantidad = (float) ($l['cantidad'] ?? 0);
                $precio   = (float) ($precioLinea);
                $dto      = (float) ($l['descuento_porcentaje'] ?? 0);
                $iva      = (float) ($l['iva_porcentaje'] ?? 21);

                $subtotal = $cantidad * $precio * (1 - ($dto / 100));
                $total    = $subtotal * (1 + ($iva / 100));

                $pedido->lineas()->create([
                    'service_id'           => $service?->id,
                    'referencia'           => $l['referencia'] ?? $service?->referencia,
                    'descripcion'          => $descripcionLinea,
                    'cantidad'             => $cantidad,
                    'precio'               => $precio,
                    'descuento_porcentaje' => $dto,
                    'iva_porcentaje'       => $iva,
                    'fecha_limite_factura' => $l['fecha_limite_factura'] ?? null,
                    'subtotal'             => $subtotal,
                    'importe_con_iva'      => $total,
                    'orden'                => $idx,
                ]);

                $sum += $total;
            }

            $pedido->update([
                'importe_total' => $sum,
            ]);
        });

        $pedido->refresh();

        AuditLog::create([
            'user_id'    => optional($request->user())->id,
            'model_type' => Pedido::class,
            'model_id'   => $pedido->id,
            'event'      => 'updated',
            'old_values' => $oldValues,
            'new_values' => $pedido->getAttributes(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('status', 'Pedido actualizado correctamente.');
    }

    public function destroy(Pedido $pedido): RedirectResponse
    {
        if ($pedido->facturas()->exists()) {
            return redirect()
                ->route('pedidos.show', $pedido)
                ->with('status', 'No se puede eliminar un pedido con facturas asociadas.');
        }

        $pedido->delete();

        return redirect()
            ->route('pedidos.index')
            ->with('status', 'Pedido eliminado.');
    }
}
