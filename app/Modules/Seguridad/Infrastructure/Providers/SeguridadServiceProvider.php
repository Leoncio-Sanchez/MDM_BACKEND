<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Infrastructure\Providers;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Seguridad\Domain\Contracts\RevisionAccesosRepositoryInterface;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Infrastructure\Hashing\DjangoPbkdf2Hasher;
use App\Modules\Seguridad\Infrastructure\Jwt\DjangoJwtTokenService;
use App\Modules\Seguridad\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Modules\Seguridad\Infrastructure\Persistence\Repositories\QueryRevisionAccesosRepository;
use App\Modules\Seguridad\Presentation\Console\RevisarAccesosCommand;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class SeguridadServiceProvider extends ServiceProvider
{
    /** Intentos de login por minuto para un mismo usuario desde una misma IP. */
    public const INTENTOS_POR_USUARIO = 5;

    /** Intentos de login por minuto desde una misma IP (con cualquier usuario). */
    public const INTENTOS_POR_IP = 20;

    public function register(): void
    {
        $this->app->bind(PasswordHasherInterface::class, DjangoPbkdf2Hasher::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RevisionAccesosRepositoryInterface::class, QueryRevisionAccesosRepository::class);

        $this->app->singleton(TokenServiceInterface::class, function () {
            $secret = (string) config('jwt.secret', 'django-insecure-dev-key-change-in-production');
            if ($secret === '') {
                $secret = 'django-insecure-dev-key-change-in-production';
            }
            $accessHours = (int) config('jwt.access_lifetime_hours', 1);
            $refreshDays = (int) config('jwt.refresh_lifetime_days', 7);

            return new DjangoJwtTokenService(
                secret: $secret,
                accessTtlSeconds: max(1, $accessHours * 3600),
                refreshTtlSeconds: max(1, $refreshDays * 86400),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([RevisarAccesosCommand::class]);
        }

        // Contra el robo de contraseñas por fuerza bruta: pocos intentos por usuario e IP,
        // y un tope por IP para quien prueba muchos usuarios desde la misma máquina.
        RateLimiter::for('login', function (Request $request): array {
            $respuesta = fn (Request $request, array $headers) => new JsonResponse(
                ['detail' => 'Demasiados intentos de inicio de sesión. Espere un minuto e intente nuevamente.'],
                429,
                $headers,
            );
            $usuario = mb_strtolower(trim((string) $request->input('username')));

            return [
                Limit::perMinute(self::INTENTOS_POR_USUARIO)->by('usuario:'.$usuario.'|'.$request->ip())->response($respuesta),
                Limit::perMinute(self::INTENTOS_POR_IP)->by('ip:'.$request->ip())->response($respuesta),
            ];
        });

        $routesPath = __DIR__.'/../../Presentation/Routes/api.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }
}
