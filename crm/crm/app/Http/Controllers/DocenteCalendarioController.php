<?php

namespace App\Http\Controllers;

use App\Models\DocenteDisponibilidad;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteCalendarioController extends Controller
{
    public function index(Request $request): View
    {
        $docentes = User::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'docente'))
            ->with([
                'disponibilidades' => fn ($q) => $q->orderBy('inicio'),
                'horariosFormacion' => fn ($q) => $q->orderBy('inicio'),
            ])
            ->orderBy('name')
            ->get();

        $events = $docentes->flatMap(function (User $docente) {
            $disponibilidades = $docente->disponibilidades->map(function (DocenteDisponibilidad $bloque) use ($docente) {
                $esDisponible = $bloque->tipo === 'disponible';
                return [
                    'title' => $docente->name . ' · ' . ($esDisponible ? 'Disponible' : 'No disponible'),
                    'start' => $bloque->inicio->toIso8601String(),
                    'end' => $bloque->fin->toIso8601String(),
                    'color' => $esDisponible ? '#16a34a' : '#dc2626',
                ];
            });


            $formaciones = $docente->horariosFormacion->map(function ($horario) use ($docente) {
                return [
                    'title' => $docente->name . ' · Pedido #' . $horario->pedido_id,
                    'start' => $horario->inicio->toIso8601String(),
                    'end' => $horario->fin->toIso8601String(),
                    'color' => '#2563eb',
                ];
            });

            return $disponibilidades->merge($formaciones);
        })->values();

        return view('docentes.calendario.index', [
            'docentes' => $docentes,
            'events' => $events,
        ]);
    }

    public function show(User $docente): View
    {
        $docente->load([
            'disponibilidades' => fn ($q) => $q->orderBy('inicio'),
            'horariosFormacion' => fn ($q) => $q->orderBy('inicio'),
        ]);

        $events = $docente->disponibilidades->map(function (DocenteDisponibilidad $bloque) {
            $esDisponible = $bloque->tipo === 'disponible';
            return [
                'title' => $esDisponible ? 'Disponible' : 'No disponible',
                'start' => $bloque->inicio->toIso8601String(),
                'end' => $bloque->fin->toIso8601String(),
                'color' => $esDisponible ? '#16a34a' : '#dc2626',
            ];
        })->values();

        $formaciones = $docente->horariosFormacion->map(function ($horario) {
            return [
                'title' => 'Formación · Pedido #' . $horario->pedido_id,
                'start' => $horario->inicio->toIso8601String(),
                'end' => $horario->fin->toIso8601String(),
                'color' => '#2563eb',
            ];
        })->values();

        return view('docentes.calendario.show', [
            'docente' => $docente,
            'events' => $events->merge($formaciones)->values(),
        ]);
    }

    public function store(Request $request, User $docente): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->is_admin && $user->id !== $docente->id)) {
            return redirect()
                ->route('docentes.calendario.show', $docente)
                ->with('status', 'No tienes permisos para editar este calendario.');
        }

        $data = $request->validate([
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:inicio'],
            'tipo' => ['required', 'in:disponible,no_disponible'],
            'nota' => ['nullable', 'string', 'max:255'],
        ]);

        $data['user_id'] = $docente->id;

        DocenteDisponibilidad::create($data);

        return redirect()
            ->route('docentes.calendario.show', $docente)
            ->with('status', 'Disponibilidad actualizada.');
    }

    public function destroy(Request $request, DocenteDisponibilidad $disponibilidad): RedirectResponse
    {
        $user = $request->user();

        if (! $user || (! $user->is_admin && $user->id !== $disponibilidad->user_id)) {
            return redirect()
                ->route('docentes.calendario.show', $disponibilidad->user_id)
                ->with('status', 'No tienes permisos para eliminar este bloque.');
        }

        $docenteId = $disponibilidad->user_id;
        $disponibilidad->delete();

        return redirect()
            ->route('docentes.calendario.show', $docenteId)
            ->with('status', 'Bloque eliminado.');
    }
}