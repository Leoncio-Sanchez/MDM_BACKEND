<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Infrastructure\Providers;

use App\Modules\Inspecciones\Domain\Contracts\ActaInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Infrastructure\Persistence\Repositories\QueryActaInspeccionRepository;
use App\Modules\Inspecciones\Infrastructure\Persistence\Repositories\QueryAsignacionInspeccionRepository;
use Illuminate\Support\ServiceProvider;

final class InspeccionesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AsignacionInspeccionRepositoryInterface::class, QueryAsignacionInspeccionRepository::class);
        $this->app->bind(ActaInspeccionRepositoryInterface::class, QueryActaInspeccionRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
