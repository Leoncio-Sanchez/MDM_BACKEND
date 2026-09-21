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
        '',
        'OBS',
        CarbonImmutable::now(),
        1,
        1,
        1
    );

    $creado = $this->repo->crear($estado);

    expect($creado->id)->toBeNumeric();
    $this->assertDatabaseHas('itse_estados', ['itse_id' => 1, 'estado' => '1', 'observaciones' => 'OBS']);
});

it('verifica si tiene estado', function () {
    $this->repo->insertar(1, 'INACTIVO', now()->toDateString(), 'O', 1);

    expect($this->repo->tieneEstado(1, 'INACTIVO'))->toBeTrue();
    expect($this->repo->tieneEstado(1, 'VIGENTE'))->toBeFalse();
});
