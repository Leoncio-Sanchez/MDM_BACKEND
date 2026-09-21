<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\PorRenovarItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;

final readonly class ListarPorRenovarItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
    ) {}

    /**
     * Lista los ITSE filtrados por fecha_solicitud_renovacion.
     * Replica ItseSerializer (misma forma que el detail).
     *
     * @return array<int, Itse>
     */
    public function execute(PorRenovarItseDTO $dto): array
    {
        return $this->itses->listarPorRenovar(
            fechaDesde: $dto->fechaDesde,
            fechaHasta: $dto->fechaHasta,
        );
    }
}
