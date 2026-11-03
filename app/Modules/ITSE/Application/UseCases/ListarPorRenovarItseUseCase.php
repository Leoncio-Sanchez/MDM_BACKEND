<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\PorRenovarItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Shared\Domain\Calendario;
use Carbon\CarbonImmutable;

final readonly class ListarPorRenovarItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
    ) {}

    /**
     * ITSE que vencen en el rango (por defecto, los próximos 30 días) y aún no se renovaron.
     * Sirve para avisar a los negocios antes de que caduque su certificado.
     *
     * @return array<int, Itse>
     */
    public function execute(PorRenovarItseDTO $dto): array
    {
        $desde = $dto->fechaDesde !== null ? CarbonImmutable::parse($dto->fechaDesde) : Calendario::hoy();
        $hasta = $dto->fechaHasta !== null ? CarbonImmutable::parse($dto->fechaHasta) : $desde->addDays($dto->dias);

        return $this->itses->listarPorRenovar(
            fechaDesde: $desde->toDateString(),
            fechaHasta: $hasta->toDateString(),
        );
    }
}
