<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Infrastructure\Providers;
use Illuminate\Support\ServiceProvider;
use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Infrastructure\Persistence\Repositories\EloquentTipoLicenciaRepository;

final class TipoLicenciaServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->bind(TipoLicenciaRepositoryInterface::class, EloquentTipoLicenciaRepository::class);
    }
    public function boot(): void {
        $this->loadRoutesFrom(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
