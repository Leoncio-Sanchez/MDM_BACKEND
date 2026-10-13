<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Middleware;

use App\Modules\Seguridad\Domain\Contracts\UserRepositoryInterface;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autoriza la petición según los accesos del perfil (usuarios_perfiles).
 *
 * Uso en rutas, después de 'rol:ADMIN_MUNICIPALIDAD':
 *   'permiso:licencias'                         lectura y escritura requieren "licencias"
 *   'permiso:*,admin'                           cualquiera lee; solo "admin" escribe
 *   'permiso:expedientes|licencias|itse,expedientes'  lee quien tenga alguno; escribe "expedientes"
 *
 * Los permisos se leen de la base en cada petición: un cambio aplica de inmediato.
 */
final class VerificarPermiso
{
    private const CUALQUIERA = '*';

    public function __construct(private readonly UserRepositoryInterface $usuarios) {}

    public function handle(Request $request, Closure $next, string $lectura, ?string $escritura = null): Response
    {
        $usuario = $request->user('api');
        if ($usuario === null) {
            return new JsonResponse(['message' => 'Unauthenticated.'], 401);
        }

        $requeridos = $request->isMethodSafe() ? $lectura : ($escritura ?? $lectura);
        if ($requeridos === self::CUALQUIERA) {
            return $next($request);
        }

        $autenticado = $this->usuarios->buscarPorId((int) $usuario->getAuthIdentifier());
        if ($autenticado === null || ! $autenticado->permisos->tieneAlguno(explode('|', $requeridos))) {
            return new JsonResponse(['error' => 'No tiene permisos para realizar esta operación.'], 403);
        }

        return $next($request);
    }
}
