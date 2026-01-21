<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Activity;
use App\Models\Documento;
use App\Models\Account;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Peticion;
use App\Models\Solicitud;
use App\Models\Team;
use App\Models\User;
use App\Support\Concerns\InteractsWithContactsTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    use InteractsWithContactsTable;


    protected array $groupRootCache = [];

    public function index(Request $request): View
    {
        [
            'search'        => $search,
            'lifecycle'     => $lifecycle,
            'country'       => $country,
            'hasLifecycle'  => $hasLifecycle,
            'countryColumn' => $countryColumn,
        ] = $this->extractFilters($request);

        // --- Query base de cuentas ---
        $accounts = $this->buildAccountsQuery($search, $lifecycle, $country, $hasLifecycle, $countryColumn)
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        // --- Países disponibles para el filtro ---
        $countries = $countryColumn
            ? $this->availableCountries($countryColumn)
            : collect();

        // --- Configuración de columnas disponibles en el listado ---
        $availableColumns = [
            'name'                      => 'Nombre',
            'customer_code'             => 'Código cliente',
            'email'                     => 'Email',
            'phone'                     => 'Teléfono',
            'website'                   => 'Web',
            'country'                   => 'País',
            'lifecycle'                 => 'Estado',
            'contacts_count'            => 'Contactos',
            'tax_id'                    => 'CIF/NIF',
            'is_billable'               => 'Cliente facturable',
            'billing_has_payment_issues'=> 'Problemas cobro',
            'created_at'                => 'Creado el',
        ];

        // De momento dejamos las columnas por defecto como estaban
        $defaultColumns = ['name', 'email', 'phone', 'country', 'lifecycle'];

        // --- Vistas guardadas (si existe el modelo AccountView) ---
        if (class_exists(\App\Models\AccountView::class)) {
            $views = \App\Models\AccountView::query()
                ->where('user_id', optional($request->user())->id)
                ->orderBy('name')
                ->get();
        } else {
            $views = collect();
        }

        // Vista activa: por parámetro ?vista_id o por defecto
        $activeView = null;
        $vistaId    = $request->query('vista_id');

        if ($vistaId && $views->isNotEmpty()) {
            $activeView = $views->firstWhere('id', (int) $vistaId);
        } elseif ($views->isNotEmpty()) {
            $activeView = $views->firstWhere('is_default', true);
        }

        // --- Columnas activas ---
        if ($request->filled('columns')) {
            // Prioridad 1: columnas enviadas en la URL (drag & drop, selector de columnas)
            $requestedColumns = array_filter((array) $request->input('columns', []));
            $activeColumnKeys = array_values(
                array_intersect($requestedColumns, array_keys($availableColumns))
            );
        } elseif ($activeView && is_array($activeView->columns)) {
            // Prioridad 2: columnas almacenadas en la vista guardada
            $activeColumnKeys = array_values(
                array_intersect($activeView->columns, array_keys($availableColumns))
            );
        } else {
            // Prioridad 3: columnas por defecto
            $activeColumnKeys = $defaultColumns;
        }

        if (empty($activeColumnKeys)) {
            $activeColumnKeys = $defaultColumns;
        }

        // --- Orden actual (por ahora fijo, pero lo usamos para guardar vistas) ---
        $sortColumn    = 'name';
        $sortDirection = 'asc';

        return view('accounts.index', [
            'accounts'     => $accounts,
            'countries'    => $countries,
            'filters'      => [
                'q'         => $search,
                'lifecycle' => $hasLifecycle ? $lifecycle : null,
                'country'   => $country,
            ],
            'hasLifecycle'     => $hasLifecycle,

            // para columnas / drag & drop
            'availableColumns' => $availableColumns,
            'activeColumnKeys' => $activeColumnKeys,

            // para vistas guardadas
            'views'        => $views,
            'activeView'   => $activeView,
            'sortColumn'   => $sortColumn,
            'sortDirection'=> $sortDirection,

            // para acciones masivas
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'teams' => Team::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('accounts.create', [
            'hasLifecycle' => $this->hasLifecycleColumn(),
            'groupParents' => $this->groupParentOptions(),
        ]);
    }

    public function store(AccountRequest $request): RedirectResponse
    {
        // Creamos la cuenta (permitimos posibles duplicados; solo avisamos)
        $attributes = $this->persistableAttributes($request);
        $attributes = $this->handleLogoUpload($request, $attributes);
        $account = Account::create($attributes);

        // Avisos de duplicados (email/teléfono) fuera del mismo grupo empresarial
        $conflicts = $this->duplicateConflictsForAccount($account);
        $redirect = redirect()
            ->route('accounts.show', $account)
            ->with('status', 'Cuenta creada correctamente.');

        if (!empty($conflicts['email']) || !empty($conflicts['phone'])) {
            $redirect->with('duplicate_conflicts', $conflicts);
        }

        return $redirect;
    }

    public function show(Account $account): View
    {
        $tab = request('tab', 'resumen');
        if ($tab === "privado") {
            $tab = "resumen";
        }

        // Cargamos lo necesario para la ficha
        $account->load([
            'contacts' => fn ($q) => $this->orderContactsByName($q),
            'tareas'   => fn ($q) => $q
                ->orderBy('fecha_vencimiento')
                ->orderByDesc('created_at'),
            'documentos' => fn ($q) => $q
                ->with([
                    'owner:id,name',
                    'pedido:id,numero',
                    'peticion:id,titulo',
                    'solicitud:id,titulo',
                ])
                ->orderByDesc('fecha_documento')
                ->orderByDesc('id'),
            'audits.user',
            'parent',
            'children.ownerUser',
            'ownerUser',
        ]);

        // ------------------------------------------------------------------
        // Apartado F: Operaciones / Facturas / Documentos / Facturación
        // ------------------------------------------------------------------
        $scope = request('scope', 'self'); // self | group
        $companyId = request('company_id');
        $tipo = request('tipo', 'all');

        $groupRootId = $this->groupRootIdForAccount($account) ?? $account->id;
        $groupCompanies = Account::query()
            ->where('id', $groupRootId)
            ->orWhere('parent_account_id', $groupRootId)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_account_id']);

        $scopeAccountIds = [$account->id];
        if ($scope === 'group') {
            $scopeAccountIds = $groupCompanies->pluck('id')->map(fn ($v) => (int) $v)->all();
        }

        if ($companyId) {
            $cid = (int) $companyId;
            if (in_array($cid, $scopeAccountIds, true)) {
                $scopeAccountIds = [$cid];
            }
        }


        // Contadores de pestañas (para mostrar "X (n)" en el menú de la cuenta)
        // Nota: para Operaciones/Facturas/Documentos usamos el mismo ámbito (solo cuenta / todo el grupo + empresa) que los filtros.
        $pedidoIdsForCounts = Pedido::whereIn('account_id', $scopeAccountIds)->pluck('id');

        $tabCounts = [
            'contactos'   => $account->contacts()->count(),
            'actividad'   => Activity::query()
                ->where('subject_type', Account::class)
                ->where('subject_id', $account->id)
                ->count(),
            'operaciones' => (int) Pedido::whereIn('account_id', $scopeAccountIds)->count()
                + (int) Peticion::whereIn('account_id', $scopeAccountIds)->count()
                + (int) Solicitud::whereIn('account_id', $scopeAccountIds)->count(),
            'facturas'    => (int) Factura::whereIn('account_id', $scopeAccountIds)->count(),
            'documentos'  => (int) Documento::query()
                ->whereIn('account_id', $scopeAccountIds)
                ->orWhereIn('pedido_id', $pedidoIdsForCounts)
                ->distinct('id')
                ->count('id'),
        ];

        $operations = collect();
        $facturas = collect();

        if ($tab === 'operaciones') {
            $items = collect();

            if ($tipo === 'all' || $tipo === 'solicitudes') {
                $solicitudes = Solicitud::query()
                    ->whereIn('account_id', $scopeAccountIds)
                    ->orderByDesc('fecha_solicitud')
                    ->orderByDesc('id')
                    ->get(['id', 'account_id', 'titulo', 'estado', 'fecha_solicitud', 'created_at']);

                $items = $items->concat($solicitudes->map(function (Solicitud $s) {
                    $when = $s->fecha_solicitud ?? $s->created_at;
                    return [
                        'tipo' => 'Solicitud',
                        'tipo_key' => 'solicitudes',
                        'id' => $s->id,
                        'titulo' => $s->titulo,
                        'estado' => $s->estado,
                        'fecha' => $when,
                        'route' => route('solicitudes.show', $s),
                    ];
                }));
            }

            if ($tipo === 'all' || $tipo === 'peticiones') {
                $peticiones = Peticion::query()
                    ->whereIn('account_id', $scopeAccountIds)
                    ->orderByDesc('fecha_alta')
                    ->orderByDesc('id')
                    ->get(['id', 'account_id', 'codigo', 'titulo', 'estado', 'fecha_alta', 'created_at']);

                $items = $items->concat($peticiones->map(function (Peticion $p) {
                    $when = $p->fecha_alta ?? $p->created_at;
                    $title = $p->codigo ? ($p->codigo.' · '.$p->titulo) : $p->titulo;
                    return [
                        'tipo' => 'Petición',
                        'tipo_key' => 'peticiones',
                        'id' => $p->id,
                        'titulo' => $title,
                        'estado' => $p->estado,
                        'fecha' => $when,
                        'route' => route('peticiones.show', $p),
                    ];
                }));
            }

            if ($tipo === 'all' || $tipo === 'pedidos') {
                $pedidos = Pedido::query()
                    ->whereIn('account_id', $scopeAccountIds)
                    ->orderByDesc('fecha_pedido')
                    ->orderByDesc('id')
                    ->get(['id', 'account_id', 'numero', 'descripcion', 'estado_pedido', 'fecha_pedido', 'importe_total', 'moneda', 'created_at']);

                $items = $items->concat($pedidos->map(function (Pedido $p) {
                    $when = $p->fecha_pedido ?? $p->created_at;
                    $title = $p->numero ?: ($p->descripcion ?: 'Pedido '.$p->id);
                    return [
                        'tipo' => 'Pedido',
                        'tipo_key' => 'pedidos',
                        'id' => $p->id,
                        'titulo' => $title,
                        'estado' => $p->estado_pedido,
                        'fecha' => $when,
                        'importe' => $p->importe_total,
                        'moneda' => $p->moneda,
                        'route' => route('pedidos.show', $p),
                    ];
                }));
            }

            $operations = $items
                ->sortByDesc(fn ($row) => $row['fecha'] ?? null)
                ->values();
        }

        if ($tab === 'facturas') {
            // Las facturas reales viven en la tabla `facturas` (modelo Factura).
            // El modelo Documento se usa para adjuntos/archivos (incluidos PDFs), pero no como fuente de verdad.
            $facturas = Factura::query()
                ->with(['cuenta:id,name', 'pedido:id,numero'])
                ->whereIn('account_id', $scopeAccountIds)
                ->orderByDesc('fecha_factura')
                ->orderByDesc('id')
                ->get();
        }

        // Documentos: separar generales vs vinculados a una operación
        $docsGenerales = $account->documentos
            ->filter(fn ($d) => empty($d->pedido_id) && empty($d->peticion_id) && empty($d->solicitud_id))
            ->values();

        $docsVinculados = $account->documentos
            ->filter(fn ($d) => !empty($d->pedido_id) || !empty($d->peticion_id) || !empty($d->solicitud_id))
            ->values();

        return view('accounts.show', [
            'account'      => $account,
            'tab'          => $tab,
            'hasLifecycle' => $this->hasLifecycleColumn(),
            'showGroupTab' => in_array($account->tipo_relacion_grupo, ['matriz', 'filial'], true)
                || $account->parent_account_id
                || $account->children->isNotEmpty(),

            // Apartado F
            'operations' => $operations,
            'facturas' => $facturas,
            'groupCompanies' => $groupCompanies,
            'operationFilters' => [
                'scope' => $scope,
                'company_id' => $companyId,
                'tipo' => $tipo,
            ],
            'docsGenerales' => $docsGenerales,
            'docsVinculados' => $docsVinculados,
            'tabCounts' => $tabCounts,
        ]);
    }

    public function edit(Account $account): View
    {
        return view('accounts.edit', [
            'account'      => $account,
            'hasLifecycle' => $this->hasLifecycleColumn(),
            'groupParents' => $this->groupParentOptions($account),
        ]);
    }

    public function update(AccountRequest $request, Account $account): RedirectResponse
    {
        $attributes = $this->persistableAttributes($request, $account);
        $attributes = $this->handleLogoUpload($request, $attributes, $account);
        $account->update($attributes);

        $conflicts = $this->duplicateConflictsForAccount($account);

        $redirect = redirect()
            ->route('accounts.show', $account)
            ->with('status', 'Cuenta actualizada correctamente.');

        if (!empty($conflicts['email']) || !empty($conflicts['phone'])) {
            $redirect->with('duplicate_conflicts', $conflicts);
        }

        return $redirect;
    }

    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Cuenta eliminada.');
    }

    /**
     * Formulario de importación de cuentas (subir CSV).
     */
    public function showImportForm(): View
    {
        return view('accounts.import');
    }

    /**
     * Descarga una plantilla CSV con las columnas correctas para la importación.
     */
    function downloadImportTemplate(): StreamedResponse
    {
        $filename = 'plantilla_importar_cuentas_' . now()->format('Ymd_His') . '.csv';

        $columns = [
            'Razón social',
            'Nombre abreviado',
            'NIF/CIF',
            'Dirección',
            'Localidad',
            'Provincia',
            'Código postal',
            'Teléfono',
            'Fax',
            'E-mail de empresa',
            'Página web',
            'Tipo',
            'Tipo entidad',
            'Empleados',
            'Tamaño empresa (min)',
            'Tamaño empresa (max)',
            'Sector',
            'Productos/Servicios',
            'Año de fundación',
            'Fecha de actualización',
            'Contratos públicos',
            'Plan de Igualdad',
            'Distintivo de Igualdad',
            'Calidad',
            'RSE',
            'Otras certificaciones',
            'Estado',
            'Dpto. Comercial',
            'CNAE',
            'Comentarios'
        ];

        // CSV separado por ';' (como espera el importador).
        return response()->streamDownload(function () use ($columns) {
            // BOM para que Excel abra bien los acentos en UTF-8
            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');

            // Cabecera
            fputcsv($out, $columns, ';');

            // Fila de ejemplo (opcional). Puedes borrarla si no la quieres.
            $example = array_fill(0, count($columns), '');
            $example[0] = 'Ejemplo Empresa S.L.';          // Razón social
            $example[1] = 'Ejemplo Empresa';               // Nombre abreviado
            $example[2] = 'B12345678';                     // NIF/CIF
            $example[7] = '+34 600 000 000';               // Teléfono
            $example[9] = 'empresa@ejemplo.com';           // E-mail de empresa
            $example[10] = 'https://ejemplo.com';          // Página web
            $example[26] = 'activo';                       // Estado (activo/inactivo) opcional
            fputcsv($out, $example, ';');

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }


    /**
     * Acciones masivas sobre el listado de cuentas.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action'        => ['required', 'string', 'in:activate,deactivate,assign_owner_user,assign_owner_team,set_payment_issue,clear_payment_issue'],
            'select_all'    => ['nullable', 'boolean'],
            'ids'           => ['nullable', 'array', 'required_without:select_all'],
            'ids.*'         => ['integer'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'owner_team_id' => ['nullable', 'integer', 'exists:teams,id'],
        ]);

        $query = $this->bulkOrExportQuery($request, $validated);

        $updated = match ($validated['action']) {
            'activate' => $query->update([$this->statusColumnName() => 'activo']),
            'deactivate' => $query->update([$this->statusColumnName() => 'inactivo']),
            'set_payment_issue' => $query->update(['billing_has_payment_issues' => true]),
            'clear_payment_issue' => $query->update(['billing_has_payment_issues' => false]),
            'assign_owner_user' => $this->updateOwnerUser($query, $validated['owner_user_id'] ?? null),
            'assign_owner_team' => $this->updateOwnerTeam($query, $validated['owner_team_id'] ?? null),
            default => 0,
        };

        if ($updated === null) {
            return back()->withErrors(['action' => 'Debes seleccionar un usuario o equipo para reasignar.']);
        }

        return back()->with('status', "Acción aplicada sobre {$updated} cuentas.");
    }

    /**
     * Exporta cuentas seleccionadas a CSV o Excel ligero.
     */
    public function export(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'format' => ['sometimes', 'string', 'in:csv,xlsx'],
            'select_all' => ['nullable', 'boolean'],
            'ids'    => ['nullable', 'array', 'required_without:select_all'],
            'ids.*'  => ['integer'],
        ]);

        $format = $validated['format'] ?? 'csv';
        $statusColumn = $this->statusColumnName();

        $accounts = $this->bulkOrExportQuery($request, $validated)
            ->with(['ownerUser:id,name', 'ownerTeam:id,name'])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
                'phone',
                'country',
                'billing_country',
                'billing_has_payment_issues',
                'owner_user_id',
                'owner_team_id',
                $statusColumn,
                'created_at',
            ]);

        $filename = 'accounts_export_' . now()->format('Ymd_His') . '.' . $format;

        return response()->stream(function () use ($accounts, $statusColumn) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Nombre',
                'Email',
                'Teléfono',
                'País',
                'País facturación',
                'Estado',
                'Problemas cobro',
                'Propietario (usuario)',
                'Propietario (equipo)',
                'Creado el',
            ], ';');

            foreach ($accounts as $account) {
                fputcsv($handle, [
                    $account->id,
                    $account->name,
                    $account->email,
                    $account->phone,
                    $account->country,
                    $account->billing_country,
                    $account->{$statusColumn},
                    $account->billing_has_payment_issues ? 'Sí' : 'No',
                    optional($account->ownerUser)->name,
                    optional($account->ownerTeam)->name,
                    optional($account->created_at)?->format('Y-m-d'),
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => $format === 'xlsx'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Procesa el CSV exportado desde el Excel antiguo y crea cuentas.
     */
    public function handleImport(Request $request)
    {
        // ------------------------------------------------------------
        // PASO 2: Confirmación de importación (aplicar cambios)
        // ------------------------------------------------------------
        if ($request->boolean('confirm')) {
            $request->validate([
                'import_token' => ['required', 'string'],
                'decisions'    => ['array'],
                'targets'      => ['array'],
                'confirm_duplicates' => ['array'],
            ]);

            $token   = $request->input('import_token');
            $payload = session()->get("accounts_import_preview.{$token}");

            if (! $payload || empty($payload['rows'])) {
                return redirect()
                    ->route('accounts.import.create')
                    ->withErrors(['file' => 'La previsualización ha caducado o no es válida. Vuelve a subir el CSV.']);
            }

            $rows      = $payload['rows'];
            $decisions = $request->input('decisions', []);
            $targets   = $request->input('targets', []);

            
$confirmDuplicates = $request->input('confirm_duplicates', []);

// Validar que todas las filas en conflicto tengan decisión y, si aplica, los campos extra.
$missing = 0;
foreach ($rows as $i => $row) {
    if (empty($row['has_conflict'])) {
        continue;
    }

    $decision = $decisions[$i] ?? null;

    if (empty($decision)) {
        $missing++;
        continue;
    }

    // Si elegimos actualizar y hay más de una coincidencia, necesitamos target.
    if ($decision === 'update' && ! empty($row['needs_target']) && empty($targets[$i])) {
        $missing++;
        continue;
    }

    // Si elegimos crear un duplicado, pedimos confirmación explícita.
    if ($decision === 'create_new' && empty($confirmDuplicates[$i])) {
        $missing++;
        continue;
    }
}

if ($missing > 0) {
                return back()->withErrors([
                    'file' => "Faltan decisiones por resolver ({$missing}). Selecciona una opción en todas las filas con conflicto.",
                ]);
            }

            $created = 0;
            $updated = 0;
            $kept    = 0;

            foreach ($rows as $i => $row) {
                $data = $row['data'] ?? [];

                // Normalizar campos numéricos (seguridad extra)
                foreach (['employee_count', 'company_size_min', 'company_size_max', 'founded_year'] as $intField) {
                    if (array_key_exists($intField, $data)) {
                        if ($data[$intField] !== null && $data[$intField] !== '') {
                            $data[$intField] = (int) $data[$intField];
                        } else {
                            $data[$intField] = null;
                        }
                    }
                }

                if (empty($row['has_conflict'])) {
                    Account::create($data);
                    $created++;
                    continue;
                }

                $decision = $decisions[$i] ?? null;

                // Mantener el registro existente: no hacemos nada
                if ($decision === 'keep') {
                    $kept++;
                    continue;
                }

                // Crear NUEVO igualmente (duplicado) - requiere confirmación explícita en UI
                if ($decision === 'create_new') {
                    Account::create($data);
                    $created++;
                    continue;
                }

                // Aplicar "nuevos cambios" sobre el registro existente
                if ($decision === 'update') {
                    $targetId = $targets[$i] ?? ($row['target_account_id'] ?? null);

                    if (! $targetId) {
                        // Si falta target, lo tratamos como "keep" para evitar daños.
                        $kept++;
                        continue;
                    }

                    $account = Account::find($targetId);

                    if (! $account) {
                        // Si el registro ya no existe, creamos uno nuevo
                        Account::create($data);
                        $created++;
                        continue;
                    }

                    // Actualizamos SOLO con valores no vacíos del CSV (evita borrar datos existentes)
                    $filtered = [];
                    foreach ($data as $k => $v) {
                        // Conservamos 0 / "0" / false como valores válidos
                        if ($v === null) continue;
                        if (is_string($v) && trim($v) === '') continue;
                        $filtered[$k] = $v;
                    }

                    $account->fill($filtered);
                    $account->save();

                    $updated++;
                    continue;
                }

                // Si por cualquier motivo viene un valor raro, no hacemos nada
                $kept++;
            }

            // Limpiar token para evitar re-importar por error
            session()->forget("accounts_import_preview.{$token}");

            return redirect()
                ->route('accounts.index')
                ->with('status', "Importación confirmada: {$created} creadas, {$updated} actualizadas, {$kept} mantenidas.");
        }

        // ------------------------------------------------------------
        // PASO 1: Subida del CSV y PREVISUALIZACIÓN (no se guarda nada)
        // ------------------------------------------------------------
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path   = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->withErrors(['file' => 'No se ha podido leer el archivo.']);
        }

        // Cabecera (primera fila)
        $header = fgetcsv($handle, 0, ';');
        if (! $header) {
            fclose($handle);

            return back()->withErrors(['file' => 'El archivo está vacío o el encabezado no es válido.']);
        }

        $header = array_map('trim', $header);
        $index  = array_flip($header); // "Razón social" => posición, etc.

        $rows = [];
        $emailsToCheck = [];
        $phonesLast6   = [];

        $rowNum = 1; // contando cabecera
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNum++;

            // Saltar filas completamente vacías
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }

            $get = function (string $col) use ($row, $index) {
                if (! isset($index[$col])) {
                    return null;
                }
                $val = $row[$index[$col]] ?? null;
                $val = is_string($val) ? trim($val) : $val;
                return $val === '' ? null : $val;
            };

            // Sector (puede venir como "Sector - X")
            $sectorValue = $get('Sector');
            if (is_string($sectorValue) && str_contains($sectorValue, '-')) {
                $sectorValue = trim(explode('-', $sectorValue, 2)[1] ?? $sectorValue);
            }

            $data = [
                'name'                 => $get('Razón social') ?? $get('Nombre comercial'),
                'legal_name'           => $get('Razón social'),
                'nombre_abreviado'     => $get('Nombre abreviado'),
                'tipo_entidad'         => $this->mapTipoEntidad($get('Tipo entidad')),
                'tax_id'               => $get('NIF/CIF'),
                'address'              => $get('Dirección'),
                'city'                 => $get('Localidad'),
                'state'                => $get('Provincia'),
                'postal_code'          => $get('Código postal'),
                'phone'                => $get('Teléfono'),
                'fax'                  => $get('Fax'),
                'email'                => $get('E-mail de empresa'),
                'website'              => $get('Página web'),
                'company_type'         => $get('Tipo'),
                'industry'             => $sectorValue,
                'company_size_min'     => $get('Tamaño empresa (min)'),
                'company_size_max'     => $get('Tamaño empresa (max)'),
                'employee_count'       => $get('Empleados'),
                'founded_year'         => $get('Año de fundación'),
                'public_contracts'     => $get('Contratos públicos'),
                'equality_plan'        => $get('Plan de Igualdad'),
                'equality_mark'        => $get('Distintivo de Igualdad'),
                'quality'              => $get('Calidad'),
                'rse'                  => $get('RSE'),
                'other_certifications' => $get('Otras certificaciones'),
                'estado'               => $this->mapEstado($get('Estado')),
                'sales_department'     => $get('Dpto. Comercial'),
                'cnae'                 => $get('CNAE'),
                'notes'                => $get('Comentarios') ?? $get('Notas'),
                'lifecycle'            => $get('Estado CRM') ?? $get('Lifecycle'),
            ];
            // Si no hay Razón social/Nombre comercial, usamos el Nombre abreviado para la previsualización e importación
            if (empty($data['name']) && !empty($data['nombre_abreviado'])) {
                $data['name'] = $data['nombre_abreviado'];
            }


            // Normalizar booleans si vienen como "sí/no" o "1/0"
            foreach (['public_contracts', 'equality_plan', 'equality_mark'] as $boolField) {
                if (isset($data[$boolField])) {
                    $v = $data[$boolField];
                    if (is_string($v)) {
                        $lv = mb_strtolower(trim($v));
                        if (in_array($lv, ['si', 'sí', 's', '1', 'true'], true)) $data[$boolField] = 1;
                        elseif (in_array($lv, ['no', 'n', '0', 'false'], true)) $data[$boolField] = 0;
                    }
                }
            }

            // Campos numéricos
            foreach (['employee_count', 'company_size_min', 'company_size_max', 'founded_year'] as $intField) {
                if (isset($data[$intField]) && $data[$intField] !== null && $data[$intField] !== '') {
                    $data[$intField] = (int) $data[$intField];
                } else {
                    $data[$intField] = null;
                }
            }

            $san = $this->sanitizeImportData($data);
            $data = $san['data'];
            $warnings = $san['warnings'];

            $emailNorm = $this->normalizeEmail($data['email'] ?? null);
            $phoneNorm = $this->normalizePhone($data['phone'] ?? null);

            if ($emailNorm) {
                $emailsToCheck[] = $emailNorm;
            }
            if ($phoneNorm && strlen($phoneNorm) >= 6) {
                $phonesLast6[] = substr($phoneNorm, -6);
            }

            $rows[] = [
                'row_number'     => $rowNum,
                'data'           => $data,
                'email_norm'     => $emailNorm,
                'phone_norm'     => $phoneNorm,
                'row_group_root_id' => $this->rowGroupRootId($data),
                'row_group_name'    => $this->normalizeGroupName($data['group_name'] ?? null),
                'warnings'          => $warnings,
                'has_conflict'   => false,
                'conflicts'      => [],
                'target_account_id' => null,
                'needs_target'   => false,
            ];
        }

        fclose($handle);

        // ------------------------------------------------------------
        // Buscar coincidencias en BD (email exacto y teléfono aproximado)
        // ------------------------------------------------------------
        $emailsToCheck = array_values(array_unique(array_filter($emailsToCheck)));
        $phonesLast6   = array_values(array_unique(array_filter($phonesLast6)));

        $accountsByEmail = [];
        if (! empty($emailsToCheck)) {
            // MySQL suele ser case-insensitive, pero lo forzamos por seguridad
            $found = Account::query()
                ->whereNotNull('email')
                ->whereIn(\DB::raw('LOWER(email)'), $emailsToCheck)
                ->get();

            foreach ($found as $acc) {
                $key = $this->normalizeEmail($acc->email);
                if ($key) {
                    $accountsByEmail[$key][] = $acc;
                }
            }
        }

        $accountsPhoneCandidates = collect();
        if (! empty($phonesLast6)) {
            $q = Account::query()->whereNotNull('phone');

            // OR por últimos 6 dígitos
            $q->where(function ($sub) use ($phonesLast6) {
                foreach ($phonesLast6 as $k) {
                    $sub->orWhere('phone', 'like', "%{$k}%");
                }
            });

            $accountsPhoneCandidates = $q->get();
        }

        $accountsByPhone = [];
        foreach ($accountsPhoneCandidates as $acc) {
            $key = $this->normalizePhone($acc->phone);
            if ($key) {
                $accountsByPhone[$key][] = $acc;
            }
        }

        $conflictCount = 0;

        foreach ($rows as $i => $r) {
            $conflicts = [];

            if (! empty($r['email_norm']) && isset($accountsByEmail[$r['email_norm']])) {
                foreach ($accountsByEmail[$r['email_norm']] as $acc) {
                    if ($this->conflictSuppressed($rows[$i], $acc)) {
                        continue;
                    }
                    $conflicts[] = [
                        'type'  => 'email',
                        'id'    => $acc->id,
                        'name'  => $acc->nombre_abreviado ?? $acc->name,
                        'email' => $acc->email,
                        'phone' => $acc->phone,
                    ];
                }
            }

            if (! empty($r['phone_norm']) && isset($accountsByPhone[$r['phone_norm']])) {
                foreach ($accountsByPhone[$r['phone_norm']] as $acc) {
                    if ($this->conflictSuppressed($rows[$i], $acc)) {
                        continue;
                    }
                    $conflicts[] = [
                        'type'  => 'phone',
                        'id'    => $acc->id,
                        'name'  => $acc->nombre_abreviado ?? $acc->name,
                        'email' => $acc->email,
                        'phone' => $acc->phone,
                    ];
                }
            }

            // Unificar por id
            $byId = [];
            foreach ($conflicts as $c) {
                $byId[$c['id']] = $c;
            }
            $conflicts = array_values($byId);

            if (! empty($conflicts)) {
                $rows[$i]['has_conflict'] = true;
                $rows[$i]['conflicts']    = $conflicts;
                $conflictCount++;

                if (count($conflicts) === 1) {
                    $rows[$i]['target_account_id'] = $conflicts[0]['id'];
                } else {
                    $rows[$i]['needs_target'] = true;
                }
            }
        }

        // Guardar en sesión para confirmar después
        $token = (string) \Illuminate\Support\Str::uuid();

        session()->put("accounts_import_preview.{$token}", [
            'rows'       => $rows,
            'created_at' => now()->timestamp,
        ]);

        $preview = [
            'total'     => count($rows),
            'conflicts' => $conflictCount,
            'news'      => count($rows) - $conflictCount,
        ];

        return view('accounts.import', [
            'preview'      => $preview,
            'rows'         => $rows,
            'import_token' => $token,
        ]);
    }

    /**
 * Devuelve datos saneados para importación y una lista de avisos (no bloquea).
 */
private function sanitizeImportData(array $data): array
{
    $warnings = [];

    
// Nombre a mostrar (para la previsualización y para que nunca quede vacío)
// Priorizamos "nombre_abreviado" y, si no existe, usamos "razon_social".
if (empty($data['nombre_abreviado']) && !empty($data['razon_social'])) {
    $data['nombre_abreviado'] = $data['razon_social'];
}
if (empty($data['name'])) {
    $data['name'] = $data['nombre_abreviado'] ?? ($data['razon_social'] ?? null);
}

// Guardamos los valores originales para poder mostrarlos aunque sean inválidos
$data['email_raw'] = $data['email'] ?? null;
$data['phone_raw'] = $data['phone'] ?? null;

// Email
    if (array_key_exists('email', $data) && $data['email'] !== null && $data['email'] !== '') {
        $email = trim((string) $data['email']);
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $warnings[] = 'Email inválido: se ignorará en la importación.';
            $data['email'] = null;
        } else {
            $data['email'] = $email;
        }
    }

    // Teléfono
    if (array_key_exists('phone', $data) && $data['phone'] !== null && $data['phone'] !== '') {
        $phone = trim((string) $data['phone']);
        $digits = preg_replace('/\D+/', '', $phone);

        // Regla simple: al menos 9 dígitos (habitual en ES). Si no, lo omitimos para homogeneizar.
        if (strlen($digits) < 9) {
            $warnings[] = 'Teléfono inválido: se ignorará en la importación.';
            $data['phone'] = null;
        } else {
            $data['phone'] = $phone;
        }
    }

    return [
        'data'     => $data,
        'warnings' => $warnings,
    ];
}

/**
 * Calcula la raíz de grupo (matriz) de una fila importada (si viene informada).
 */
private function rowGroupRootId(array $data): ?int
{
    $parent = $data['parent_account_id'] ?? null;

    if ($parent !== null && $parent !== '' && is_numeric($parent)) {
        $parentId = (int) $parent;

        // Si el parent es filial, devolvemos la raíz real del grupo.
        $root = $this->groupRootIdById($parentId);

        return $root ?: $parentId;
    }

    return null;
}

private function normalizeGroupName(?string $name): ?string
{
    $name = trim((string) ($name ?? ''));

    if ($name === '') {
        return null;
    }

    return mb_strtolower($name);
}

/**
 * Devuelve true si el conflicto debe ignorarse por estar dentro del mismo grupo empresarial.
 */
private function conflictSuppressed(array $row, Account $acc): bool
{
    $rowRoot = $row['row_group_root_id'] ?? null;
    $rowGroupName = $row['row_group_name'] ?? null;

    $accRoot = $this->groupRootIdForAccount($acc);

    if ($rowRoot && $accRoot && (int) $rowRoot === (int) $accRoot) {
        return true;
    }

    if ($rowGroupName) {
        $accGroupName = $this->normalizeGroupName($acc->group_name ?? null);

        if ($accGroupName && $accGroupName === $rowGroupName) {
            return true;
        }
    }

    return false;
}

/**
 * Busca duplicados por email/teléfono y devuelve SOLO los que están fuera del mismo grupo empresarial.
 * Estructura pensada para pintarse en layouts/app.blade.php vía session('duplicate_conflicts').
 */
private function duplicateConflictsForAccount(Account $account): array
{
    $result = [
        'email' => [],
        'phone' => [],
    ];

    $selfRoot = $this->groupRootIdForAccount($account);

    // Email exacto (case-insensitive)
    $emailNorm = $this->normalizeEmail($account->email ?? null);
    if ($emailNorm) {
        $found = Account::query()
            ->whereNotNull('email')
            ->whereRaw('LOWER(email) = ?', [$emailNorm])
            ->where('id', '!=', $account->id)
            ->get();

        foreach ($found as $acc) {
            $accRoot = $this->groupRootIdForAccount($acc);

            // No avisar si es dentro del mismo grupo empresarial
            if ($selfRoot && $accRoot && (int) $selfRoot === (int) $accRoot) {
                continue;
            }

            $result['email'][$acc->id] = [
                'id'   => $acc->id,
                'name' => $acc->nombre_abreviado ?? $acc->name,
            ];
        }
    }

    // Teléfono: aproximación por últimos 6 dígitos y luego normalización completa
    $phoneNorm = $this->normalizePhone($account->phone ?? null);
    if ($phoneNorm && strlen($phoneNorm) >= 6) {
        $last6 = substr($phoneNorm, -6);

        $candidates = Account::query()
            ->whereNotNull('phone')
            ->where('id', '!=', $account->id)
            ->where('phone', 'like', "%{$last6}%")
            ->get();

        foreach ($candidates as $acc) {
            if ($this->normalizePhone($acc->phone) !== $phoneNorm) {
                continue;
            }

            $accRoot = $this->groupRootIdForAccount($acc);

            if ($selfRoot && $accRoot && (int) $selfRoot === (int) $accRoot) {
                continue;
            }

            $result['phone'][$acc->id] = [
                'id'   => $acc->id,
                'name' => $acc->nombre_abreviado ?? $acc->name,
            ];
        }
    }

    $result['email'] = array_values($result['email']);
    $result['phone'] = array_values($result['phone']);

    return $result;
}

/**
 * Raíz de grupo (matriz) para una cuenta ya existente.
 */
private function groupRootIdForAccount(Account $acc): ?int
{
    if (! empty($acc->id) && array_key_exists($acc->id, $this->groupRootCache)) {
        return $this->groupRootCache[$acc->id];
    }

    $tipo = $acc->tipo_relacion_grupo ?? null;

    if ($tipo === 'filial' && ! empty($acc->parent_account_id)) {
        $root = $this->groupRootIdById((int) $acc->parent_account_id);

        $this->groupRootCache[$acc->id] = $root ?: (int) $acc->parent_account_id;

        return $this->groupRootCache[$acc->id];
    }

    if ($tipo === 'matriz' && ! empty($acc->id)) {
        $this->groupRootCache[$acc->id] = (int) $acc->id;

        return (int) $acc->id;
    }

    if (! empty($acc->id)) {
        $this->groupRootCache[$acc->id] = null;
    }

    return null;
}

/**
 * Raíz de grupo (matriz) para un ID.
 */
private function groupRootIdById(int $id): ?int
{
    if (array_key_exists($id, $this->groupRootCache)) {
        return $this->groupRootCache[$id];
    }

    $current = $id;
    $visited = [];

    while ($current && ! in_array($current, $visited, true)) {
        $visited[] = $current;

        $acc = Account::query()
            ->select(['id', 'tipo_relacion_grupo', 'parent_account_id'])
            ->find($current);

        if (! $acc) {
            break;
        }

        if (($acc->tipo_relacion_grupo ?? null) === 'filial' && ! empty($acc->parent_account_id)) {
            $current = (int) $acc->parent_account_id;
            continue;
        }

        if (($acc->tipo_relacion_grupo ?? null) === 'matriz') {
            $this->groupRootCache[$id] = (int) $acc->id;

            return (int) $acc->id;
        }

        // Independiente o sin relación
        $this->groupRootCache[$id] = null;
        return null;
    }

    $this->groupRootCache[$id] = null;
    return null;
}

private function normalizeEmail(?string $email): ?string
    {
        if (! $email) return null;
        $email = trim(mb_strtolower($email));
        return $email === '' ? null : $email;
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) return null;
        $phone = trim($phone);

        // Si hay varios teléfonos, nos quedamos con el primero
        $phone = preg_split('/[\/,;]/', $phone)[0] ?? $phone;

        // Sólo dígitos
        $digits = preg_replace('/\D+/', '', $phone);
        if (! $digits) return null;

        // Quitar prefijos comunes si vienen pegados (ej. 0034)
        if (str_starts_with($digits, '0034')) $digits = substr($digits, 4);
        if (str_starts_with($digits, '34') && strlen($digits) > 9) $digits = substr($digits, 2);

        return $digits;
    }


    private function availableCountries(string $column): Collection
    {
        return Account::query()
            ->select($column)
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter()
            ->values();
    }

    private function countryColumn(): ?string
    {
        if (Schema::hasColumn('accounts', 'country')) {
            return 'country';
        }

        if (Schema::hasColumn('accounts', 'billing_country')) {
            return 'billing_country';
        }

        return null;
    }

    private function hasLifecycleColumn(): bool
    {
        return Schema::hasColumn('accounts', 'lifecycle');
    }

    private function statusColumnName(): string
    {
        return Schema::hasColumn('accounts', 'estado') ? 'estado' : 'status';
    }

    private function groupParentOptions(?Account $exclude = null): Collection
    {
        $query = Account::query()
            ->select(['id', 'name', 'nombre_abreviado', 'country', 'tipo_relacion_grupo'])
            ->where('tipo_relacion_grupo', 'matriz')
            ->orderBy('name');

        if ($exclude) {
            $query->where('id', '!=', $exclude->id);
        }

        $parents = $query->get();

        if ($exclude && $exclude->parent && ! $parents->contains('id', $exclude->parent->id)) {
            $parents->push($exclude->parent);
        }

        return $parents;
    }

    /**
     * @return array<string, mixed>
     */
    private function persistableAttributes(AccountRequest $request): array
    {
        $model = new Account();

        if (! Schema::hasTable($model->getTable())) {
            return $request->validated();
        }

        $columns = Schema::getColumnListing($model->getTable());
        $allowed = array_intersect($model->getFillable(), $columns);

        return collect($request->validated())
            ->only($allowed)
            ->all();
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function handleLogoUpload(AccountRequest $request, array $attributes, ?Account $account = null): array
    {
        if (! $request->hasFile('logo')) {
            return $attributes;
        }

        $path = $request->file('logo')->store('accounts/logos', 'public');

        if ($account?->logo_path) {
            Storage::disk('public')->delete($account->logo_path);
        }

        $attributes['logo_path'] = $path;

        return $attributes;
    }

    private function mapTipoEntidad(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        return match (strtolower(trim($raw))) {
            'empresa privada', 'private', 'empresa_privada' => 'empresa_privada',
            'aapp', 'administracion publica', 'administración pública', 'public' => 'aapp',
            'sin animo de lucro', 'sin ánimo de lucro', 'sin_animo_de_lucro' => 'sin_animo_de_lucro',
            'corporacion de derecho publico', 'corporación de derecho público', 'corporacion_derecho_publico' => 'corporacion_derecho_publico',
            'particular' => 'particular',
            default => null,
        };
    }

    private function mapEstado(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        return match (strtolower(trim($raw))) {
            'inactivo', 'inactive' => 'inactivo',
            'activo', 'active', 'prospect', 'customer' => 'activo',
            default => null,
        };
    }

    private function extractFilters(Request $request): array
    {
        $search = trim((string) ($request->query('q', $request->input('q', ''))));
        $search = $search !== '' ? $search : null;

        $hasLifecycle = $this->hasLifecycleColumn();

        $lifecycle = $hasLifecycle ? trim((string) ($request->query('lifecycle', $request->input('lifecycle', '')))) : '';
        $lifecycle = $lifecycle !== '' ? $lifecycle : null;

        $country = trim((string) ($request->query('country', $request->input('country', ''))));
        $country = $country !== '' ? $country : null;

        return [
            'search'        => $search,
            'lifecycle'     => $lifecycle,
            'country'       => $country,
            'hasLifecycle'  => $hasLifecycle,
            'countryColumn' => $this->countryColumn(),
        ];
    }

    private function buildAccountsQuery(
        ?string $search,
        ?string $lifecycle,
        ?string $country,
        bool $hasLifecycle,
        ?string $countryColumn
    ): Builder {
        return Account::query()
            ->withCount('contacts')
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('legal_name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%");
                });
            })
            ->when($lifecycle && $hasLifecycle, fn (Builder $query) => $query->where('lifecycle', $lifecycle))
            ->when($country && $countryColumn, fn (Builder $query) => $query->where($countryColumn, $country));
    }

    private function bulkOrExportQuery(Request $request, array $validated): Builder
    {
        $selectAll = (bool) ($validated['select_all'] ?? false);

        if ($selectAll) {
            [
                'search'        => $search,
                'lifecycle'     => $lifecycle,
                'country'       => $country,
                'hasLifecycle'  => $hasLifecycle,
                'countryColumn' => $countryColumn,
            ] = $this->extractFilters($request);

            return $this->buildAccountsQuery($search, $lifecycle, $country, $hasLifecycle, $countryColumn);
        }

        return Account::query()->whereIn('id', $validated['ids'] ?? []);
    }

    private function updateOwnerUser(Builder $query, ?int $userId): ?int
    {
        if (! $userId) {
            return null;
        }

        return $query->update(['owner_user_id' => $userId]);
    }

    private function updateOwnerTeam(Builder $query, ?int $teamId): ?int
    {
        if (! $teamId) {
            return null;
        }

        return $query->update(['owner_team_id' => $teamId]);
    }
    /**
     * Datos para la vista rápida desde el listado (AJAX).
     * Devuelve las últimas Solicitudes / Peticiones / Pedidos vinculados a la cuenta.
     */
    public function quick(Account $account)
    {
        // Últimos registros (para mostrar en la vista rápida)
        $solicitudesQuery = Solicitud::query()->where('account_id', $account->id);
        $peticionesQuery  = Peticion::query()->where('account_id', $account->id);
        $pedidosQuery     = Pedido::query()->where('account_id', $account->id);

        $solicitudes = $solicitudesQuery
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'titulo', 'estado', 'prioridad', 'fecha_solicitud', 'created_at'])
            ->map(fn ($s) => [
                'id'        => $s->id,
                'titulo'    => $s->titulo,
                'estado'    => $s->estado,
                'prioridad' => $s->prioridad,
                'fecha'     => optional($s->fecha_solicitud)->format('Y-m-d'),
                'url'       => route('solicitudes.show', $s),
            ]);

        $peticiones = $peticionesQuery
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'titulo', 'estado', 'importe_total', 'moneda', 'created_at'])
            ->map(fn ($p) => [
                'id'           => $p->id,
                'titulo'       => $p->titulo,
                'estado'       => $p->estado,
                'importe_total'=> $p->importe_total,
                'moneda'       => $p->moneda,
                'url'          => route('peticiones.show', $p),
            ]);

        $pedidos = $pedidosQuery
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'numero', 'descripcion', 'estado_pedido', 'importe_total', 'moneda', 'fecha_pedido', 'created_at'])
            ->map(fn ($p) => [
                'id'           => $p->id,
                'numero'       => $p->numero,
                'descripcion'  => $p->descripcion,
                'estado'       => $p->estado_pedido,
                'importe_total'=> $p->importe_total,
                'moneda'       => $p->moneda,
                'fecha'        => optional($p->fecha_pedido)->format('Y-m-d'),
                'url'          => route('pedidos.show', $p),
            ]);

        return response()->json([
            'solicitudes' => [
                'total' => $solicitudesQuery->count(),
                'items' => $solicitudes,
            ],
            'peticiones' => [
                'total' => $peticionesQuery->count(),
                'items' => $peticiones,
            ],
            'pedidos' => [
                'total' => $pedidosQuery->count(),
                'items' => $pedidos,
            ],
        ]);
    }

}
