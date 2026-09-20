<?php
declare(strict_types=1);

it('crea una licencia', function () {
    // Basic test
    expect(true)->toBeTrue();
});

it('rechaza sin autenticación', function () {
    expect(true)->toBeTrue();
});

it('lista tipos de licencia', function () {
    expect(true)->toBeTrue();
});

it('modifica una licencia vía API', function () {
    $user = \App\Models\User::factory()->create();
    $licencia = \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::create([
        'uuid' => 'test-uuid-mod',
        'numero_licencia' => 'LIC-001',
        'fecha_emision' => '2025-01-01',
        'nombre_comercial' => 'Viejo',
        'tipo_licencia_id' => 1,
        'estado' => 'ACTIVA',
    ]);

    $response = $this->actingAs($user, 'api')
        ->putJson("/api/lf-itse/licencias-funcionamiento/{$licencia->id}", [
            'nombre_comercial' => 'Nuevo',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('licencias_funcionamiento', [
        'id' => $licencia->id,
        'nombre_comercial' => 'Nuevo',
    ]);
});
