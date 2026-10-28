<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Domain\Contracts;

use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;

interface ReportesRepositoryInterface
{
    /**
     * Licencias emitidas entre las fechas (Y-m-d, inclusive), por número de licencia.
     *
     * @return list<LicenciaPadron>
     */
    public function padronLicencias(string $desde, string $hasta): array;

    /** @return list<string> nombres de los niveles de riesgo, en orden */
    public function nivelesRiesgo(): array;
}
