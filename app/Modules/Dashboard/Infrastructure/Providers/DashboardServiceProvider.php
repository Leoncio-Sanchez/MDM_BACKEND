<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Infrastructure\Providers;

use App\Modules\Dashboard\Domain\Contracts\ResumenRepositoryInterface;
use App\Modules\Dashboard\Infrastructure\Persistence\Repositories\QueryResumenRepository;
use Illuminate\Support\ServiceProvider;

final class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResumenRepositoryInterface::class, QueryResumenRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
