<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Infrastructure\Providers;

use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Repositories\EloquentUnidadOrganicaRepository;
use Illuminate\Support\ServiceProvider;

final class UnidadesOrganicasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UnidadOrganicaRepositoryInterface::class,
            EloquentUnidadOrganicaRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
