<?php

declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\Providers;

use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\ReniecClientInterface;
use App\Modules\Personas\Infrastructure\External\ReniecHttpClient;
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaDocumentoRepository;
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaRepository;
use Illuminate\Support\ServiceProvider;

class PersonasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PersonaRepositoryInterface::class, EloquentPersonaRepository::class);
        $this->app->bind(PersonaDocumentoRepositoryInterface::class, EloquentPersonaDocumentoRepository::class);

        $this->app->singleton(ReniecClientInterface::class, function ($app) {
            return new ReniecHttpClient(
                url: config('services.reniec.url', 'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar'),
                dniUsuario: config('services.reniec.dni_usuario', ''),
                rucUsuario: config('services.reniec.ruc_usuario', ''),
                password: config('services.reniec.password', ''),
                timeout: (int) config('services.reniec.timeout', 10),
            );
        });
    }

    public function boot(): void
    {
        if (file_exists(__DIR__.'/../../Presentation/Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
        }
    }
}
