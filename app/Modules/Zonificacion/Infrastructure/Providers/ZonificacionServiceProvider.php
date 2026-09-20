<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Infrastructure\Providers;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Infrastructure\Persistence\Repositories\EloquentZonificacionRepository;
use Illuminate\Support\ServiceProvider;

class ZonificacionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ZonificacionRepositoryInterface::class, EloquentZonificacionRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
