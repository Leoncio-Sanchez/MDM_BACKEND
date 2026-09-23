<?php

declare(strict_types=1);

namespace App\Modules\Pide\Infrastructure\Providers;

use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;
use App\Modules\Pide\Domain\Contracts\SunatClientInterface;
use App\Modules\Pide\Infrastructure\External\ReniecHttpClient;
use App\Modules\Pide\Infrastructure\External\SunatHttpClient;
use Illuminate\Support\ServiceProvider;

final class PideServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReniecClientInterface::class, function (): ReniecClientInterface {
            return new ReniecHttpClient(
                url: (string) config('services.reniec.url', 'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar'),
                dniUsuario: (string) config('services.reniec.dni_usuario', ''),
                rucUsuario: (string) config('services.reniec.ruc_usuario', ''),
                password: (string) config('services.reniec.password', ''),
                timeout: (int) config('services.reniec.timeout', 10),
            );
        });

        $this->app->singleton(SunatClientInterface::class, function (): SunatClientInterface {
            return new SunatHttpClient(
                url: (string) config('services.sunat.url', 'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales'),
            );
        });
    }

    public function boot(): void
    {
        if (file_exists(__DIR__.'/../../Presentation/Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../../Presentation/Routes/api.php');
        }
    }
}
