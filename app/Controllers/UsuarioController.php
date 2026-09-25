<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Requests\Usuarios\CambiarPasswordRequest;
use App\Requests\Usuarios\StoreUsuarioRequest;
use App\Requests\Usuarios\UpdateUsuarioRequest;
use App\Resources\UsuarioResource;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioService $usuarioService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $busqueda = $request->query('busqueda');
        $usuarios = $this->usuarioService->listar(is_string($busqueda) ? $busqueda : null);

        return response()->json(
            UsuarioResource::collection($usuarios)->resolve()
        );
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $perfilDatos = $request->input('perfil');
        $digitadorId = $request->user()?->id ? (int) $request->user()->id : null;

        $user = $this->usuarioService->crear(
            $datos,
            is_array($perfilDatos) ? $perfilDatos : null,
            $digitadorId
        );

        return response()->json((new UsuarioResource($user))->resolve(), 201);
    }

    public function show(int $pk): JsonResponse
    {
        $user = $this->usuarioService->buscarPorId($pk);
        if (! $user) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json((new UsuarioResource($user))->resolve(), 200);
    }

    public function update(UpdateUsuarioRequest $request, int $pk): JsonResponse
    {
        $datos = $request->validated();
        $perfilDatos = $request->input('perfil');
        $digitadorId = $request->user()?->id ? (int) $request->user()->id : null;

        $user = $this->usuarioService->actualizar(
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
        $exito = $this->usuarioService->eliminar($pk);
        if (! $exito) {
            return response()->json(['detail' => 'No encontrado.'], 404);
        }

        return response()->json(null, 204);
    }

    public function detalle(int $pk): JsonResponse
    {
        return $this->show($pk);
    }

    public function cambiarPassword(CambiarPasswordRequest $request, int $pk): JsonResponse
    {
        $password = (string) $request->input('password', '');
        $passwordConfirm = (string) $request->input('password_confirm', '');

        $res = $this->usuarioService->cambiarPassword($pk, $password, $passwordConfirm);

        return response()->json($res['data'], $res['status']);
    }

    public function menus(Request $request): JsonResponse
    {
        $user = $request->user();
        $usuarioId = $user !== null ? (int) $user->id : 1;
        $menus = $this->usuarioService->construirMenus($usuarioId);

        return response()->json($menus, 200);
    }
}
