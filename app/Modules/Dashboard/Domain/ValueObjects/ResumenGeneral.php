<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Domain\ValueObjects;

/**
 * Cifras generales del sistema. Solo totales (ningún dato de personas ni trámites):
 * por eso las ve todo el personal, tenga o no acceso al módulo de cada cifra.
 */
final readonly class ResumenGeneral
{
    public function __construct(
        // Expedientes con alguna autorización (LF / ITSE) aún sin resolver
        public int $expedientesEnTramite,
        // …de ellos, los que ya llegaron a su fecha de alerta
        public int $expedientesPorVencer,
        // …y los que pasaron su fecha límite de atención
        public int $expedientesVencidos,
        public int $licenciasVigentes,
        public int $licenciasEmitidasMes,
        public int $licenciasTotal,
        public int $itseVigentes,
        // Vigentes que caducan pronto y aún no se renovaron (mismo criterio que "ITSE por renovar")
        public int $itsePorVencer,
        public int $itseEmitidosMes,
        public int $diasAlertaItse,
    ) {}
}
