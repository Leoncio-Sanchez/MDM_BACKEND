<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Presentation\Controllers;

use App\Modules\Archivos\Application\UseCases\DescargarArchivoUseCase;
use App\Modules\Archivos\Application\UseCases\InactivarArchivoUseCase;
use App\Modules\Archivos\Application\UseCases\ListarArchivosUseCase;
use App\Modules\Archivos\Application\UseCases\SubirArchivoUseCase;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\LicenciaFuncionamientoArchivoRepositoryInterface;

final class LicenciaFuncionamientoArchivoController extends AbstractArchivoController
{
    public function __construct(
        ListarArchivosUseCase $listar,
        SubirArchivoUseCase $subir,
        InactivarArchivoUseCase $inactivar,
        DescargarArchivoUseCase $descargar,
        private readonly LicenciaFuncionamientoArchivoRepositoryInterface $repository,
    ) {
        parent::__construct($listar, $subir, $inactivar, $descargar);
    }

    protected function getRepository(): ArchivoRepositoryInterface
    {
        return $this->repository;
    }

    protected function getCarpetaBase(): string
    {
        return 'licencias_funcionamiento';
    }

    protected function getParentFieldName(): string
    {
        return 'licencia_funcionamiento';
    }
}
