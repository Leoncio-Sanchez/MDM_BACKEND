<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

/** Expediente declarado improcedente (licencia o ITSE denegada) — reporte para el OCI. */
final readonly class Improcedencia
{
    public function __construct(
        public string $fechaRechazo,
        public string $documento,
        public string $motivo,
        public int $numeroExpediente,
        public string $procedimiento,
        public string $solicitante,
        public string $documentoSolicitante,
        /** Recepción del expediente, en fecha de Perú (Y-m-d) */
        public string $fechaRecepcion,
        public string $registradoPor,
        /** Cuándo se digitó, en hora de Perú (Y-m-d H:i) */
        public string $fechaRegistro,
    ) {}
}
