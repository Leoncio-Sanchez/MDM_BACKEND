<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Tests\Feature;

use App\Modules\NivelRiesgo\Infrastructure\Persistence\Models\NivelRiesgoModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NivelRiesgoTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_listar_niveles_riesgo(): void
    {
        NivelRiesgoModel::create(['codigo' => 'TEST1', 'nombre' => 'Test 1', 'esta_activo' => true]);

        $response = $this->getJson('/api/lf-itse/niveles-riesgo');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_puede_crear_nivel_riesgo(): void
    {
        $data = ['codigo' => 'TEST2', 'nombre' => 'Test 2', 'esta_activo' => true];

        $response = $this->postJson('/api/lf-itse/niveles-riesgo', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('niveles_riesgo', $data);
    }

    public function test_puede_actualizar_nivel_riesgo(): void
    {
        $m = NivelRiesgoModel::create(['codigo' => 'TEST1', 'nombre' => 'Test 1', 'esta_activo' => true]);
        $data = ['codigo' => 'TEST1', 'nombre' => 'Test Updated', 'esta_activo' => false];

        $response = $this->putJson('/api/lf-itse/niveles-riesgo/'.$m->id, $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('niveles_riesgo', $data);
    }

    public function test_puede_eliminar_nivel_riesgo(): void
    {
        $m = NivelRiesgoModel::create(['codigo' => 'TEST1', 'nombre' => 'Test 1', 'esta_activo' => true]);

        $response = $this->deleteJson('/api/lf-itse/niveles-riesgo/'.$m->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('niveles_riesgo', ['id' => $m->id]);
    }
}
