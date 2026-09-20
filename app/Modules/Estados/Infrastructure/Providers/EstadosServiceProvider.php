<?php

declare(strict_types=1);

namespace App\Modules\Estados\Infrastructure\Providers;

use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Infrastructure\Persistence\Repositories\EloquentEstadoRepository;
use Illuminate\Support\ServiceProvider;

final class EstadosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EstadoRepositoryInterface::class, EloquentEstadoRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
