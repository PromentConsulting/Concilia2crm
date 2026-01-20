<?php

namespace Tests\Unit;

use App\Models\Solicitud;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitudEstadoHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_historial_al_cambiar_estado(): void
    {
        $solicitud = Solicitud::create([
            'titulo' => 'Lead demo',
            'estado' => 'pendiente_asignacion',
            'origen' => 'web',
            'prioridad' => 'media',
        ]);

        $solicitud->actualizarEstado('ganado', 'contactado', 'Cierre exitoso');

        $this->assertDatabaseHas('solicitud_estado_historial', [
            'solicitud_id' => $solicitud->id,
            'estado_nuevo' => 'ganado',
            'motivo_cierre' => 'contactado',
        ]);
        $this->assertNotNull($solicitud->fresh()->closed_at);
    }
}