<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\Mappers;

use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;
use Carbon\CarbonImmutable;

final class LicenciaMapper
{
    public function toDomain(LicenciaModel $model): Licencia
    {
        return new Licencia(
            id: $model->id,
            uuid: $model->uuid,
            numeroLicencia: NumeroLicencia::fromInt((int) $model->numero_licencia),
            fechaEmision: CarbonImmutable::parse($model->fecha_emision),
            nombreComercial: NombreComercial::fromString($model->nombre_comercial),
            titularId: (int) $model->titular_id,
            conductorId: (int) $model->conductor_id,
            licenciaPrincipalId: $model->licencia_principal_id,
            expedienteId: (int) $model->expediente_id,
            tipoLicenciaId: (int) $model->tipo_licencia_id,
            nivelRiesgoId: (int) $model->nivel_riesgo_id,
            zonificacionId: (int) $model->zonificacion_id,
            esVigenciaIndeterminada: (bool) $model->es_vigencia_indeterminada,
            fechaInicioVigencia: $model->fecha_inicio_vigencia ? CarbonImmutable::parse($model->fecha_inicio_vigencia) : null,
            fechaFinVigencia: $model->fecha_fin_vigencia ? CarbonImmutable::parse($model->fecha_fin_vigencia) : null,
            actividad: (string) $model->actividad,
            direccion: (string) $model->direccion,
            horaDesde: (int) $model->hora_desde,
            horaHasta: (int) $model->hora_hasta,
            resolucionNumero: (string) $model->resolucion_numero,
            area: (float) $model->area,
            numeroReciboPago: (string) $model->numero_recibo_pago,
            observaciones: $model->observaciones,
            sePuedePublicar: (bool) $model->se_puede_publicar,
            fechaNotificacion: $model->fecha_notificacion ? CarbonImmutable::parse($model->fecha_notificacion) : null,
            estado: EstadoLicencia::fromString($model->estado ?? 'REGISTRADA'),
        );
    }

    public function toPersistence(Licencia $licencia): array
    {
        return [
            'uuid' => $licencia->uuid,
            'numero_licencia' => $licencia->numeroLicencia->value,
            'fecha_emision' => $licencia->fechaEmision->toDateString(),
            'nombre_comercial' => $licencia->nombreComercial->value,
            'titular_id' => $licencia->titularId,
            'conductor_id' => $licencia->conductorId,
            'licencia_principal_id' => $licencia->licenciaPrincipalId,
            'expediente_id' => $licencia->expedienteId,
            'tipo_licencia_id' => $licencia->tipoLicenciaId,
            'nivel_riesgo_id' => $licencia->nivelRiesgoId,
            'zonificacion_id' => $licencia->zonificacionId,
            'es_vigencia_indeterminada' => $licencia->esVigenciaIndeterminada,
            'fecha_inicio_vigencia' => $licencia->fechaInicioVigencia?->toDateString(),
            'fecha_fin_vigencia' => $licencia->fechaFinVigencia?->toDateString(),
            'actividad' => $licencia->actividad,
            'direccion' => $licencia->direccion,
            'hora_desde' => $licencia->horaDesde,
            'hora_hasta' => $licencia->horaHasta,
            'resolucion_numero' => $licencia->resolucionNumero,
            'area' => $licencia->area,
            'numero_recibo_pago' => $licencia->numeroReciboPago,
            'observaciones' => $licencia->observaciones,
            'se_puede_publicar' => $licencia->sePuedePublicar,
            'fecha_notificacion' => $licencia->fechaNotificacion?->toDateTimeString(),
        ];
    }
}
