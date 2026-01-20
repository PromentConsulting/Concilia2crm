<?php

namespace App\Http\Controllers;

use App\Models\AlertSetting;
use App\Models\Solicitud;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AlertaController extends Controller
{
    /**
     * Lista de alertas actuales del usuario.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Configuración del usuario (si no existe, se crea con valores por defecto)
        $settings = AlertSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notify_overdue_tasks'    => true,
                'notify_open_solicitudes' => true,
            ]
        );

        $alerts = [];

        // 1) Tareas vencidas
        if ($settings->notify_overdue_tasks) {
            $overdueTasks = Tarea::query()
                ->where('owner_user_id', $user->id)
                ->where('estado', '!=', 'completada')
                ->whereNotNull('fecha_vencimiento')
                ->where('fecha_vencimiento', '<', now())
                ->orderBy('fecha_vencimiento')
                ->limit(20)
                ->get();

            foreach ($overdueTasks as $tarea) {
                $alerts[] = [
                    'tipo'    => 'Tarea vencida',
                    'mensaje' => "La tarea «{$tarea->titulo}» está vencida desde " .
                        optional($tarea->fecha_vencimiento)->format('d/m/Y'),
                    'url'     => route('tareas.edit', $tarea),
                ];
            }
        }

        // 2) Solicitudes abiertas asignadas al usuario
        if ($settings->notify_open_solicitudes) {
            $openSolicitudes = Solicitud::query()
                ->where('owner_user_id', $user->id)
                ->whereIn('estado', ['abierta', 'en_proceso'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            foreach ($openSolicitudes as $solicitud) {
                $alerts[] = [
                    'tipo'    => 'Solicitud abierta',
                    'mensaje' => "La solicitud «{$solicitud->asunto}» sigue en estado " .
                        str_replace('_',' ', $solicitud->estado),
                    'url'     => route('solicitudes.show', $solicitud),
                ];
            }
        }

        return view('alertas.index', [
            'alerts'   => $alerts,
            'settings' => $settings,
        ]);
    }

    /**
     * Pantalla de configuración de alertas.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $settings = AlertSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'notify_overdue_tasks'    => true,
                'notify_open_solicitudes' => true,
            ]
        );

        return view('alertas.edit', [
            'settings' => $settings,
        ]);
    }

    /**
     * Guardar configuración de alertas.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $settings = AlertSetting::firstOrCreate(['user_id' => $user->id]);

        $settings->notify_overdue_tasks    = $request->boolean('notify_overdue_tasks');
        $settings->notify_open_solicitudes = $request->boolean('notify_open_solicitudes');
        $settings->save();

        return redirect()
            ->route('alertas.index')
            ->with('status', 'Configuración de alertas actualizada correctamente.');
    }
}
