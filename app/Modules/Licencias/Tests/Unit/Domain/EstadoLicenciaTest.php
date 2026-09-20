<?php
declare(strict_types=1);

use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;

it('crea estado registrada', function () {
    $estado = EstadoLicencia::registrada();
    expect($estado->value)->toBe('REGISTRADA')
        ->and($estado->equals(EstadoLicencia::registrada()))->toBeTrue();
});

it('crea estado inactiva', function () {
    $estado = EstadoLicencia::inactiva();
    expect($estado->value)->toBe('INACTIVA');
});

it('compara dos estados iguales', function () {
    expect(
        EstadoLicencia::registrada()->equals(EstadoLicencia::registrada())
    )->toBeTrue();
});

it('compara dos estados diferentes', function () {
    expect(
        EstadoLicencia::registrada()->equals(EstadoLicencia::inactiva())
    )->toBeFalse();
});

it('crea estado desde string válido', function () {
    $estado = EstadoLicencia::fromString('ACTIVA');
    expect($estado->value)->toBe('ACTIVA');
});
