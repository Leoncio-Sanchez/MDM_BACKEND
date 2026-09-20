<?php
declare(strict_types=1);

use App\Modules\Licencias\Domain\Exceptions\NombreComercialInvalido;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;

it('acepta un nombre válido', function () {
    $vo = NombreComercial::fromString('Mi Negocio');
    expect($vo->value)->toBe('Mi Negocio');
});

it('lanza excepción con cadena vacía', function () {
    expect(fn () => NombreComercial::fromString(''))->toThrow(NombreComercialInvalido::class);
});

it('lanza excepción con solo espacios', function () {
    expect(fn () => NombreComercial::fromString('   '))->toThrow(NombreComercialInvalido::class);
});

it('lanza excepción si excede 250 caracteres', function () {
    expect(fn () => NombreComercial::fromString(str_repeat('A', 251)))->toThrow(NombreComercialInvalido::class);
});
