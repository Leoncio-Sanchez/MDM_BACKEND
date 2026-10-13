<?php
declare(strict_types=1);

use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\Exceptions\NumeroLicenciaInvalido;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use Carbon\CarbonImmutable;

it('crea una licencia con datos válidos', function () {
    $licencia = Licencia::crear(
        uuid: 'uuid-123',
        numeroLicencia: 12345,
        nombreComercial: 'Restaurante Central',
        tipoLicenciaId: 1,
        titularId: 10,
        conductorId: 20,
        expedienteId: 30,
        nivelRiesgoId: 2,
        zonificacionId: 3
    );

    expect($licencia->numeroLicencia->value)->toBe(12345)
        ->and($licencia->nombreComercial->value)->toBe('Restaurante Central')
        ->and($licencia->estado->equals(EstadoLicencia::registrada()))->toBeTrue();
});

it('lanza excepción si número de licencia <= 0', function () {
    expect(fn () => Licencia::crear(
        uuid: 'uuid-123',
        numeroLicencia: 0,
        nombreComercial: 'Test',
        tipoLicenciaId: 1,
        titularId: 10,
        conductorId: 20,
        expedienteId: 30,
        nivelRiesgoId: 2,
        zonificacionId: 3
    ))->toThrow(NumeroLicenciaInvalido::class);
});

it('inactiva una licencia sin mutar la original', function () {
    $licencia = Licencia::crear(
        uuid: 'uuid-123',
        numeroLicencia: 12345,
        nombreComercial: 'Restaurante Central',
        tipoLicenciaId: 1,
        titularId: 10,
        conductorId: 20,
        expedienteId: 30,
        nivelRiesgoId: 2,
        zonificacionId: 3
    );

    $inactiva = $licencia->inactivar();

    expect($licencia->estado->equals(EstadoLicencia::registrada()))->toBeTrue()
        ->and($inactiva->estado->equals(EstadoLicencia::inactiva()))->toBeTrue();
});

it('actualiza una licencia sin mutar la original', function () {
    $licencia = Licencia::crear(
        uuid: 'uuid-123',
        numeroLicencia: 12345,
        nombreComercial: 'Restaurante Central',
        tipoLicenciaId: 1,
        titularId: 10,
        conductorId: 20,
        expedienteId: 30,
        nivelRiesgoId: 2,
        zonificacionId: 3
    );

    $modificada = $licencia->actualizar(
        numeroLicencia: 54321,
        nombreComercial: 'Nuevo Nombre'
    );

    expect($licencia->numeroLicencia->value)->toBe(12345)
        ->and($modificada->numeroLicencia->value)->toBe(54321)
        ->and($modificada->nombreComercial->value)->toBe('Nuevo Nombre');
});

it('usa la fecha de Lima como fecha de emisión aunque en UTC ya sea el día siguiente', function () {
    // 02:00 UTC del 13 = 21:00 del 12 en Lima
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-13 02:00:00', 'UTC'));

    $licencia = Licencia::crear(
        uuid: 'uuid',
        numeroLicencia: 1,
        nombreComercial: 'X',
        tipoLicenciaId: 1,
        titularId: 1,
        conductorId: 1,
        expedienteId: 1,
        nivelRiesgoId: 1,
        zonificacionId: 1,
    );

    expect($licencia->fechaEmision->format('Y-m-d'))->toBe('2026-10-12');

    CarbonImmutable::setTestNow();
});
