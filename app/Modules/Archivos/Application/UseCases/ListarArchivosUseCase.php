<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Application\UseCases;

use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;

final readonly class ListarArchivosUseCase
{
    /**
     * @return ArchivoMetadata[]
     */
    public function execute(
        ArchivoRepositoryInterface $repository,
        int $parentId,
    ): array {
        return $repository->listarPorParent($parentId);
    }
}
