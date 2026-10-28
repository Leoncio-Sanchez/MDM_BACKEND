<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Presentation\Controllers;

use App\Modules\Reportes\Application\UseCases\GenerarInformeLicenciasUseCase;
use App\Modules\Reportes\Application\UseCases\PadronLicenciasUseCase;
use App\Modules\Reportes\Application\UseCases\ReporteBajasUseCase;
use App\Modules\Reportes\Application\UseCases\ReporteImprocedentesUseCase;
use App\Modules\Reportes\Domain\ValueObjects\BajaRegistrada;
use App\Modules\Reportes\Domain\ValueObjects\Improcedencia;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use App\Modules\Reportes\Domain\ValueObjects\TipoAutorizacion;
use App\Modules\Reportes\Presentation\Requests\InformeLicenciasRequest;
use App\Modules\Reportes\Presentation\Requests\PeriodoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final readonly class ReportesController
{
    public function __construct(
        private PadronLicenciasUseCase $padron,
        private GenerarInformeLicenciasUseCase $informeLicencias,
        private ReporteBajasUseCase $bajas,
        private ReporteImprocedentesUseCase $improcedentes,
    ) {}

    /** El tipo (LF/ITSE) lo fija la ruta: cada una exige su propio acceso */
    public function bajas(PeriodoRequest $request, string $tipo): JsonResponse
    {
        $r = $this->bajas->execute(TipoAutorizacion::from($tipo), $request->validated('desde'), $request->validated('hasta'));

        return new JsonResponse([
            'desde' => $r['desde'],
            'hasta' => $r['hasta'],
            'resumen' => [
                'total' => count($r['registros']),
                'por_estado' => array_map(
                    fn (string $estado, int $total) => ['estado' => $estado, 'total' => $total],
                    array_keys($r['por_estado']),
                    array_values($r['por_estado']),
                ),
            ],
            'registros' => array_map(fn (BajaRegistrada $b) => [
                'fecha_estado' => $b->fechaEstado,
                'estado' => $b->estado,
                'documento' => $b->documento,
                'motivo' => $b->motivo,
                'numero' => $b->numero,
                'numero_expediente' => $b->numeroExpediente,
                'nombre_comercial' => $b->nombreComercial,
                'titular' => $b->titular,
                'documento_titular' => $b->documentoTitular,
                'direccion' => $b->direccion,
                'fecha_emision' => $b->fechaEmision,
                'registrado_por' => $b->registradoPor,
                'fecha_registro' => $b->fechaRegistro,
            ], $r['registros']),
        ]);
    }

    public function improcedentes(PeriodoRequest $request, string $tipo): JsonResponse
    {
        $r = $this->improcedentes->execute(TipoAutorizacion::from($tipo), $request->validated('desde'), $request->validated('hasta'));

        return new JsonResponse([
            'desde' => $r['desde'],
            'hasta' => $r['hasta'],
            'resumen' => ['total' => count($r['registros'])],
            'registros' => array_map(fn (Improcedencia $i) => [
                'fecha_rechazo' => $i->fechaRechazo,
                'documento' => $i->documento,
                'motivo' => $i->motivo,
                'numero_expediente' => $i->numeroExpediente,
                'procedimiento' => $i->procedimiento,
                'solicitante' => $i->solicitante,
                'documento_solicitante' => $i->documentoSolicitante,
                'fecha_recepcion' => $i->fechaRecepcion,
                'registrado_por' => $i->registradoPor,
                'fecha_registro' => $i->fechaRegistro,
            ], $r['registros']),
        ]);
    }

    public function informeLicenciasPdf(InformeLicenciasRequest $request): Response
    {
        $r = $this->informeLicencias->execute(
            (string) $request->validated('desde'),
            (string) $request->validated('hasta'),
            (int) $request->user()?->getAuthIdentifier(),
        );

        return new Response($r['contenido'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$r['nombre_archivo'].'"',
            'X-Informe-Codigo' => $r['codigo'],
            'X-Informe-Reimpresion' => $r['reimpresion'] ? '1' : '0',
            'Access-Control-Expose-Headers' => 'Content-Disposition, X-Informe-Codigo, X-Informe-Reimpresion',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function padronLicencias(PeriodoRequest $request): JsonResponse
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
