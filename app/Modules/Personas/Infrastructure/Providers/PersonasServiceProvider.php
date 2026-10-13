<?php

declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\Providers;

use App\Modules\Personas\Domain\Contracts\PersonaDocumentoRepositoryInterface;
use App\Modules\Personas\Domain\Contracts\PersonaRepositoryInterface;
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaDocumentoRepository;
use App\Modules\Personas\Infrastructure\Persistence\Repositories\EloquentPersonaRepository;
use Illuminate\Support\ServiceProvider;

class PersonasServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PersonaRepositoryInterface::class, EloquentPersonaRepository::class);
        $this->app->bind(PersonaDocumentoRepositoryInterface::class, EloquentPersonaDocumentoRepository::class);
    }

    public function boot(): void
    {
        if (file_exists(__DIR__.'/../../Presentation/Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
        }
    }
}
