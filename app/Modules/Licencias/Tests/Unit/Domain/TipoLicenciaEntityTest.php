<?php
declare(strict_types=1);

it('crea un TipoLicencia', function () {
    $tipo = new \App\Modules\Licencias\Domain\Entities\TipoLicencia(
        id: 1, codigo: 'EST', nombre: 'ESTANDAR', estaActivo: true,
    );
    expect($tipo->id)->toBe(1)
        ->and($tipo->codigo)->toBe('EST')
        ->and($tipo->nombre)->toBe('ESTANDAR')
        ->and($tipo->estaActivo)->toBeTrue();
});

it('crea un TipoLicencia inactivo', function () {
    $tipo = new \App\Modules\Licencias\Domain\Entities\TipoLicencia(
        id: 2, codigo: 'IN', nombre: 'INACTIVO', estaActivo: false,
    );
    expect($tipo->estaActivo)->toBeFalse();
});
