<?php

namespace App\Http\Controllers;

use App\Models\SolicitudAssignmentRule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudAssignmentRuleController extends Controller
{
    public function index(): View
    {
        $rules = SolicitudAssignmentRule::query()
            ->with('owner')
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        $users = User::orderBy('name')->get();

        // Campos sobre los que se pueden crear reglas
        $availableFields = [
            'origen'          => 'Origen de la solicitud',
            'estado'          => 'Estado de la solicitud',
            'prioridad'       => 'Prioridad',
            'account_country' => 'País de la cuenta',
            'account_state'   => 'Provincia de la cuenta',
            'account_city'    => 'Localidad de la cuenta',
        ];

        $operators = [
            'equals'       => 'es igual a',
            'not_equals'   => 'no es igual a',
            'contains'     => 'contiene',
            'not_contains' => 'no contiene',
            'starts_with'  => 'empieza por',
            'ends_with'    => 'termina en',
        ];

        return view('solicitudes.reglas.index', compact(
            'rules',
            'users',
            'availableFields',
            'operators'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'field'         => ['required', 'string'],
            'operator'      => ['required', 'string'],
            'value'         => ['nullable', 'string', 'max:255'],
            'owner_user_id' => ['required', 'exists:users,id'],
            'priority'      => ['nullable', 'integer', 'min:1', 'max:9999'],
            'active'        => ['sometimes', 'boolean'],
        ]);

        $data['priority'] = $data['priority'] ?? 100;
        $data['active']   = $request->boolean('active');

        SolicitudAssignmentRule::create($data);

        return redirect()
            ->route('solicitudes.reglas.index')
            ->with('status', 'Regla de asignación creada correctamente.');
    }

    public function update(Request $request, SolicitudAssignmentRule $regla): RedirectResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'field'         => ['required', 'string'],
            'operator'      => ['required', 'string'],
            'value'         => ['nullable', 'string', 'max:255'],
            'owner_user_id' => ['required', 'exists:users,id'],
            'priority'      => ['nullable', 'integer', 'min:1', 'max:9999'],
            'active'        => ['sometimes', 'boolean'],
        ]);

        $data['priority'] = $data['priority'] ?? 100;
        $data['active']   = $request->boolean('active');

        $regla->update($data);

        return redirect()
            ->route('solicitudes.reglas.index')
            ->with('status', 'Regla de asignación actualizada correctamente.');
    }

    public function destroy(SolicitudAssignmentRule $regla): RedirectResponse
    {
        $regla->delete();

        return redirect()
            ->route('solicitudes.reglas.index')
            ->with('status', 'Regla de asignación eliminada.');
    }
}
