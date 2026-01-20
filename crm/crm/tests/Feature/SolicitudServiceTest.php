<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Services\SolicitudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitudServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reusa_contacto_existente_por_email(): void
    {
        $contacto = Contact::factory()->create([
            'primary_email' => 'demo@example.com',
        ]);

        $service = app(SolicitudService::class);

        $solicitud = $service->createFromPayload([
            'email' => 'demo@example.com',
            'titulo' => 'Demo',
            'descripcion' => 'Solicitud de prueba',
            'origen' => 'web',
        ]);

        $this->assertEquals($contacto->id, $solicitud->contact_id);
        $this->assertDatabaseCount('solicitudes', 1);
    }

    public function test_crea_contacto_y_cuenta_si_no_existe(): void
    {
        $service = app(SolicitudService::class);

        $solicitud = $service->createFromPayload([
            'email' => 'nuevo@example.com',
            'nombre' => 'Nuevo',
            'apellidos' => 'Contacto',
            'razon_social' => 'Empresa Demo',
            'origen' => 'mautic',
        ]);

        $this->assertNotNull($solicitud->contact_id);
        $this->assertNotNull($solicitud->account_id);
        $this->assertDatabaseHas('accounts', ['name' => 'Empresa Demo']);
    }
}