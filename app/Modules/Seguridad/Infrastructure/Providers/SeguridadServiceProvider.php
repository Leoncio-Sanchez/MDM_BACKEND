<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Infrastructure\Providers;

use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Seguridad\Domain\Contracts\TokenServiceInterface;
use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Seguridad\Infrastructure\Hashing\DjangoPbkdf2Hasher;
use App\Modules\Seguridad\Infrastructure\Jwt\DjangoJwtTokenService;
use App\Modules\Seguridad\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class SeguridadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PasswordHasherInterface::class, DjangoPbkdf2Hasher::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

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
}
