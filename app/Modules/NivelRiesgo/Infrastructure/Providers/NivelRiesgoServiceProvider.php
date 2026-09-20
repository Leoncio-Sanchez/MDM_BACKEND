<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Infrastructure\Providers;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Infrastructure\Persistence\Repositories\EloquentNivelRiesgoRepository;
use Illuminate\Support\ServiceProvider;

class NivelRiesgoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NivelRiesgoRepositoryInterface::class, EloquentNivelRiesgoRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
