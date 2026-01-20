<?php

namespace App\Http\Controllers;

use App\Models\SolicitudRegla;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SolicitudReglaController extends Controller
{
    /**
     * Listado y formulario de reglas de asignación de solicitudes.
     */
    public function index(): View
    {
        $reglas = SolicitudRegla::with('owner')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        // Opciones básicas para los selects
        $origenes = [
            'web'      => 'Web',
            'telefono' => 'Teléfono',
            'email'    => 'Email',
            'otro'     => 'Otro',
        ];

        $prioridades = [
            'alta'   => 'Alta',
            'normal' => 'Normal',
            'baja'   => 'Baja',
        ];

        $estados = [
            'nueva'      => 'Nueva',
            'abierta'    => 'Abierta',
            'en_proceso' => 'En proceso',
            'cerrada'    => 'Cerrada',
        ];

        return view('solicitudes.reglas', [
            'reglas'      => $reglas,
            'users'       => $users,
            'origenes'    => $origenes,
            'prioridades' => $prioridades,
            'estados'     => $estados,
        ]);
    }

    /**
     * Crear nueva regla.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'        => ['required', 'string', 'max:255'],
            'descripcion'   => ['nullable', 'string'],
            'origen'        => ['nullable', 'string', 'max:255'],
            'prioridad'     => ['nullable', 'string', 'max:255'],
            'estado'        => ['nullable', 'string', 'max:255'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'activo'        => ['sometimes', 'boolean'],
            'orden'         => ['nullable', 'integer', 'min:0'],
        ]);

        $data['activo'] = $request->boolean('activo');

        SolicitudRegla::create($data);

        return redirect()
            ->route('solicitudes.reglas.index')
            ->with('status', 'Regla creada correctamente.');
    }

    /**
     * Borrar una regla.
     */
    public function destroy(SolicitudRegla $regla): RedirectResponse
    {
        $regla->delete();

        return redirect()
            ->route('solicitudes.reglas.index')
            ->with('status', 'Regla eliminada.');
    }
}
