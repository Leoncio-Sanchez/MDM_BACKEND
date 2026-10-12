<?php
declare(strict_types=1);

use App\Modules\ITSE\Domain\Entities\ItseEstado;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentItseEstadoRepository;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->repo = new EloquentItseEstadoRepository();
});

it('crea un ItseEstado', function () {
    $estado = ItseEstado::crear(
        CarbonImmutable::now(),
        'RES 001',
        'OBS',
        CarbonImmutable::now(),
        1,
        1,
        1
    );

    $creado = $this->repo->crear($estado);

    expect($creado->id)->toBeNumeric();
    $this->assertDatabaseHas('itse_estados', [
        'itse_id' => 1,
        'estado_id' => 1,
        'documento' => 'RES 001',
        'observaciones' => 'OBS',
    ]);
});

it('detecta si el ITSE tiene un estado inactivo', function () {
    $activo = \Illuminate\Support\Facades\DB::table('estados')->insertGetId(['nombre' => 'VIGENTE', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => true]);
    $inactivo = \Illuminate\Support\Facades\DB::table('estados')->insertGetId(['nombre' => 'CLAUSURA', 'es_para_lf' => false, 'es_para_itse' => true, 'esta_activo' => false]);

    $this->repo->crear(ItseEstado::crear(CarbonImmutable::now(), 'D', 'O', CarbonImmutable::now(), $activo, 1, 1));
    expect($this->repo->tieneEstadoInactivo(1))->toBeFalse();

    $this->repo->crear(ItseEstado::crear(CarbonImmutable::now(), 'D', 'O', CarbonImmutable::now(), $inactivo, 1, 1));
    expect($this->repo->tieneEstadoInactivo(1))->toBeTrue();
    expect($this->repo->tieneEstadoInactivo(2))->toBeFalse();
});
