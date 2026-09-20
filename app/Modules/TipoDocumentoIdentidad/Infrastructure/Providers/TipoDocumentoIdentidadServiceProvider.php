<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Infrastructure\Providers;

use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;
use App\Modules\TipoDocumentoIdentidad\Infrastructure\Persistence\Repositories\EloquentTipoDocumentoIdentidadRepository;
use Illuminate\Support\ServiceProvider;

class TipoDocumentoIdentidadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TipoDocumentoIdentidadRepositoryInterface::class, EloquentTipoDocumentoIdentidadRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
