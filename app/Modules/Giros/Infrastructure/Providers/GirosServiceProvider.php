<?php

declare(strict_types=1);

namespace App\Modules\Giros\Infrastructure\Providers;

use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;
use App\Modules\Giros\Infrastructure\Persistence\Repositories\EloquentGiroRepository;
use Illuminate\Support\ServiceProvider;

class GirosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GiroRepositoryInterface::class, EloquentGiroRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
