<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Persistence\Repositories;

use App\Modules\Archivos\Domain\Contracts\ItseArchivoRepositoryInterface;
use App\Modules\Archivos\Infrastructure\Persistence\Models\ItseArchivoModel;

final class EloquentItseArchivoRepository extends AbstractEloquentArchivoRepository implements ItseArchivoRepositoryInterface
{
    protected function getModelClass(): string
    {
        return ItseArchivoModel::class;
    }

    protected function getParentForeignKey(): string
    {
        return 'itse_id';
    }

    protected function getParentTable(): string
    {
        return 'itse';
    }
}
