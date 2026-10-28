<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Infrastructure\Providers;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Infrastructure\Persistence\Repositories\QueryReportesRepository;
use Illuminate\Support\ServiceProvider;

final class ReportesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReportesRepositoryInterface::class, QueryReportesRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
