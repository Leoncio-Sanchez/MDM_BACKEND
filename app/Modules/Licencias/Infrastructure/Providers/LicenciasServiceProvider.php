<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Providers;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\NivelRiesgoReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\PersonaReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\TipoLicenciaReadRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\ZonificacionReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentExpedienteReadRepository;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentLicenciaRepository;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentNivelRiesgoReadRepository;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentPersonaReadRepository;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentTipoLicenciaReadRepository;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentZonificacionReadRepository;
use Illuminate\Support\ServiceProvider;

final class LicenciasServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(\App\Modules\Licencias\Domain\Contracts\LicenciaGiroRepositoryInterface::class, \App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentLicenciaGiroRepository::class);
        $this->app->bind(\App\Modules\Licencias\Domain\Contracts\LicenciaArchivoRepositoryInterface::class, \App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentLicenciaArchivoRepository::class);
        $this->app->bind(LicenciaRepositoryInterface::class, EloquentLicenciaRepository::class);
        $this->app->bind(ExpedienteReadRepositoryInterface::class, EloquentExpedienteReadRepository::class);
        $this->app->bind(PersonaReadRepositoryInterface::class, EloquentPersonaReadRepository::class);
        $this->app->bind(TipoLicenciaReadRepositoryInterface::class, EloquentTipoLicenciaReadRepository::class);
        $this->app->bind(NivelRiesgoReadRepositoryInterface::class, EloquentNivelRiesgoReadRepository::class);
        $this->app->bind(ZonificacionReadRepositoryInterface::class, EloquentZonificacionReadRepository::class);
    }
    public function boot(): void {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
