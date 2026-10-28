<?php

declare(strict_types=1);

use App\Modules\ConsultaPublica\Domain\ValueObjects\EstadoTramite;
use Carbon\CarbonImmutable;

it('un expediente sigue en trámite mientras falte alguna autorización requerida', function () {
    expect(EstadoTramite::deExpediente(true, true, true, false, false, false))->toBe(EstadoTramite::EN_TRAMITE)
        ->and(EstadoTramite::deExpediente(true, false, false, false, false, false))->toBe(EstadoTramite::EN_TRAMITE);
});

it('un expediente queda atendido cuando se emitió lo requerido', function () {
    expect(EstadoTramite::deExpediente(true, true, true, true, false, false))->toBe(EstadoTramite::ATENDIDO)
        // Una emitida y la otra improcedente: igual se atendió
        ->and(EstadoTramite::deExpediente(true, true, true, false, false, true))->toBe(EstadoTramite::ATENDIDO);
});

it('un expediente es improcedente si todo lo requerido se rechazó', function () {
    expect(EstadoTramite::deExpediente(true, true, false, false, true, true))->toBe(EstadoTramite::IMPROCEDENTE)
        ->and(EstadoTramite::deExpediente(false, true, false, false, false, true))->toBe(EstadoTramite::IMPROCEDENTE);
});

it('una autorización es vigente, vencida o inactiva', function () {
    $hoy = CarbonImmutable::parse('2026-10-28');

    expect(EstadoTramite::deAutorizacion(false, $hoy->addDay(), $hoy))->toBe(EstadoTramite::VIGENTE)
        ->and(EstadoTramite::deAutorizacion(false, $hoy, $hoy))->toBe(EstadoTramite::VIGENTE)
        ->and(EstadoTramite::deAutorizacion(false, $hoy->subDay(), $hoy))->toBe(EstadoTramite::VENCIDO)
        ->and(EstadoTramite::deAutorizacion(false, null, $hoy))->toBe(EstadoTramite::VIGENTE)
        ->and(EstadoTramite::deAutorizacion(true, $hoy->addYear(), $hoy))->toBe(EstadoTramite::INACTIVO);
});
