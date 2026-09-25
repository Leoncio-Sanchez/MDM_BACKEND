<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Requests\Archivos\SubirArchivoRequest;
use App\Resources\ArchivoResource;
use App\Services\ArchivoService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ArchivoController
{
    public function __construct(
        private readonly ArchivoService $service,
    ) {}

    // ═══════════════════════════════════════════════════════════════════════════
    // EXPEDIENTES
    // ═══════════════════════════════════════════════════════════════════════════

    public function listarExpediente(int $pk): JsonResponse
    {
        $archivos = $this->service->listar('expediente', $pk);

        return response()->json(ArchivoResource::collection($archivos)->resolve());
    }

    public function subirExpediente(SubirArchivoRequest $request, int $pk): JsonResponse
    {
        $usuarioId = (int) ($request->user()?->getAuthIdentifier() ?? 1);
        $archivo = $this->service->subir('expediente', $pk, $request->file('archivo'), $usuarioId);

        return response()->json(new ArchivoResource($archivo), 201);
    }

    public function eliminarExpediente(int $pk): SymfonyResponse
    {
        $this->service->eliminar('expediente', $pk);

        return response()->noContent();
    }

    public function descargarExpediente(string $uuid): SymfonyResponse
    {
        return $this->service->descargar('expediente', $uuid);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ITSE
    // ═══════════════════════════════════════════════════════════════════════════

    public function listarItse(int $pk): JsonResponse
    {
        $archivos = $this->service->listar('itse', $pk);

        return response()->json(ArchivoResource::collection($archivos)->resolve());
    }

    public function subirItse(SubirArchivoRequest $request, int $pk): JsonResponse
    {
        $usuarioId = (int) ($request->user()?->getAuthIdentifier() ?? 1);
        $archivo = $this->service->subir('itse', $pk, $request->file('archivo'), $usuarioId);

        return response()->json(new ArchivoResource($archivo), 201);
    }

    public function eliminarItse(int $pk): SymfonyResponse
    {
        $this->service->eliminar('itse', $pk);

        return response()->noContent();
    }

    public function descargarItse(string $uuid): SymfonyResponse
    {
        return $this->service->descargar('itse', $uuid);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // LICENCIAS DE FUNCIONAMIENTO
    // ═══════════════════════════════════════════════════════════════════════════

    public function listarLicencia(int $pk): JsonResponse
    {
        $archivos = $this->service->listar('licencia', $pk);

        return response()->json(ArchivoResource::collection($archivos)->resolve());
    }

    public function subirLicencia(SubirArchivoRequest $request, int $pk): JsonResponse
    {
        $usuarioId = (int) ($request->user()?->getAuthIdentifier() ?? 1);
        $archivo = $this->service->subir('licencia', $pk, $request->file('archivo'), $usuarioId);

        return response()->json(new ArchivoResource($archivo), 201);
    }

    public function eliminarLicencia(int $pk): SymfonyResponse
    {
        $this->service->eliminar('licencia', $pk);

        return response()->noContent();
    }

    public function descargarLicencia(string $uuid): SymfonyResponse
    {
        return $this->service->descargar('licencia', $uuid);
    }
}
