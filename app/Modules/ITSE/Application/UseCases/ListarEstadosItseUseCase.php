<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;

final readonly class ListarEstadosItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
        private ItseEstadoRepositoryInterface $estados,
    ) {}

    /**
     * Lista el historial de estados de un ITSE usando el SQL de Django.
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(int $itseId): array
    {
        if (!$this->itses->existePorId($itseId)) {
            throw ItseNoEncontrado::porId($itseId);
        }

        return $this->estados->listarPorItse($itseId);
    }
}
