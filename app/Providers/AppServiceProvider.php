<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\DjangoJwtGuard;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // Register a custom guard that understands Django simplejwt tokens
        // (which use "user_id" instead of the "sub" claim that tymon/jwt-auth expects)
        Auth::extend('django-jwt', function ($app, $name, array $config) {
            return new DjangoJwtGuard($app['request']);
        });
    }
}
