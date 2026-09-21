<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentItseRepository;

use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentItseEstadoRepository;

use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentExpedienteReadRepository;

use App\Modules\ITSE\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentPersonaReadRepository;

use App\Modules\ITSE\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentNivelRiesgoReadRepository;

use App\Modules\ITSE\Domain\Contracts\ZonificacionReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentZonificacionReadRepository;

use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Repositories\EloquentAutorizacionImprocedenteReadRepository;


final class ItseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ItseRepositoryInterface::class, EloquentItseRepository::class);
        $this->app->bind(ItseEstadoRepositoryInterface::class, EloquentItseEstadoRepository::class);
        $this->app->bind(ExpedienteReadRepositoryInterface::class, EloquentExpedienteReadRepository::class);
        $this->app->bind(PersonaReadRepositoryInterface::class, EloquentPersonaReadRepository::class);
        $this->app->bind(NivelRiesgoReadRepositoryInterface::class, EloquentNivelRiesgoReadRepository::class);
        $this->app->bind(ZonificacionReadRepositoryInterface::class, EloquentZonificacionReadRepository::class);
        $this->app->bind(AutorizacionImprocedenteReadRepositoryInterface::class, EloquentAutorizacionImprocedenteReadRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
