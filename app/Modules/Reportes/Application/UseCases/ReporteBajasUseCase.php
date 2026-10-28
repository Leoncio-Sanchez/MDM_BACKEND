<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Application\UseCases;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\BajaRegistrada;
use App\Modules\Reportes\Domain\ValueObjects\Periodo;
use App\Modules\Reportes\Domain\ValueObjects\TipoAutorizacion;

/** Reporte para el OCI: licencias o certificados ITSE anulados/revocados en el periodo. */
final readonly class ReporteBajasUseCase
{
    public function __construct(private ReportesRepositoryInterface $reportes) {}

    /**
     * @return array{desde: string, hasta: string, por_estado: array<string, int>, registros: list<BajaRegistrada>}
     */
    public function execute(TipoAutorizacion $tipo, ?string $desde, ?string $hasta): array
    {
        $periodo = Periodo::oMesActual($desde, $hasta);
        $registros = $this->reportes->bajas($tipo, $periodo->desde, $periodo->hasta);

        // Todos los estados de baja, aunque no tengan registros (el cero también informa)
        $porEstado = array_fill_keys($this->reportes->estadosDeBaja($tipo), 0);
        foreach ($registros as $r) {
            $porEstado[$r->estado] = ($porEstado[$r->estado] ?? 0) + 1;
        }

        return [
            'desde' => $periodo->desde,
            'hasta' => $periodo->hasta,
            'por_estado' => $porEstado,
            'registros' => $registros,
        ];
    }
}
