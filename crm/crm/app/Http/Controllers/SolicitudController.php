<?php

namespace App\Http\Controllers;

use App\Http\Requests\SolicitudRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Solicitud;
use App\Models\Team;
use App\Models\User;
use App\Services\SolicitudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SolicitudController extends Controller
{
    public function __construct(private SolicitudService $service)
    {
    }

    public function index(Request $request): View
    {
        [
            'q'             => $busqueda,
            'estado'        => $estado,
            'origen'        => $origen,
            'prioridad'     => $prioridad,
            'owner_user_id' => $ownerUserId,
        ] = $this->extractFilters($request);

        $views              = collect();
        $solicitudViewClass = \App\Models\SolicitudView::class;

        if (class_exists($solicitudViewClass)) {
            $views = $solicitudViewClass::query()
                ->where('user_id', optional($request->user())->id)
                ->orderBy('name')
                ->get();
        }

        $activeView = null;
        $vistaId    = $request->query('vista_id');

        if ($vistaId && $views->isNotEmpty()) {
            $activeView = $views->firstWhere('id', (int) $vistaId);
        } elseif ($views->isNotEmpty()) {
            $activeView = $views->firstWhere('is_default', true);
        }

        // Aplicar filtros de vista si no hay filtros manuales
        if (! $busqueda && ! $estado && ! $origen && ! $prioridad && ! $ownerUserId && $activeView && is_array($activeView->filters)) {
            $busqueda    = $activeView->filters['q'] ?? null;
            $estado      = $activeView->filters['estado'] ?? null;
            $origen      = $activeView->filters['origen'] ?? null;
            $prioridad   = $activeView->filters['prioridad'] ?? null;
            $ownerUserId = $activeView->filters['owner_user_id'] ?? null;
        }

        $solicitudes = $this->buildSolicitudesQuery($busqueda, $estado, $origen, $prioridad, $ownerUserId)
            ->with([
                'account:id,name',
                'contact:id,first_name,last_name',
                'owner:id,name',
            ])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);
        $teams    = Team::query()->orderBy('name')->get(['id', 'name']);

        return view('solicitudes.index', [
            'solicitudes' => $solicitudes,
            'filtros' => [
                'q'             => $busqueda,
                'estado'        => $estado,
                'origen'        => $origen,
                'prioridad'     => $prioridad,
                'owner_user_id' => $ownerUserId,
            ],
            'usuarios'   => $usuarios,
            'teams'      => $teams,
            'views'      => $views,
            'activeView' => $activeView,
        ]);
    }

    public function create(): View
    {
        $cuentas = Account::query()->orderBy('name')->get(['id', 'name']);
        $contactos = Contact::query()->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);

        return view('solicitudes.create', compact('cuentas', 'contactos', 'usuarios'));
    }

    public function store(SolicitudRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! isset($data['owner_user_id']) && $request->user()) {
            $data['owner_user_id'] = $request->user()->id;
        }

        $solicitud = $this->service->createFromPayload($data);

        return redirect()
            ->route('solicitudes.show', $solicitud)
            ->with('status', 'Solicitud creada correctamente.');
    }

    public function show(Solicitud $solicitud): View
    {
        $solicitud->load([
            'account',
            'contact',
            'owner',
            'documentos',
            'audits.user',
            'tareas.owner',
            'tareas' => fn ($q) => $q->orderBy('fecha_vencimiento')->orderByDesc('created_at'),
        ]);

        return view('solicitudes.show', [
            'solicitud' => $solicitud,
            'tab' => request('tab', 'resumen'),
        ]);
    }

    public function edit(Solicitud $solicitud): View
    {
        $cuentas = Account::query()->orderBy('name')->get(['id', 'name']);
        $contactos = Contact::query()->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $usuarios = User::query()->orderBy('name')->get(['id', 'name']);

        return view('solicitudes.edit', compact('solicitud', 'cuentas', 'contactos', 'usuarios'));
    }

    public function update(SolicitudRequest $request, Solicitud $solicitud): RedirectResponse
    {
        $data = $request->validated();

        if (! isset($data['owner_user_id']) && $request->user()) {
            $data['owner_user_id'] = $request->user()->id;
        }

        $solicitud->update($data);

        return redirect()
            ->route('solicitudes.show', $solicitud)
            ->with('status', 'Solicitud actualizada correctamente.');
    }

    public function destroy(Solicitud $solicitud): RedirectResponse
    {
        $solicitud->delete();

        return redirect()
            ->route('solicitudes.index')
            ->with('status', 'Solicitud eliminada.');
    }

    /**
     * Acciones masivas (selección + select_all) igual patrón de Accounts.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action'     => ['required', 'string', 'in:set_estado,set_prioridad,assign_owner_user,assign_owner_team,delete'],
            'select_all' => ['nullable', 'boolean'],

            'ids'   => ['nullable', 'array', 'required_without:select_all'],
            'ids.*' => ['integer'],

            // Destinos (to_*)
            'to_estado'        => ['nullable', 'string', Rule::in(Solicitud::ESTADOS)],
            'to_prioridad'     => ['nullable', 'string', Rule::in(Solicitud::PRIORIDADES)],
            'to_owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'to_owner_team_id' => ['nullable', 'integer', 'exists:teams,id'],

            // Filtros actuales (como en index)
            'q'            => ['nullable', 'string'],
            'estado'       => ['nullable', 'string'],
            'origen'       => ['nullable', 'string'],
            'prioridad'    => ['nullable', 'string'],
            'owner_user_id'=> ['nullable', 'integer'],
        ]);

        $query = $this->bulkQuery($request, $validated);

        $updated = 0;

        // Iteramos modelos para disparar events/audits + historial de estado
        $query->orderBy('id')->chunkById(200, function ($chunk) use (&$updated, $validated) {
            foreach ($chunk as $solicitud) {
                switch ($validated['action']) {
                    case 'set_estado':
                        if (!($validated['to_estado'] ?? null)) break;
                        $solicitud->actualizarEstado($validated['to_estado']);
                        $updated++;
                        break;

                    case 'set_prioridad':
                        if (!($validated['to_prioridad'] ?? null)) break;
                        $solicitud->update(['prioridad' => $validated['to_prioridad']]);
                        $updated++;
                        break;

                    case 'assign_owner_user':
                        if (!($validated['to_owner_user_id'] ?? null)) break;
                        $solicitud->update(['owner_user_id' => (int) $validated['to_owner_user_id']]);
                        $updated++;
                        break;

                    case 'assign_owner_team':
                        if (!($validated['to_owner_team_id'] ?? null)) break;
                        $solicitud->update(['owner_team_id' => (int) $validated['to_owner_team_id']]);
                        $updated++;
                        break;

                    case 'delete':
                        $solicitud->delete();
                        $updated++;
                        break;
                }
            }
        });

        return back()->with('status', "Acción aplicada sobre {$updated} solicitudes.");
    }

    private function bulkQuery(Request $request, array $validated): Builder
    {
        $selectAll = (bool) ($validated['select_all'] ?? false);

        if ($selectAll) {
            [
                'q'             => $busqueda,
                'estado'        => $estado,
                'origen'        => $origen,
                'prioridad'     => $prioridad,
                'owner_user_id' => $ownerUserId,
            ] = $this->extractFilters($request);

            return $this->buildSolicitudesQuery($busqueda, $estado, $origen, $prioridad, $ownerUserId);
        }

        return Solicitud::query()->whereIn('id', $validated['ids'] ?? []);
    }

    private function buildSolicitudesQuery(
        ?string $busqueda,
        ?string $estado,
        ?string $origen,
        ?string $prioridad,
        ?int $ownerUserId
    ): Builder {
        return Solicitud::query()
            ->when($busqueda, function (Builder $q) use ($busqueda) {
                $q->where(function (Builder $sub) use ($busqueda) {
                    $sub->where('titulo', 'like', "%{$busqueda}%")
                        ->orWhere('descripcion', 'like', "%{$busqueda}%")
                        ->orWhereHas('account', fn (Builder $qa) => $qa->where('name', 'like', "%{$busqueda}%"))
                        ->orWhereHas('contact', function (Builder $qc) use ($busqueda) {
                            $qc->where('first_name', 'like', "%{$busqueda}%")
                               ->orWhere('last_name', 'like', "%{$busqueda}%");
                        });
                });
            })
            ->when($estado, fn (Builder $q) => $q->where('estado', $estado))
            ->when($origen, fn (Builder $q) => $q->where('origen', $origen))
            ->when($prioridad, fn (Builder $q) => $q->where('prioridad', $prioridad))
            ->when($ownerUserId, fn (Builder $q) => $q->where('owner_user_id', $ownerUserId));
    }

    private function extractFilters(Request $request): array
    {
        $q = trim((string) $request->query('q', $request->input('q', '')));
        $q = $q !== '' ? $q : null;

        $estado = trim((string) $request->query('estado', $request->input('estado', '')));
        $estado = $estado !== '' ? $estado : null;

        $origen = trim((string) $request->query('origen', $request->input('origen', '')));
        $origen = $origen !== '' ? $origen : null;

        $prioridad = trim((string) $request->query('prioridad', $request->input('prioridad', '')));
        $prioridad = $prioridad !== '' ? $prioridad : null;

        $owner = $request->query('owner_user_id', $request->input('owner_user_id'));
        $owner = is_numeric($owner) ? (int) $owner : null;

        return [
            'q'             => $q,
            'estado'        => $estado,
            'origen'        => $origen,
            'prioridad'     => $prioridad,
            'owner_user_id' => $owner,
        ];
    }
}
