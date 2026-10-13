<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Middleware;

use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autoriza la petición según el rol del usuario autenticado.
 *
 * Uso en rutas: ->middleware(['auth:api', 'rol:ADMIN_MUNICIPALIDAD'])
 *
 * El rol se resuelve contra la base de datos en cada petición (no se confía en el
 * claim del token): si a alguien se le quita el acceso, aplica de inmediato y no
 * al vencer su token.
 */
final class VerificarRol
{
    public function __construct(private readonly UserRepositoryInterface $usuarios) {}

    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        $usuario = $request->user('api');
        if ($usuario === null) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        $autenticado = $this->usuarios->buscarPorId((int) $usuario->getAuthIdentifier());
        if ($autenticado === null || ! in_array($autenticado->role, $rolesPermitidos, true)) {
            return new JsonResponse(['error' => 'No tiene permisos para realizar esta operación.'], 403);
        }

        return $next($request);
    }
}
