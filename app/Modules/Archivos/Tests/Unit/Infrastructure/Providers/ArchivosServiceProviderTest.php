<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Tests\Unit\Infrastructure\Providers;

use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Archivos\Domain\Contracts\ExpedienteArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\ItseArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Contracts\LicenciaFuncionamientoArchivoRepositoryInterface;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentExpedienteArchivoRepository;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentItseArchivoRepository;
use App\Modules\Archivos\Infrastructure\Persistence\Repositories\EloquentLicenciaFuncionamientoArchivoRepository;
use App\Modules\Archivos\Infrastructure\Storage\LocalStorageArchivoManager;
use Tests\TestCase;

uses(TestCase::class);

it('resuelve archivo manager y los tres repositorios desde el contenedor', function () {
    $manager = app(ArchivoManagerInterface::class);
    $expRepo = app(ExpedienteArchivoRepositoryInterface::class);
    $itseRepo = app(ItseArchivoRepositoryInterface::class);
    $licRepo = app(LicenciaFuncionamientoArchivoRepositoryInterface::class);

    expect($manager)->toBeInstanceOf(LocalStorageArchivoManager::class)
        ->and($expRepo)->toBeInstanceOf(EloquentExpedienteArchivoRepository::class)
        ->and($itseRepo)->toBeInstanceOf(EloquentItseArchivoRepository::class)
        ->and($licRepo)->toBeInstanceOf(EloquentLicenciaFuncionamientoArchivoRepository::class);
});
