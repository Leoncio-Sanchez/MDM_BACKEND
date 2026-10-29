<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Infrastructure\Providers;

use App\Modules\ConsultaPublica\Domain\Contracts\ConsultaPublicaRepositoryInterface;
use App\Modules\ConsultaPublica\Domain\Contracts\CuentaCiudadanoRepositoryInterface;
use App\Modules\ConsultaPublica\Infrastructure\Persistence\Repositories\QueryConsultaPublicaRepository;
use App\Modules\ConsultaPublica\Infrastructure\Persistence\Repositories\QueryCuentaCiudadanoRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class ConsultaPublicaServiceProvider extends ServiceProvider
{
    /** Consultas permitidas por minuto desde una misma IP. */
    public const CONSULTAS_POR_MINUTO = 10;

    /** Intentos por minuto para un mismo documento desde una IP (crear cuenta, restablecer, ingresar) */
    public const INTENTOS_CUENTA_POR_DOCUMENTO = 5;

    /** Tope por IP aunque se prueben documentos distintos */
    public const INTENTOS_CUENTA_POR_IP = 20;

    public function register(): void
    {
        $this->app->bind(ConsultaPublicaRepositoryInterface::class, QueryConsultaPublicaRepository::class);
        $this->app->bind(CuentaCiudadanoRepositoryInterface::class, QueryCuentaCiudadanoRepository::class);
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

        RateLimiter::for('cuenta-ciudadano', function (Request $request): array {
            $respuesta = fn (Request $request, array $headers) => new JsonResponse(
                ['error' => 'Demasiados intentos seguidos. Espere un minuto e intente nuevamente.'],
                429,
                $headers,
            );
            $documento = mb_strtoupper(trim((string) $request->input('documento')));

            return [
                Limit::perMinute(self::INTENTOS_CUENTA_POR_DOCUMENTO)->by('doc:'.$documento.'|'.$request->ip())->response($respuesta),
                Limit::perMinute(self::INTENTOS_CUENTA_POR_IP)->by('ip:'.$request->ip())->response($respuesta),
            ];
        });

        $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
    }
}
