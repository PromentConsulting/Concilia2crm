<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeticionRequest;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Contact;
use App\Models\Peticion;
use App\Models\Solicitud;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeticionController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda  = trim((string) $request->query('q', '')) ?: null;
        $estado    = trim((string) $request->query('estado', '')) ?: null;

        $peticiones = Peticion::query()
            ->with([
                'cuenta:id,name',
                'contacto',
                'solicitud:id,asunto',
                'owner:id,name',
            ])
            ->when($busqueda, function (Builder $query) use ($busqueda) {
                $query->where(function (Builder $sub) use ($busqueda) {
                    $sub->where('titulo', 'like', "%{$busqueda}%")
                        ->orWhere('codigo', 'like', "%{$busqueda}%")
                        ->orWhere('descripcion', 'like', "%{$busqueda}%")
                        ->orWhereHas('cuenta', fn (Builder $q) => $q->where('name', 'like', "%{$busqueda}%"))
                        ->orWhereHas('contacto', function (Builder $q) use ($busqueda) {
                            $q->where(function (Builder $q2) use ($busqueda) {
                                $q2->where('first_name', 'like', "%{$busqueda}%")
                                   ->orWhere('last_name', 'like', "%{$busqueda}%")
                                   ->orWhere('email', 'like', "%{$busqueda}%");
                            });
                        })
                        ->orWhereHas('solicitud', fn (Builder $q) => $q->where('asunto', 'like', "%{$busqueda}%"));
                });
            })
            ->when($estado, fn (Builder $q) => $q->where('estado', $estado))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('peticiones.index', [
            'peticiones' => $peticiones,
            'filtros' => [
                'q'      => $busqueda,
                'estado' => $estado,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $solicitudId = $request->integer('solicitud_id') ?: null;
        $solicitud   = $solicitudId ? Solicitud::query()->with(['cuenta', 'contacto'])->find($solicitudId) : null;

        $cuentas = Account::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $contactos = Contact::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $usuarios = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('peticiones.create', [
            'cuentas'      => $cuentas,
            'contactos'    => $contactos,
            'solicitud'    => $solicitud,
            'usuarios'     => $usuarios,
            'subvenciones' => config('peticiones.subvenciones'),
            'tiposProyecto'=> config('peticiones.tipos_proyecto'),
        ]);
    }

    public function store(PeticionRequest $request): RedirectResponse
    {
        $peticion = Peticion::create($request->validated());

        AuditLog::create([
            'user_id'    => $request->user()?->id,
            'model_type' => Peticion::class,
            'model_id'   => $peticion->id,
            'event'      => 'created',
            'old_values' => null,
            'new_values' => $peticion->getAttributes(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('peticiones.show', $peticion)
            ->with('status', 'Petición creada correctamente.');
    }

    public function show(Peticion $peticion): View
    {
        $peticion->load([
            'tareas' => fn ($q) => $q
                ->orderBy('fecha_vencimiento')
                ->orderBy('created_at', 'desc'),
            'tareas.owner',
            'lineas.service.category',
            'owner',
            'creador',
            'documentos' => fn ($q) => $q->orderByDesc('fecha_documento')->orderByDesc('created_at'),
            'documentos.owner',
        ]);

        $servicios = Service::query()
            ->with('category')
            ->where('estado', '!=', 'inactivo')
            ->orderBy('referencia')
            ->get(['id', 'referencia', 'descripcion', 'precio', 'service_category_id']);

        $logs = AuditLog::query()
            ->where('model_type', Peticion::class)
            ->where('model_id', $peticion->id)
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


        return view('peticiones.show', [
            'peticion'  => $peticion,
            'servicios' => $servicios,
            'categoriasServicios' => $servicios->pluck('category')->filter()->unique('id'),
            'subvenciones' => config('peticiones.subvenciones'),
            'tiposProyecto'=> config('peticiones.tipos_proyecto'),
            'logs' => $logs,
            'logEntries' => $logEntries,
        ]);
    }

    public function edit(Peticion $peticion): View
    {
        $cuentas = Account::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $contactos = Contact::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        $usuarios = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('peticiones.edit', [
            'peticion'  => $peticion,
            'cuentas'   => $cuentas,
            'contactos' => $contactos,
            'usuarios'  => $usuarios,
            'subvenciones' => config('peticiones.subvenciones'),
            'tiposProyecto'=> config('peticiones.tipos_proyecto'),
        ]);
    }

    public function update(PeticionRequest $request, Peticion $peticion): RedirectResponse
    {
        $original = $peticion->getOriginal();
        $peticion->update($request->validated());

        AuditLog::create([
            'user_id'    => $request->user()?->id,
            'model_type' => Peticion::class,
            'model_id'   => $peticion->id,
            'event'      => 'updated',
            'old_values' => $original,
            'new_values' => $peticion->getChanges(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('peticiones.show', $peticion)
            ->with('status', 'Petición actualizada correctamente.');
    }

    public function destroy(Peticion $peticion): RedirectResponse
    {
        $peticion->delete();

        return redirect()
            ->route('peticiones.index')
            ->with('status', 'Petición eliminada.');
    }
}
