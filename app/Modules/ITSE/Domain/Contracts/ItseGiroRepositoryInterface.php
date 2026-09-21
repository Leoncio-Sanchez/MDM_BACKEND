<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

interface ItseGiroRepositoryInterface
{
    /**
     * Lista los giros de un ITSE, replicando _SQL_GIROS_POR_ITSE de Django.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorItse(int $itseId): array;
}
