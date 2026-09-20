<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Infrastructure\Providers;

use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Infrastructure\Persistence\Repositories\EloquentTipoProcedimientoTupaRepository;
use Illuminate\Support\ServiceProvider;

class TipoProcedimientoTupaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TipoProcedimientoTupaRepositoryInterface::class, EloquentTipoProcedimientoTupaRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
