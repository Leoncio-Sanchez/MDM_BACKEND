<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Controllers;

use App\Modules\Inspecciones\Application\UseCases\ActualizarAsignacionUseCase;
use App\Modules\Inspecciones\Application\UseCases\AsignarInspectorUseCase;
use App\Modules\Inspecciones\Application\UseCases\ReasignarInspectorUseCase;
use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosAsignacion;
use App\Modules\Inspecciones\Presentation\Requests\ActualizarAsignacionRequest;
use App\Modules\Inspecciones\Presentation\Requests\AsignarInspectorRequest;
use App\Modules\Inspecciones\Presentation\Requests\ReasignarInspectorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class AsignacionInspeccionController
{
    public function __construct(private AsignacionInspeccionRepositoryInterface $asignaciones) {}

    // ── Jefe de ITSE ──────────────────────────────────────────────────────────

    public function porInspeccionar(): JsonResponse
    {
        return new JsonResponse($this->asignaciones->expedientesPorInspeccionar());
    }

    public function inspectores(): JsonResponse
    {
        return new JsonResponse($this->asignaciones->inspectoresDisponibles());
    }

    public function asignar(AsignarInspectorRequest $request, AsignarInspectorUseCase $useCase): JsonResponse
    {
        $asignacion = $useCase->execute(
            (int) $request->validated('expediente_id'),
            new DatosAsignacion(
                inspectorId: (int) $request->validated('inspector_id'),
                nombreComercial: (string) $request->validated('nombre_comercial'),
                direccion: (string) $request->validated('direccion'),
                fechaProgramada: $this->texto($request->validated('fecha_programada')),
                indicaciones: $this->texto($request->validated('indicaciones')),
                acompanantes: $this->acompanantes($request) ?? [],
            ),
            $this->usuarioId($request),
        );

        return new JsonResponse($asignacion, 201);
    }

    public function actualizar(int $pk, ActualizarAsignacionRequest $request, ActualizarAsignacionUseCase $useCase): JsonResponse
    {
        return new JsonResponse($useCase->execute(
            $pk,
            (string) $request->validated('nombre_comercial'),
            (string) $request->validated('direccion'),
            $this->texto($request->validated('fecha_programada')),
            $this->texto($request->validated('indicaciones')),
            $this->acompanantes($request),
            $this->usuarioId($request),
        ));
    }

    public function reasignar(int $pk, ReasignarInspectorRequest $request, ReasignarInspectorUseCase $useCase): JsonResponse
    {
        return new JsonResponse($useCase->execute(
            $pk,
            (int) $request->validated('inspector_id'),
            $this->acompanantes($request),
            (string) $request->validated('motivo'),
            $this->texto($request->validated('fecha_programada')),
            $this->texto($request->validated('indicaciones')),
            $this->usuarioId($request),
        ), 201);
    }

    public function historial(int $expedienteId): JsonResponse
    {
        return new JsonResponse($this->asignaciones->historial($expedienteId));
    }

    // ── Inspector ─────────────────────────────────────────────────────────────

    public function miBandeja(Request $request): JsonResponse
    {
        return new JsonResponse($this->asignaciones->bandeja($this->usuarioId($request)));
    }

    private function usuarioId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }

    /** @return array<int, int>|null null si el pedido no trae el campo (se conserva el equipo) */
    private function acompanantes(Request $request): ?array
    {
        if (! $request->has('acompanantes')) {
            return null;
        }

        return array_map('intval', (array) $request->validated('acompanantes'));
    }

    private function texto(mixed $valor): ?string
    {
        return is_string($valor) ? $valor : null;
    }
}
