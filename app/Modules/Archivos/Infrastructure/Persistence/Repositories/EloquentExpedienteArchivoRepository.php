<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Persistence\Repositories;

use App\Modules\Archivos\Domain\Contracts\ExpedienteArchivoRepositoryInterface;
use App\Modules\Archivos\Infrastructure\Persistence\Models\ExpedienteArchivoModel;

final class EloquentExpedienteArchivoRepository extends AbstractEloquentArchivoRepository implements ExpedienteArchivoRepositoryInterface
{
    protected function getModelClass(): string
    {
        return ExpedienteArchivoModel::class;
    }

    protected function getParentForeignKey(): string
    {
        return 'expediente_id';
    }

    protected function getParentTable(): string
    {
        return 'expedientes';
    }
}
