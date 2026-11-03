<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Application\DTOs\BuscarExpedientesDTO;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use App\Shared\Domain\Calendario;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class BuscarExpedientesUseCase
{
    private const FILTROS_VALIDOS = [
        'ID',
        'NUMERO',
        'FECHA_RECEPCION',
        'FECHA_VENCIMIENTO',
        'NOMBRE_SOLICITANTE',
        'RUC_SOLICITANTE',
    ];

    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
        private CalculadoraDiasHabilesService $calculadora,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(BuscarExpedientesDTO $dto): array
    {
        $filtro = strtoupper(trim($dto->filtro));

        if (! in_array($filtro, self::FILTROS_VALIDOS, true)) {
            throw new InvalidArgumentException(
                "Filtro '{$dto->filtro}' no válido. Opciones: ".implode(', ', self::FILTROS_VALIDOS)
            );
        }

        $filas = $this->expedientes->buscarPorFiltro($filtro, $dto->valor);
        $hoy = Calendario::hoy();

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
