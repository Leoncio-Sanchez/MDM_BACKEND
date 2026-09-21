<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\ConsultaItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;

final readonly class ConsultaItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
    ) {}

    /**
     * Consulta rápida de ITSE con filtro opcional por numero_itse.
     * Replica ItseConsultaView de Django (ItseSerializer completo).
     *
     * @return array<int, Itse>
     */
    public function execute(ConsultaItseDTO $dto): array
    {
        return $this->itses->consultaRapida(
            numeroItse: $dto->numeroItse,
        );
    }
}
