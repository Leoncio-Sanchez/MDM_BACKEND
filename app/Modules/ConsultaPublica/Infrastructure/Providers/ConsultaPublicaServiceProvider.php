<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Infrastructure\Providers;

use App\Modules\ConsultaPublica\Domain\Contracts\ConsultaPublicaRepositoryInterface;
use App\Modules\ConsultaPublica\Infrastructure\Persistence\Repositories\QueryConsultaPublicaRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class ConsultaPublicaServiceProvider extends ServiceProvider
{
    /** Consultas permitidas por minuto desde una misma IP. */
    public const CONSULTAS_POR_MINUTO = 10;

    public function register(): void
    {
        $this->app->bind(ConsultaPublicaRepositoryInterface::class, QueryConsultaPublicaRepository::class);
    }

    public function boot(): void
    {
        RateLimiter::for('consulta-publica', fn (Request $request) => Limit::perMinute(self::CONSULTAS_POR_MINUTO)
            ->by((string) $request->ip())
            ->response(fn (Request $request, array $headers) => new JsonResponse(
                ['error' => 'Realizó demasiadas consultas seguidas. Espere un minuto e intente nuevamente.'],
                429,
                $headers,
            )));

        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
