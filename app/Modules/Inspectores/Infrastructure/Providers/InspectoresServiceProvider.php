<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Infrastructure\Providers;

use App\Modules\Inspectores\Domain\Contracts\InspectorRepositoryInterface;
use App\Modules\Inspectores\Infrastructure\Persistence\Repositories\EloquentInspectorRepository;
use Illuminate\Support\ServiceProvider;

final class InspectoresServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InspectorRepositoryInterface::class, EloquentInspectorRepository::class);
    }

    public function boot(): void
    {
        $routesPath = __DIR__.'/../../Presentation/Routes/api.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }
}
