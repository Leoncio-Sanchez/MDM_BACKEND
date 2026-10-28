<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Presentation\Controllers;

use App\Modules\Reportes\Application\UseCases\PadronLicenciasUseCase;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use App\Modules\Reportes\Presentation\Requests\PadronLicenciasRequest;
use Illuminate\Http\JsonResponse;

final readonly class ReportesController
{
    public function __construct(private PadronLicenciasUseCase $padron) {}

    public function padronLicencias(PadronLicenciasRequest $request): JsonResponse
    {
        $r = $this->padron->execute($request->validated('desde'), $request->validated('hasta'));
        $resumen = $r['resumen'];

        return new JsonResponse([
            'desde' => $r['desde'],
            'hasta' => $r['hasta'],
            'resumen' => [
                'total' => $resumen->total,
                'por_nivel_riesgo' => array_map(
                    fn (string $nivel, int $total) => ['nivel' => $nivel, 'total' => $total],
                    array_keys($resumen->porNivelRiesgo),
                    array_values($resumen->porNivelRiesgo),
                ),
                'tiempo_promedio_dias_habiles' => $resumen->tiempoPromedioDiasHabiles,
                'dentro_de_plazo' => $resumen->dentroDePlazo,
                'porcentaje_cumplimiento' => $resumen->porcentajeCumplimiento,
            ],
            'licencias' => array_map(fn (LicenciaPadron $l) => [
                'numero_licencia' => $l->numeroLicencia,
                'numero_expediente' => $l->numeroExpediente,
                'nombre_comercial' => $l->nombreComercial,
                'titular' => $l->titular,
                'documento_titular' => $l->documentoTitular,
                'giros' => $l->giros,
                'zonificacion' => $l->zonificacion,
                'nivel_riesgo' => $l->nivelRiesgo,
                'tipo_licencia' => $l->tipoLicencia,
                'fecha_recepcion' => $l->fechaRecepcion,
                'fecha_emision' => $l->fechaEmision,
                'dias_habiles' => $l->diasHabiles,
                'plazo_dias' => $l->plazoDias,
                'dentro_de_plazo' => $l->dentroDePlazo(),
            ], $r['licencias']),
        ]);
    }
}
