<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Presentation\Controllers;

use App\Modules\Inspecciones\Application\UseCases\AnularActaUseCase;
use App\Modules\Inspecciones\Application\UseCases\RegistrarActaUseCase;
use App\Modules\Inspecciones\Domain\Contracts\ActaInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\ActaNoEncontrada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosActa;
use App\Modules\Inspecciones\Domain\ValueObjects\EstadoChecklist;
use App\Modules\Inspecciones\Presentation\Requests\AnularActaRequest;
use App\Modules\Inspecciones\Presentation\Requests\RegistrarActaRequest;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

final readonly class ActaInspeccionController
{
    public function __construct(private ActaInspeccionRepositoryInterface $actas) {}

    // ── Inspector ─────────────────────────────────────────────────────────────

    public function registrar(int $asignacionId, RegistrarActaRequest $request, RegistrarActaUseCase $useCase): JsonResponse
    {
        $checklist = [];
        foreach (array_keys(DatosActa::GRUPOS) as $grupo) {
            $checklist[$grupo] = EstadoChecklist::from((string) $request->validated($grupo));
        }
        $plazo = $request->validated('plazo_subsanacion_dias');
        $observaciones = $request->validated('observaciones');

        $datos = new DatosActa(
            numeroActa: (string) $request->validated('numero_acta'),
            fechaVisita: (new DateTimeImmutable((string) $request->validated('fecha_visita'), new DateTimeZone('America/Lima')))
                ->setTimezone(new DateTimeZone((string) config('app.timezone'))),
            areaM2: (float) $request->validated('area_m2'),
            aforo: (int) $request->validated('aforo'),
            checklist: $checklist,
            observaciones: is_string($observaciones) ? $observaciones : null,
            plazoSubsanacionDias: $plazo === null ? null : (int) $plazo,
        );

        /** @var array<int, array{tipo: string, archivo: UploadedFile}> $fotos */
        $fotos = array_values(array_map(
            static fn (array $foto): array => ['tipo' => (string) $foto['tipo'], 'archivo' => $foto['archivo']],
            (array) $request->validated('fotos'),
        ));

        return new JsonResponse($useCase->execute($asignacionId, $this->usuarioId($request), $datos, $fotos), 201);
    }

    public function verComoInspector(int $actaId, Request $request): JsonResponse
    {
        $this->exigirAlcanceInspector($actaId, $request);

        return $this->ver($actaId);
    }

    public function fotoComoInspector(int $actaId, int $fotoId, Request $request): Response
    {
        $this->exigirAlcanceInspector($actaId, $request);

        return $this->foto($actaId, $fotoId);
    }

    // ── Oficina (ITSE y jefe de ITSE) ─────────────────────────────────────────

    public function deExpediente(int $expedienteId): JsonResponse
    {
        return new JsonResponse($this->actas->deExpediente($expedienteId));
    }

    public function ver(int $actaId): JsonResponse
    {
        return new JsonResponse($this->actas->buscar($actaId) ?? throw ActaNoEncontrada::id($actaId));
    }

    public function foto(int $actaId, int $fotoId): Response
    {
        $resultado = $this->actas->leerFoto($actaId, $fotoId);
        if ($resultado === null) {
            return new JsonResponse(['error' => 'La foto no existe.'], 404);
        }
        $contenido = $resultado['contenido'];
        $cabeceras = [
            'Content-Type' => $resultado['foto']['extension'] === 'png' ? 'image/png' : 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ];

        if (is_resource($contenido)) {
            return response()->stream(function () use ($contenido): void {
                fpassthru($contenido);
                fclose($contenido);
            }, 200, $cabeceras);
        }

        return response((string) $contenido, 200, $cabeceras);
    }

    // ── Jefe de ITSE ──────────────────────────────────────────────────────────

    public function anular(int $actaId, AnularActaRequest $request, AnularActaUseCase $useCase): JsonResponse
    {
        $documento = $request->validated('documento');

        return new JsonResponse($useCase->execute(
            $actaId,
            (string) $request->validated('motivo'),
            is_string($documento) ? $documento : null,
            $this->usuarioId($request),
        ));
    }

    private function exigirAlcanceInspector(int $actaId, Request $request): void
    {
        if (! $this->actas->inspectorPuedeVer($actaId, $this->usuarioId($request))) {
            throw ActaNoEncontrada::id($actaId);
        }
    }

    private function usuarioId(Request $request): int
    {
        return (int) $request->user()?->getAuthIdentifier();
    }
}
