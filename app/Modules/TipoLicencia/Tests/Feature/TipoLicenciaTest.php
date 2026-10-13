<?php
declare(strict_types=1);

use App\Modules\TipoLicencia\Infrastructure\Persistence\Models\TipoLicenciaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;


it('can list tipos licencia', function () {
    $tipo = TipoLicenciaModel::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);
    $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-licencia');
    $response->assertStatus(200)->assertJsonCount(1);
});

it('can get a tipo licencia', function () {
    $tipo = TipoLicenciaModel::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);
    $response = $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-licencia/' . $tipo->id);
    $response->assertStatus(200)->assertJsonPath('codigo', 'EST');
});

it('can create a tipo licencia', function () {
    $response = $this->comoUsuarioApi()->postJson('/api/lf-itse/tipos-licencia', [
        'codigo' => 'NEW',
        'nombre' => 'NUEVO TIPO',
        'esta_activo' => true
    ]);
    $response->assertStatus(201);
    expect(TipoLicenciaModel::count())->toBe(1);
});

it('can update a tipo licencia', function () {
    $tipo = TipoLicenciaModel::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);
    $response = $this->comoUsuarioApi()->putJson('/api/lf-itse/tipos-licencia/' . $tipo->id, [
        'codigo' => 'UPD',
        'nombre' => 'UPDATED TIPO',
        'esta_activo' => false
    ]);
    $response->assertStatus(200);
    expect(TipoLicenciaModel::first()->codigo)->toBe('UPD');
});

it('can delete a tipo licencia', function () {
    $tipo = TipoLicenciaModel::create(['codigo' => 'EST', 'nombre' => 'ESTANDAR', 'esta_activo' => true]);
    $response = $this->comoUsuarioApi()->deleteJson('/api/lf-itse/tipos-licencia/' . $tipo->id);
    $response->assertStatus(204);
    expect(TipoLicenciaModel::count())->toBe(0);
});

it('rechaza todas las operaciones sin autenticación', function () {
    $url = '/api/lf-itse/tipos-licencia';

    $this->getJson($url)->assertUnauthorized();
    $this->getJson($url . '/1')->assertUnauthorized();
    $this->postJson($url, [])->assertUnauthorized();
    $this->putJson($url . '/1', [])->assertUnauthorized();
    $this->deleteJson($url . '/1')->assertUnauthorized();
});

it('responde 404 si el tipo de licencia no existe', function () {
    $this->comoUsuarioApi()->getJson('/api/lf-itse/tipos-licencia/999999')
        ->assertNotFound()
        ->assertJsonStructure(['error']);
});
