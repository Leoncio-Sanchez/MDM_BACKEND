<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Infrastructure\Providers;

use App\Modules\Usuarios\Domain\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Infrastructure\Persistence\Repositories\EloquentUsuarioRepository;
use Illuminate\Support\ServiceProvider;

final class UsuariosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UsuarioRepositoryInterface::class, EloquentUsuarioRepository::class);
    }

    public function boot(): void
    {
        $routesPath = __DIR__.'/../../Presentation/Routes/api.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }
}
