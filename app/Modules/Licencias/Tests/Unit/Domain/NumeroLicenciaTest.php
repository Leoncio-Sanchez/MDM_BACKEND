<?php
declare(strict_types=1);

use App\Modules\Licencias\Domain\Exceptions\NumeroLicenciaInvalido;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;

it('acepta un número válido', function () {
    $vo = NumeroLicencia::fromInt(12345);
    expect($vo->value)->toBe(12345);
});

it('lanza excepción con 0', function () {
    expect(fn () => NumeroLicencia::fromInt(0))
        ->toThrow(NumeroLicenciaInvalido::class);
});

it('lanza excepción con número negativo', function () {
    expect(fn () => NumeroLicencia::fromInt(-5))
        ->toThrow(NumeroLicenciaInvalido::class);
});
