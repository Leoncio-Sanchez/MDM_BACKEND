<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use DateTimeImmutable;

final readonly class ListarExpedientesPendientesUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private CalculadoraDiasHabilesService $calculadora,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(): array
    {
        $filas = $this->expedientes->listarPendientes();
        $hoy = new DateTimeImmutable('today');

        foreach ($filas as &$fila) {
            $vencimiento = new DateTimeImmutable((string) $fila['fecha_vencimiento']);
            $fila['dias_habiles_restantes'] = $this->calculadora->diferenciaDiasHabiles(
                $hoy,
                $vencimiento,
            );
        }
        unset($fila);

        return $filas;
    }
}
