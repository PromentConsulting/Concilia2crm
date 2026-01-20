<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TareaController extends Controller
{
    public function index(Request $request): View
    {
        $tipo   = $request->query('tipo');
        $estado = $request->query('estado');
        $owner  = $request->query('owner');

        $tareas = Tarea::query()
            ->with(['owner', 'account', 'contact', 'solicitud'])
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($owner, fn ($q) => $q->where('owner_user_id', $owner))
            ->orderBy('fecha_vencimiento')
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('tareas.index', [
            'tareas'  => $tareas,
            'users'   => $users,
            'filters' => [
                'tipo'   => $tipo,
                'estado' => $estado,
                'owner'  => $owner,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $users    = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        // no usamos "name" de la BBDD de contactos (no existe),
        // luego en la vista montamos el nombre completo
        $contacts = Contact::orderBy('id')->get();

        // Prefills cuando venimos desde cuenta/solicitud/etc
        $prefill = [
            'account_id'   => $request->query('account_id'),
            'contact_id'   => $request->query('contact_id'),
            'solicitud_id' => $request->query('solicitud_id'),
            'peticion_id'  => $request->query('peticion_id'),
            'pedido_id'    => $request->query('pedido_id'),
        ];

        // Si venimos desde una solicitud, rellenamos cuenta y contacto automáticamente
        if ($prefill['solicitud_id']) {
            $solicitud = Solicitud::find($prefill['solicitud_id']);

            if ($solicitud) {
                if (empty($prefill['account_id']) && $solicitud->account_id) {
                    $prefill['account_id'] = $solicitud->account_id;
                }
                if (empty($prefill['contact_id']) && $solicitud->contact_id) {
                    $prefill['contact_id'] = $solicitud->contact_id;
                }
            }
        }

        return view('tareas.create', [
            'tarea'    => null,
            'users'    => $users,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'prefill'  => $prefill,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tipo'              => ['required', 'string', 'max:50'],
            'titulo'            => ['required', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'estado'            => ['required', 'string', 'max:50'],
            'fecha_inicio'      => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],

            'owner_user_id'     => ['nullable', 'exists:users,id'],

            'account_id'        => ['nullable', 'exists:accounts,id'],
            'contact_id'        => ['nullable', 'exists:contacts,id'],
            'solicitud_id'      => ['nullable', 'exists:solicitudes,id'],
            'peticion_id'       => ['nullable', 'exists:peticiones,id'],
            'pedido_id'         => ['nullable', 'exists:pedidos,id'],
        ]);

        // Si no viene owner, le ponemos el usuario logueado
        if (! isset($data['owner_user_id']) && $request->user()) {
            $data['owner_user_id'] = $request->user()->id;
        }

        // Si viene vinculada a una solicitud pero sin cuenta/contacto, los heredamos de la solicitud
        if (! empty($data['solicitud_id']) && (empty($data['account_id']) || empty($data['contact_id']))) {
            $solicitud = Solicitud::find($data['solicitud_id']);

            if ($solicitud) {
                if (empty($data['account_id']) && $solicitud->account_id) {
                    $data['account_id'] = $solicitud->account_id;
                }
                if (empty($data['contact_id']) && $solicitud->contact_id) {
                    $data['contact_id'] = $solicitud->contact_id;
                }
            }
        }

        Tarea::create($data);

        return redirect()
            ->route('tareas.index')
            ->with('status', 'Tarea creada correctamente.');
    }

    public function edit(Tarea $tarea): View
    {
        $users    = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('id')->get();

        return view('tareas.edit', [
            'tarea'    => $tarea,
            'users'    => $users,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'prefill'  => [
                'account_id'   => $tarea->account_id,
                'contact_id'   => $tarea->contact_id,
                'solicitud_id' => $tarea->solicitud_id,
                'peticion_id'  => $tarea->peticion_id,
                'pedido_id'    => $tarea->pedido_id,
            ],
        ]);
    }

    public function update(Request $request, Tarea $tarea): RedirectResponse
    {
        $data = $request->validate([
            'tipo'              => ['required', 'string', 'max:50'],
            'titulo'            => ['required', 'string', 'max:255'],
            'descripcion'       => ['nullable', 'string'],
            'estado'            => ['required', 'string', 'max:50'],
            'fecha_inicio'      => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],

            'owner_user_id'     => ['nullable', 'exists:users,id'],

            'account_id'        => ['nullable', 'exists:accounts,id'],
            'contact_id'        => ['nullable', 'exists:contacts,id'],
            'solicitud_id'      => ['nullable', 'exists:solicitudes,id'],
            'peticion_id'       => ['nullable', 'exists:peticiones,id'],
            'pedido_id'         => ['nullable', 'exists:pedidos,id'],
        ]);

        if (! isset($data['owner_user_id']) && $request->user()) {
            $data['owner_user_id'] = $request->user()->id;
        }

        if ($data['estado'] === 'completada' && ! $tarea->fecha_completada) {
            $data['fecha_completada'] = now();
        }

        $tarea->update($data);

        return redirect()
            ->route('tareas.index')
            ->with('status', 'Tarea actualizada correctamente.');
    }

    public function destroy(Tarea $tarea): RedirectResponse
    {
        $tarea->delete();

        return redirect()
            ->route('tareas.index')
            ->with('status', 'Tarea eliminada.');
    }
}
