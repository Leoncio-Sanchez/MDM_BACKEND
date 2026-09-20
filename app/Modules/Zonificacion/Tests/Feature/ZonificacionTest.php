<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Tests\Feature;

use App\Modules\Zonificacion\Infrastructure\Persistence\Models\ZonificacionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZonificacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_listar_zonificaciones(): void
    {
        ZonificacionModel::create([
            'codigo' => 'TEST1',
            'nombre' => 'Test 1',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->getJson('/api/lf-itse/zonificaciones');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_crear_zonificacion(): void
    {

        $data = ['codigo' => 'TEST2', 'nombre' => 'Test 2', 'esta_activo' => true];

        $response = $this->postJson('/api/lf-itse/zonificaciones', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('zonificaciones', ['codigo' => 'TEST2']);
    }

    public function test_puede_actualizar_zonificacion(): void
    {
        $m = ZonificacionModel::create([
            'codigo' => 'TEST1',
            'nombre' => 'Test 1',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $data = ['codigo' => 'TEST1', 'nombre' => 'Test Updated', 'esta_activo' => false];

        $response = $this->putJson('/api/lf-itse/zonificaciones/'.$m->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('zonificaciones', ['nombre' => 'Test Updated']);
    }

    public function test_puede_eliminar_zonificacion(): void
    {
        $m = ZonificacionModel::create([
            'codigo' => 'TEST1',
            'nombre' => 'Test 1',
            'esta_activo' => true,
            'fecha_digitacion' => '2026-04-14 13:41:31',
            'usuario_id' => 1,
        ]);

        $response = $this->deleteJson('/api/lf-itse/zonificaciones/'.$m->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('zonificaciones', ['id' => $m->id]);
    }
}
