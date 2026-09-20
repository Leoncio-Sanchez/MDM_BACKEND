<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Repositories\EloquentLicenciaRepository;

class LicenciasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LicenciaRepositoryInterface::class, EloquentLicenciaRepository::class);
    }

    public function boot(): void
    {
        Route::middleware('api')->group(__DIR__ . '/../../Presentation/Routes/api.php');
    }
}
