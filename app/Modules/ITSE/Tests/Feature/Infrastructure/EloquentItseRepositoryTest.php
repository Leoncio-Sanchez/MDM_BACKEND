<?php
declare(strict_types=1);

use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Infrastructure\Persistence\Models\ItseModel;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentItseRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentItseRepository();
});

it('crea un ITSE correctamente', function () {
    $itse = Itse::crear(
        Uuid::uuid4()->toString(),
        12345,
        CarbonImmutable::now(),
        null,
        CarbonImmutable::now()->addYear(),
        'TEST',
        'DIR',
        'RES',
        10.5,
        100,
        'REC',
        null,
        false,
        1,
        1,
        null,
        1,
        1,
        1,
        1,
        CarbonImmutable::now()
    );

    $creado = $this->repo->crear($itse);

    expect($creado->id)->toBeNumeric()
        ->and($creado->numeroItse)->toBe(12345);

    $this->assertDatabaseHas('itse', [
        'numero_itse' => 12345,
        'nombre_comercial' => 'TEST',
    ]);
});

it('actualiza un ITSE correctamente', function () {
    $model = ItseModel::create([
        'uuid' => Uuid::uuid4()->toString(),
        'numero_itse' => 999,
        'fecha_expedicion' => now()->toDateString(),
        'fecha_caducidad' => now()->addYear()->toDateString(),
        'nombre_comercial' => 'OLD',
        'direccion' => 'DIR',
        'resolucion_numero' => 'RES',
        'area' => 10,
        'capacidad_aforo' => 10,
        'numero_recibo_pago' => 'REC1',
        'se_puede_publicar' => false,
        'fecha_digitacion' => now()->toDateTimeString(),
        'tipo_itse_id' => 1,
        'expediente_id' => 1,
        'usuario_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ]);

    $itse = $this->repo->buscarPorId($model->id);
    $actualizado = $itse->actualizar(
        $itse->fechaExpedicion,
        $itse->fechaSolicitudRenovacion,
        $itse->fechaCaducidad,
        'NEW',
        $itse->direccion,
        $itse->resolucionNumero,
        $itse->area,
        $itse->capacidadAforo,
        $itse->numeroReciboPago,
        $itse->observaciones,
        $itse->sePuedePublicar,
        $itse->tipoItseId,
        $itse->nivelRiesgoId,
        $itse->conductorId,
        $itse->titularId,
    );

    $result = $this->repo->actualizar($actualizado);

    expect($result->nombreComercial)->toBe('NEW');
    $this->assertDatabaseHas('itse', ['id' => $model->id, 'nombre_comercial' => 'NEW']);
});

it('busca un ITSE por ID', function () {
    $model = ItseModel::create([
        'uuid' => Uuid::uuid4()->toString(),
        'numero_itse' => 111,
        'fecha_expedicion' => now()->toDateString(),
        'fecha_caducidad' => now()->addYear()->toDateString(),
        'nombre_comercial' => 'N',
        'direccion' => 'D',
        'resolucion_numero' => 'R',
        'area' => 10,
        'capacidad_aforo' => 10,
        'numero_recibo_pago' => 'REC2',
        'se_puede_publicar' => false,
        'fecha_digitacion' => now()->toDateTimeString(),
        'tipo_itse_id' => 1,
        'expediente_id' => 1,
        'usuario_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ]);

    $itse = $this->repo->buscarPorId($model->id);
    expect($itse)->toBeInstanceOf(Itse::class)->and($itse->numeroItse)->toBe(111);
    
    expect($this->repo->buscarPorId(9999))->toBeNull();
});

it('verifica si existe numero o recibo', function () {
    $model = ItseModel::create([
        'uuid' => Uuid::uuid4()->toString(),
        'numero_itse' => 222,
        'fecha_expedicion' => now()->toDateString(),
        'fecha_caducidad' => now()->addYear()->toDateString(),
        'nombre_comercial' => 'N',
        'direccion' => 'D',
        'resolucion_numero' => 'R',
        'area' => 10,
        'capacidad_aforo' => 10,
        'numero_recibo_pago' => 'REC3',
        'se_puede_publicar' => false,
        'fecha_digitacion' => now()->toDateTimeString(),
        'tipo_itse_id' => 1,
        'expediente_id' => 1,
        'usuario_id' => 1,
        'nivel_riesgo_id' => 1,
        'conductor_id' => 1,
        'titular_id' => 1,
    ]);

    expect($this->repo->existeNumero(222))->toBeTrue();
    expect($this->repo->existeNumero(222, $model->id))->toBeFalse();
    
    expect($this->repo->existeRecibo('REC3'))->toBeTrue();
    expect($this->repo->existeRecibo('REC3', $model->id))->toBeFalse();
});

it('busca por filtro', function () {
    DB::shouldReceive('select')->andReturn([(object)['numero_itse' => 333]]);
    
    $res1 = $this->repo->buscarPorFiltro('NUMERO', '999');
    expect($res1)->toHaveCount(1)->and($res1[0]['numero_itse'])->toBe(333);
    
    $res3 = $this->repo->buscarPorFiltro('INVALID', 'X');
    expect($res3)->toBeEmpty();
});
