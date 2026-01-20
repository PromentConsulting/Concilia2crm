<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Contact;
use App\Services\CampaignSegmentBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignSegmentBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_excluye_contactos_con_baja_o_rebote(): void
    {
        $account = Account::factory()->create(['estado' => 'activo', 'provincia' => 'Madrid']);

        $contactActivo = Contact::factory()->create([
            'primary_email' => 'ok@example.com',
            'estado_contacto' => 'activo',
            'account_id' => $account->id,
            'estado_rgpd' => 'ok',
        ]);

        Contact::factory()->create([
            'primary_email' => 'baja@example.com',
            'estado_contacto' => 'baja_marketing',
            'account_id' => $account->id,
            'estado_rgpd' => 'ok',
        ]);

        $builder = new CampaignSegmentBuilder();
        $query = $builder->build([
            'account_estado' => ['activo'],
            'account_provincia' => 'Madrid',
        ]);

        $this->assertEquals(1, $query->count());
        $this->assertEquals($contactActivo->id, $query->first()->id);
    }
    public function test_filtra_por_flags_de_cuenta(): void
    {
        $accountCalidad = Account::factory()->create([
            'estado' => 'activo',
            'quality' => true,
            'rse' => true,
        ]);

        $accountSinCalidad = Account::factory()->create([
            'estado' => 'activo',
            'quality' => false,
            'rse' => false,
        ]);

        $contactCalidad = Contact::factory()->create([
            'primary_email' => 'calidad@example.com',
            'estado_contacto' => 'activo',
            'account_id' => $accountCalidad->id,
        ]);

        Contact::factory()->create([
            'primary_email' => 'otro@example.com',
            'estado_contacto' => 'activo',
            'account_id' => $accountSinCalidad->id,
        ]);

        $builder = new CampaignSegmentBuilder();
        $query = $builder->build([
            'account_quality' => true,
            'account_rse' => true,
        ]);

        $this->assertEquals(1, $query->count());
        $this->assertEquals($contactCalidad->id, $query->first()->id);
    }
}