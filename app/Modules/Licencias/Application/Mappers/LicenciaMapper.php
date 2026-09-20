<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\Mappers;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Domain\ValueObjects\EstadoLicencia;
use App\Modules\Licencias\Domain\ValueObjects\NombreComercial;
use App\Modules\Licencias\Domain\ValueObjects\NumeroLicencia;
use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;
use Carbon\CarbonImmutable;

final class LicenciaMapper {
    public function toDomain(LicenciaModel $model): Licencia {
        return new Licencia(
            id: $model->id,
            uuid: $model->uuid,
            numeroLicencia: NumeroLicencia::fromString((string)$model->numero_licencia),
            fechaEmision: CarbonImmutable::parse($model->fecha_emision),
            nombreComercial: NombreComercial::fromString($model->nombre_comercial),
            titularId: $model->titular_id,
            expedienteId: $model->expediente_id,
            tipoLicenciaId: $model->tipo_licencia_id,
            nivelRiesgoId: $model->nivel_riesgo_id,
            zonificacionId: $model->zonificacion_id,
            estado: EstadoLicencia::fromString((string)($model->estado ?? 'REGISTRADA')),
        );
    }
    /** @return array<string, mixed> */
    public function toPersistence(Licencia $licencia): array {
        return [
            'uuid' => $licencia->uuid,
            'numero_licencia' => $licencia->numeroLicencia->value,
            'fecha_emision' => $licencia->fechaEmision->toDateString(),
            'nombre_comercial' => $licencia->nombreComercial->value,
            'titular_id' => $licencia->titularId,
            'expediente_id' => $licencia->expedienteId,
            'tipo_licencia_id' => $licencia->tipoLicenciaId,
            'nivel_riesgo_id' => $licencia->nivelRiesgoId,
            'zonificacion_id' => $licencia->zonificacionId,
            'estado' => $licencia->estado->value,
        ];
    }
}
