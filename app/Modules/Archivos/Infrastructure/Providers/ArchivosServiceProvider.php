<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Providers;

use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ExpedienteArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\ItseArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\LicenciaFuncionamientoArchivoRepositoryInterface;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentExpedienteArchivoRepository;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentItseArchivoRepository;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentLicenciaFuncionamientoArchivoRepository;
use App\Modules\Archivos\Infrastructure\Storage\LocalStorageArchivoManager;
use Illuminate\Support\ServiceProvider;

final class ArchivosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArchivoManagerInterface::class, function () {
            return new LocalStorageArchivoManager('media');
        });

        $this->app->bind(
            ExpedienteArchivoRepositoryInterface::class,
            EloquentExpedienteArchivoRepository::class,
        );

        $this->app->bind(
            ItseArchivoRepositoryInterface::class,
            EloquentItseArchivoRepository::class,
        );

        $this->app->bind(
            LicenciaFuncionamientoArchivoRepositoryInterface::class,
            EloquentLicenciaFuncionamientoArchivoRepository::class,
        );
    }
}
