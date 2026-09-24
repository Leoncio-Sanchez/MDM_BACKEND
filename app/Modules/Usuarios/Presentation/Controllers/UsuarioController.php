<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Usuarios\Application\UseCases\ActualizarUsuarioUseCase;
use App\Modules\Usuarios\Application\UseCases\CambiarPasswordUseCase;
use App\Modules\Usuarios\Application\UseCases\ConstruirMenuUsuarioUseCase;
use App\Modules\Usuarios\Application\UseCases\CrearUsuarioUseCase;
use App\Modules\Usuarios\Application\UseCases\EliminarUsuarioUseCase;
use App\Modules\Usuarios\Application\UseCases\ListarUsuariosUseCase;
use App\Modules\Usuarios\Application\UseCases\ObtenerUsuarioUseCase;
use App\Modules\Usuarios\Presentation\Resources\UsuarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UsuarioController extends Controller
{
    public function __construct(
        private readonly ListarUsuariosUseCase $listarUsuarios,
        private readonly ObtenerUsuarioUseCase $obtenerUsuario,
        private readonly CrearUsuarioUseCase $crearUsuario,
        private readonly ActualizarUsuarioUseCase $actualizarUsuario,
        private readonly EliminarUsuarioUseCase $eliminarUsuario,
        private readonly CambiarPasswordUseCase $cambiarPassword,
        private readonly ConstruirMenuUsuarioUseCase $construirMenu,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $busqueda = $request->query('busqueda');
        $usuarios = $this->listarUsuarios->execute(is_string($busqueda) ? $busqueda : null);

        return response()->json(
            UsuarioResource::collection($usuarios)->resolve()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->only([
            'username',
            'password',
            'first_name',
            'last_name',
            'email',
            'is_active',
            'is_staff',
            'is_superuser',
        ]);

        if (empty($datos['username'])) {
            return response()->json(['error' => 'El username es requerido.'], 400);
        }

        $perfilDatos = $request->input('perfil');
        $digitadorId = $request->user()?->id ? (int) $request->user()->id : null;

        $user = $this->crearUsuario->execute(
            $datos,
            is_array($perfilDatos) ? $perfilDatos : null,
            $digitadorId
        );

        return response()->json((new UsuarioResource($user))->resolve(), 201);
    }

    public function show(int $pk): JsonResponse
    {
        $user = $this->obtenerUsuario->execute($pk);
        if (! $user) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new UsuarioResource($user))->resolve(), 200);
    }

    public function update(Request $request, int $pk): JsonResponse
    {
        $datos = $request->only([
            'first_name',
            'last_name',
            'email',
            'is_active',
            'is_staff',
        ]);

        $perfilDatos = $request->input('perfil');
        $digitadorId = $request->user()?->id ? (int) $request->user()->id : null;

        $user = $this->actualizarUsuario->execute(
            $pk,
            $datos,
            is_array($perfilDatos) ? $perfilDatos : null,
            $digitadorId
        );

        if (! $user) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new UsuarioResource($user))->resolve(), 200);
    }

    public function destroy(int $pk): JsonResponse
    {
        $exito = $this->eliminarUsuario->execute($pk);
        if (! $exito) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json(null, 204);
    }

    public function cambiarPassword(Request $request, int $pk): JsonResponse
    {
        $password = (string) $request->input('password', '');
        $passwordConfirm = (string) $request->input('password_confirm', '');

        $res = $this->cambiarPassword->execute($pk, $password, $passwordConfirm);

        return response()->json($res['data'], $res['status']);
    }

    public function menus(Request $request): JsonResponse
    {
        $user = $request->user();
        $usuarioId = $user !== null ? (int) $user->id : 1;
        $menus = $this->construirMenu->execute($usuarioId);

        return response()->json($menus, 200);
    }
}
