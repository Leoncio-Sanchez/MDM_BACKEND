<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Persistence\Repositories;

use App\Modules\Archivos\Domain\Contracts\LicenciaFuncionamientoArchivoRepositoryInterface;
use App\Modules\Archivos\Infrastructure\Persistence\Models\LicenciaFuncionamientoArchivoModel;

final class EloquentLicenciaFuncionamientoArchivoRepository extends AbstractEloquentArchivoRepository implements LicenciaFuncionamientoArchivoRepositoryInterface
{
    protected function getModelClass(): string
    {
        return LicenciaFuncionamientoArchivoModel::class;
    }

    protected function getParentForeignKey(): string
    {
        return 'licencia_funcionamiento_id';
    }
}
