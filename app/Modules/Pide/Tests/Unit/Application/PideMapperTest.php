<?php

declare(strict_types=1);

namespace App\Modules\Pide\Tests\Unit\Application;

use App\Modules\Pide\Application\Mappers\PideMapper;
use App\Modules\Pide\Domain\Exceptions\DocumentoNoEncontrado;
use App\Modules\Pide\Domain\Exceptions\ReniecError;

$reniec = fn (array $retorno) => ['consultarResponse' => ['return' => $retorno]];

it('separa departamento, provincia y distrito del ubigeo de RENIEC', function () use ($reniec) {
    $persona = PideMapper::personaDesdeReniec('12345678', $reniec([
        'coResultado' => '0000',
        'datosPersona' => ['prenombres' => 'ANA', 'apPrimer' => 'RIOS', 'ubigeo' => 'SAN MARTIN / SAN MARTIN / MORALES'],
    ]));

    expect([$persona->departamento, $persona->provincia, $persona->distrito])->toBe(['SAN MARTIN', 'SAN MARTIN', 'MORALES'])
        ->and($persona->apellidoMaterno)->toBe(''); // campo ausente: vacío, sin fallar
});

it('un DNI inexistente es "no encontrado" y un rechazo del servicio es un error de RENIEC', function () use ($reniec) {
    expect(fn () => PideMapper::personaDesdeReniec('12345678', $reniec(['coResultado' => '0001'])))
        ->toThrow(DocumentoNoEncontrado::class);

    // Credenciales vencidas, servicio no habilitado, etc.: no confundirlo con un DNI inexistente
    expect(fn () => PideMapper::personaDesdeReniec('12345678', $reniec(['coResultado' => '1999', 'deResultado' => 'Usuario no autorizado'])))
        ->toThrow(ReniecError::class, 'RENIEC rechazó la consulta (código 1999). Usuario no autorizado');

    expect(fn () => PideMapper::personaDesdeReniec('12345678', []))->toThrow(DocumentoNoEncontrado::class);
});

it('arma la dirección de SUNAT omitiendo las partes sin dato', function () {
    $empresa = PideMapper::contribuyenteDesdeSunat('20123456789', ['getDatosPrincipalesResponse' => ['return' => [
        'ddp_nombre' => 'EMPRESA S.A.C.',
        'ddp_estado' => 'ACTIVO',          // si no viene desc_estado se usa ddp_estado
        'desc_tipvia' => 'AV.', 'ddp_nomvia' => 'PERU', 'ddp_numer1' => '-', 'ddp_inter1' => 'B',
        'desc_tipzon' => 'URB.', 'ddp_nomzon' => 'EL SOL',
    ]]]);

    expect($empresa->direccion)->toBe('AV. PERU INT. B URB. EL SOL')
        ->and($empresa->estado)->toBe('ACTIVO')
        ->and($empresa->condicion)->toBe('');
});

it('un RUC sin razón social es "no encontrado"', function () {
    expect(fn () => PideMapper::contribuyenteDesdeSunat('20123456789', ['getDatosPrincipalesResponse' => ['return' => ['ddp_nombre' => '']]]))
        ->toThrow(DocumentoNoEncontrado::class);
});
