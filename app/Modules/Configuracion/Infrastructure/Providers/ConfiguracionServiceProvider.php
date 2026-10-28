<?php

declare(strict_types=1);

namespace App\Modules\Configuracion\Infrastructure\Providers;

use App\Modules\Configuracion\Domain\Contracts\ConfiguracionRepositoryInterface;
use App\Modules\Configuracion\Infrastructure\Persistence\Repositories\QueryConfiguracionRepository;
use Illuminate\Support\ServiceProvider;

final class ConfiguracionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ConfiguracionRepositoryInterface::class, QueryConfiguracionRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
