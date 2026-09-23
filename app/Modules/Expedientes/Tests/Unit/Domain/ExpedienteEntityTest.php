<?php

declare(strict_types=1);

use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

it('conAutorizaciones preserva el numero de expediente y asigna autorizaciones', function () {
    $now = new DateTimeImmutable('2026-03-01 10:00:00');
    $expediente = Expediente::crear(
        uuid: '11111111-1111-1111-1111-111111111111',
        numeroExpediente: 42,
        fechaRecepcion: $now,
        tipoProcedimientoTupaId: 1,
        solicitanteId: 10,
        usuarioId: 99,
        fechaVencimiento: $now->modify('+10 days'),
        fechaAlerta: $now->modify('+7 days'),
    );

    expect($expediente->numeroExpediente)->toBe(42)
        ->and($expediente->autorizacionesImprocedentes)->toBe([]);

    $autorizacion = new AutorizacionImprocedente(
        id: 1,
        expedienteId: 42,
        tipoAutorizacion: TipoAutorizacion::LF,
        fechaRechazo: $now,
        documento: 'DOC-01',
        observaciones: 'Rechazado',
        usuarioId: 99,
        fechaDigitacion: $now,
    );

    $conAutorizaciones = $expediente->conAutorizaciones([$autorizacion]);

    expect($conAutorizaciones->numeroExpediente)->toBe(42)
        ->and($conAutorizaciones->uuid)->toBe('11111111-1111-1111-1111-111111111111')
        ->and($conAutorizaciones->solicitanteId)->toBe(10)
        ->and($conAutorizaciones->usuarioId)->toBe(99)
        ->and(count($conAutorizaciones->autorizacionesImprocedentes))->toBe(1)
        ->and($conAutorizaciones->autorizacionesImprocedentes[0]->documento)->toBe('DOC-01');
});
