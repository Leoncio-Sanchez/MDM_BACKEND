<?php

declare(strict_types=1);

use App\Modules\Expedientes\Application\DTOs\ActualizarExpedienteDTO;
use App\Modules\Expedientes\Application\DTOs\BuscarExpedientesDTO;
use App\Modules\Expedientes\Application\DTOs\ConsultaExpedientesDTO;
use App\Modules\Expedientes\Application\DTOs\CrearExpedienteDTO;

it('instancia CrearExpedienteDTO desde array', function () {
    $data = [
        'numero_expediente' => 123,
        'fecha_recepcion' => '2025-05-10 08:30:00',
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 10,
        'representante_id' => 20,
        'observaciones' => 'Nota test',
    ];

    $dto = CrearExpedienteDTO::fromArray($data, 5);

    expect($dto->numeroExpediente)->toBe(123)
        ->and($dto->fechaRecepcion->format('Y-m-d'))->toBe('2025-05-10')
        ->and($dto->tipoProcedimientoTupaId)->toBe(1)
        ->and($dto->solicitanteId)->toBe(10)
        ->and($dto->representanteId)->toBe(20)
        ->and($dto->observaciones)->toBe('Nota test')
        ->and($dto->usuarioId)->toBe(5);

    $dtoDefault = CrearExpedienteDTO::fromArray([
        'tipo_procedimiento_tupa_id' => 2,
        'solicitante_id' => 3,
        'fecha_recepcion' => new DateTimeImmutable('2025-01-01'),
    ], 1);
    expect($dtoDefault->numeroExpediente)->toBeNull()
        ->and($dtoDefault->representanteId)->toBeNull()
        ->and($dtoDefault->observaciones)->toBeNull();
});

it('instancia ActualizarExpedienteDTO desde array', function () {
    $data = [
        'numero_expediente' => 456,
        'fecha_recepcion' => '2025-06-15 10:00:00',
        'tipo_procedimiento_tupa_id' => 3,
        'solicitante_id' => 15,
        'representante_id' => null,
        'observaciones' => null,
    ];

    $dto = ActualizarExpedienteDTO::fromArray(99, $data);

    expect($dto->expedienteId)->toBe(99)
        ->and($dto->numeroExpediente)->toBe(456)
        ->and($dto->fechaRecepcion->format('Y-m-d'))->toBe('2025-06-15')
        ->and($dto->tipoProcedimientoTupaId)->toBe(3)
        ->and($dto->solicitanteId)->toBe(15)
        ->and($dto->representanteId)->toBeNull()
        ->and($dto->observaciones)->toBeNull();

    $dtoObj = ActualizarExpedienteDTO::fromArray(1, [
        'numero_expediente' => 77,
        'fecha_recepcion' => new DateTimeImmutable('2025-02-02'),
        'tipo_procedimiento_tupa_id' => 1,
        'solicitante_id' => 2,
    ]);
    expect($dtoObj->numeroExpediente)->toBe(77);
});

it('instancia BuscarExpedientesDTO desde array', function () {
    $dto = BuscarExpedientesDTO::fromArray([
        'filtro' => 'NUMERO',
        'valor' => '1234',
    ]);

    expect($dto->filtro)->toBe('NUMERO')
        ->and($dto->valor)->toBe('1234');
});

it('instancia ConsultaExpedientesDTO desde array con y sin numero_expediente', function () {
    $dtoConNumero = ConsultaExpedientesDTO::fromArray([
        'numero_expediente' => '500',
    ]);
    expect($dtoConNumero->numeroExpediente)->toBe(500);

    $dtoVacio = ConsultaExpedientesDTO::fromArray([]);
    expect($dtoVacio->numeroExpediente)->toBeNull();

    $dtoNull = ConsultaExpedientesDTO::fromArray(['numero_expediente' => null]);
    expect($dtoNull->numeroExpediente)->toBeNull();
});
