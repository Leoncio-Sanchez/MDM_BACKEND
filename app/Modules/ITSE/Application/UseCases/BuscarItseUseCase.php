<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\BuscarItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;

final readonly class BuscarItseUseCase
{
    public function __construct(private ItseRepositoryInterface $itses) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(BuscarItseDTO $dto): array
    {
        return $this->itses->buscarPorFiltro($dto->filtro, $dto->valor);
    }
}
