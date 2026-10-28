<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Application\UseCases;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use App\Modules\Reportes\Domain\ValueObjects\ResumenPadron;
use Carbon\CarbonImmutable;

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
        $hoy = CarbonImmutable::today();
        $desde ??= $hoy->startOfMonth()->toDateString();
        $hasta ??= $hoy->endOfMonth()->toDateString();

        $licencias = $this->reportes->padronLicencias($desde, $hasta);

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'resumen' => ResumenPadron::de($licencias, $this->reportes->nivelesRiesgo()),
            'licencias' => $licencias,
        ];
    }
}
