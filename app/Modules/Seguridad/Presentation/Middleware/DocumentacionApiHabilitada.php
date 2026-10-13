<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Oculta la documentación Swagger fuera del entorno local.
 *
 * En producción responde 404 (no revela que existe), salvo que se habilite
 * explícitamente con L5_SWAGGER_PUBLICO=true.
 */
final class DocumentacionApiHabilitada
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local') && ! config('l5-swagger.defaults.publico')) {
            abort(404);
        }

        return $next($request);
    }
}
