<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Application\UseCases;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use App\Modules\Reportes\Domain\ValueObjects\Periodo;
use App\Modules\Reportes\Domain\ValueObjects\ResumenPadron;

final readonly class PadronLicenciasUseCase
{
    public function __construct(private ReportesRepositoryInterface $reportes) {}

    /**
     * Sin fechas: el mes en curso (el informe mensual).
     *
     * @return array{desde: string, hasta: string, resumen: ResumenPadron, licencias: list<LicenciaPadron>}
     */
    public function execute(?string $desde, ?string $hasta): array
    {
        $periodo = Periodo::oMesActual($desde, $hasta);
        $licencias = $this->reportes->padronLicencias($periodo->desde, $periodo->hasta);

        return [
            'desde' => $periodo->desde,
            'hasta' => $periodo->hasta,
            'resumen' => ResumenPadron::de($licencias, $this->reportes->nivelesRiesgo()),
            'licencias' => $licencias,
        ];
    }
}
