<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Providers;

use App\Modules\Expedientes\Domain\Contracts\AutorizacionImprocedenteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\FeriadoReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\TipoProcedimientoTupaReadRepositoryInterface;
use App\Modules\Expedientes\Domain\Contracts\UsuarioReadRepositoryInterface;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentAutorizacionImprocedenteRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentExpedienteRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentFeriadoReadRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentPersonaReadRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentTipoProcedimientoTupaReadRepository;
use App\Modules\Expedientes\Infrastructure\Persistence\Repositories\EloquentUsuarioReadRepository;
use Illuminate\Support\ServiceProvider;

final class ExpedienteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExpedienteRepositoryInterface::class, EloquentExpedienteRepository::class);
        $this->app->bind(AutorizacionImprocedenteRepositoryInterface::class, EloquentAutorizacionImprocedenteRepository::class);
        $this->app->bind(FeriadoReadRepositoryInterface::class, EloquentFeriadoReadRepository::class);
        $this->app->bind(TipoProcedimientoTupaReadRepositoryInterface::class, EloquentTipoProcedimientoTupaReadRepository::class);
        $this->app->bind(PersonaReadRepositoryInterface::class, EloquentPersonaReadRepository::class);
        $this->app->bind(UsuarioReadRepositoryInterface::class, EloquentUsuarioReadRepository::class);
    }

    public function boot(): void
    {
        $routesPath = __DIR__ . '/../../Presentation/Routes/api.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }
}
