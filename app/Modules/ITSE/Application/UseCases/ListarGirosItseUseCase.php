<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Domain\Contracts\ItseGiroRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;

final readonly class ListarGirosItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
        private ItseGiroRepositoryInterface $giros,
    ) {}

    /**
     * Lista los giros de un ITSE usando el mismo SQL que Django.
     *
     * @return array<int, array<string, mixed>>
     */
    public function execute(int $itseId): array
    {
        if (!$this->itses->existePorId($itseId)) {
            throw ItseNoEncontrado::porId($itseId);
        }

        return $this->giros->listarPorItse($itseId);
    }
}
