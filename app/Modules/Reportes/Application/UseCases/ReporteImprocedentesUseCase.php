<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Application\UseCases;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\Improcedencia;
use App\Modules\Reportes\Domain\ValueObjects\Periodo;
use App\Modules\Reportes\Domain\ValueObjects\TipoAutorizacion;

/** Reporte para el OCI: expedientes de licencia o ITSE declarados improcedentes en el periodo. */
final readonly class ReporteImprocedentesUseCase
{
    public function __construct(private ReportesRepositoryInterface $reportes) {}

    /**
     * @return array{desde: string, hasta: string, registros: list<Improcedencia>}
     */
    public function execute(TipoAutorizacion $tipo, ?string $desde, ?string $hasta): array
    {
        $periodo = Periodo::oMesActual($desde, $hasta);

        return [
            'desde' => $periodo->desde,
            'hasta' => $periodo->hasta,
            'registros' => $this->reportes->improcedencias($tipo, $periodo->desde, $periodo->hasta),
        ];
    }
}
