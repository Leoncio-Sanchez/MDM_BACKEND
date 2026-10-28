<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\ValueObjects;

/** Una fila del padrón de licencias emitidas (columnas del informe mensual definidas por el equipo). */
final readonly class LicenciaPadron
{
    public function __construct(
        public int $numeroLicencia,
        public int $numeroExpediente,
        public string $nombreComercial,
        public string $titular,
        public string $documentoTitular,
        public string $giros,
        public string $zonificacion,
        public string $nivelRiesgo,
        public string $tipoLicencia,
        public string $fechaRecepcion,
        public string $fechaEmision,
        // Días hábiles entre la recepción del expediente y la emisión
        public int $diasHabiles,
        // Plazo TUPA propio del procedimiento
        public int $plazoDias,
    ) {}

    public function dentroDePlazo(): bool
    {
        return $this->diasHabiles <= $this->plazoDias;
    }
}
