<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\AmpliarPlazoExpedienteDTO;
use App\Modules\Expedientes\Application\DTOs\BuscarAutorizacionImprocedenteDTO;
use App\Modules\Expedientes\Application\DTOs\DenegarItseDTO;
use App\Modules\Expedientes\Application\DTOs\DenegarLicenciaDTO;
use App\Modules\Expedientes\Domain\Exceptions\TipoAutorizacionInvalidoError;
use App\Modules\Expedientes\Domain\ValueObjects\DiasAmpliacion;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

it('instancia AmpliarPlazoExpedienteDTO correctamente fromArray con string date', function () {
    $data = [
        'fecha_suspension' => '2026-09-10',
        'dias_ampliacion' => 5,
        'motivo_ampliacion' => 'Subsanación de observaciones',
    ];

    $dto = AmpliarPlazoExpedienteDTO::fromArray(42, $data, 10);

    expect($dto->expedienteId)->toBe(42)
        ->and($dto->diasAmpliacion)->toBe(5)
        ->and($dto->motivoAmpliacion)->toBe('Subsanación de observaciones')
        ->and($dto->usuarioId)->toBe(10)
        ->and($dto->fechaSuspension->format('Y-m-d'))->toBe('2026-09-10');
});

it('instancia AmpliarPlazoExpedienteDTO fromArray con DateTimeImmutable date', function () {
    $dt = new DateTimeImmutable('2026-09-11 00:00:00');
    $data = [
        'fecha_suspension' => $dt,
        'dias_ampliacion' => 3,
        'motivo_ampliacion' => 'Inspección técnica',
    ];

    $dto = AmpliarPlazoExpedienteDTO::fromArray(1, $data, 2);

    expect($dto->fechaSuspension)->toBe($dt);
});

it('instancia BuscarAutorizacionImprocedenteDTO correctamente fromParams', function () {
    $dtoLf = BuscarAutorizacionImprocedenteDTO::fromParams(50, 'LF');
    expect($dtoLf->expedienteId)->toBe(50)
        ->and($dtoLf->tipo)->toBe(TipoAutorizacion::LF);

    $dtoItse = BuscarAutorizacionImprocedenteDTO::fromParams(51, 'itse');
    expect($dtoItse->expedienteId)->toBe(51)
        ->and($dtoItse->tipo)->toBe(TipoAutorizacion::ITSE);
});

it('lanza TipoAutorizacionInvalidoError al instanciar BuscarAutorizacionImprocedenteDTO con tipo inválido', function () {
    BuscarAutorizacionImprocedenteDTO::fromParams(50, 'INVALIDO');
})->throws(TipoAutorizacionInvalidoError::class, "Tipo de autorización 'INVALIDO' no válido. Opciones: LF, ITSE");

it('instancia DenegarLicenciaDTO correctamente fromArray', function () {
    $data = [
        'fecha_rechazo' => '2026-09-15',
        'documento' => 'RESOLUCION-001',
        'observaciones' => 'Zonificación incompatible',
    ];

    $dto = DenegarLicenciaDTO::fromArray(99, $data, 7);

    expect($dto->expedienteId)->toBe(99)
        ->and($dto->fechaRechazo->format('Y-m-d'))->toBe('2026-09-15')
        ->and($dto->documento)->toBe('RESOLUCION-001')
        ->and($dto->observaciones)->toBe('Zonificación incompatible')
        ->and($dto->usuarioId)->toBe(7);
});

it('instancia DenegarItseDTO correctamente fromArray', function () {
    $data = [
        'fecha_rechazo' => '2026-09-16',
        'documento' => 'ACTA-ITSE-999',
        'observaciones' => 'Riesgo alto sin subsanar',
    ];

    $dto = DenegarItseDTO::fromArray(100, $data, 8);

    expect($dto->expedienteId)->toBe(100)
        ->and($dto->fechaRechazo->format('Y-m-d'))->toBe('2026-09-16')
        ->and($dto->documento)->toBe('ACTA-ITSE-999')
        ->and($dto->observaciones)->toBe('Riesgo alto sin subsanar')
        ->and($dto->usuarioId)->toBe(8);
});

it('DiasAmpliacion acepta valores mayores o iguales a 1', function () {
    $dias = new DiasAmpliacion(1);
    expect($dias->valor)->toBe(1);

    $dias10 = new DiasAmpliacion(10);
    expect($dias10->valor)->toBe(10);
});

it('DiasAmpliacion rechaza valores menores a 1', function () {
    new DiasAmpliacion(0);
})->throws(InvalidArgumentException::class, 'Días de ampliación debe ser >= 1, recibido: 0');
