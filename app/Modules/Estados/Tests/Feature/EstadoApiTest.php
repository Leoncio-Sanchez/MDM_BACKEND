<?php
declare(strict_types=1);

use App\Models\User;
use App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModel;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\assertDatabaseMissing;

uses(Tests\TestCase::class); // arranca Laravel: el test usa facades / helpers HTTP

it('rechaza listar sin autenticacion', function () {
    getJson('/api/lf-itse/estados/')->assertUnauthorized();
});

it('lista estados paginados', function () {
    EstadoModel::factory()->count(15)->create();
    $user = User::factory()->administrador()->create();

    $response = actingAs($user, 'api')->getJson('/api/lf-itse/estados/?page=1');

    $response->assertOk()
        ->assertJsonStructure(['count', 'next', 'previous', 'results'])
        ->assertJsonPath('count', 15);
});

it('crea un estado', function () {
    $user = User::factory()->administrador()->create();
    $response = actingAs($user, 'api')->postJson('/api/lf-itse/estados/', [
        'nombre' => 'Nuevo Test',
        'es_para_lf' => true,
        'es_para_itse' => false,
    ]);
    
    $response->assertCreated()
        ->assertJsonPath('nombre', 'Nuevo Test')
        ->assertJsonPath('esta_activo', false);
});

it('obtiene un estado por id', function () {
    $estado = EstadoModel::factory()->create();
    $user = User::factory()->administrador()->create();
    
    $response = actingAs($user, 'api')->getJson("/api/lf-itse/estados/{$estado->id}");
    $response->assertOk()
        ->assertJsonPath('nombre', $estado->nombre);
});

it('actualiza un estado', function () {
    /** @var EstadoModel $estado */
    $estado = EstadoModel::factory()->create(['nombre' => 'Viejo', 'esta_activo' => false]);
    $user = User::factory()->administrador()->create();
    
    $response = actingAs($user, 'api')->putJson("/api/lf-itse/estados/{$estado->id}", [
        'nombre' => 'Actualizado',
        'esta_activo' => true
    ]);
    
    $response->assertOk()
        ->assertJsonPath('nombre', 'Actualizado')
        ->assertJsonPath('esta_activo', true);
});

it('elimina un estado', function () {
    $estado = EstadoModel::factory()->create();
    $user = User::factory()->administrador()->create();
    
    actingAs($user, 'api')->deleteJson("/api/lf-itse/estados/{$estado->id}")
        ->assertNoContent();
        
    assertDatabaseMissing('estados', ['id' => $estado->id]);
});
