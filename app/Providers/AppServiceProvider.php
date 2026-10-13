<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\DjangoJwtGuard;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // Sin JWT_SECRET propio se usaría la clave de ejemplo y cualquiera podría firmar tokens válidos
        if ($this->app->environment('production')
            && in_array(config('jwt.secret'), [null, '', 'django-insecure-dev-key-change-in-production'], true)) {
            throw new RuntimeException('JWT_SECRET no está configurado para producción.');
        }

        // Register a custom guard that understands Django simplejwt tokens
        // (which use "user_id" instead of the "sub" claim that tymon/jwt-auth expects)
        Auth::extend('django-jwt', function ($app, $name, array $config) {
            return new DjangoJwtGuard($app['request']);
        });
    }
}
