<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Resources;

use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class LicenciaListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $id = is_array($this->resource) ? ($this->resource['id'] ?? null) : $this->resource->id;

        $model = LicenciaModel::find($id);
        if (! $model) {
            return [];
        }

        $expediente = DB::table('expedientes')->find($model->expediente_id);
        $titular = DB::table('personas')->find($model->titular_id);
        $conductor = DB::table('personas')->find($model->conductor_id);
        $tipoLicencia = DB::table('tipos_licencia')->find($model->tipo_licencia_id);
        $nivelRiesgo = DB::table('niveles_riesgo')->find($model->nivel_riesgo_id);
        $zonificacion = DB::table('zonificaciones')->find($model->zonificacion_id);

        $titularNombre = $titular
            ? trim(($titular->apellido_paterno ?? '').' '.($titular->apellido_materno ?? '').' '.($titular->nombres ?? ''))
            : null;

        $conductorNombre = $conductor
            ? trim(($conductor->apellido_paterno ?? '').' '.($conductor->apellido_materno ?? '').' '.($conductor->nombres ?? ''))
            : null;

        return [
            'actividad' => $model->actividad,
            'area' => number_format((float) $model->area, 1, '.', ''),
            'conductor_id' => $model->conductor_id,
            'conductor_nombre' => $conductorNombre,
            'direccion' => $model->direccion,
            'es_vigencia_indeterminada' => (bool) $model->es_vigencia_indeterminada,
            'esta_activo' => true,
            'expediente_id' => $model->expediente_id,
            'fecha_digitacion' => $model->fecha_digitacion ? Carbon::parse($model->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'fecha_emision' => $model->fecha_emision ? Carbon::parse($model->fecha_emision)->toDateString() : null,
            'fecha_fin_vigencia' => $model->fecha_fin_vigencia ? Carbon::parse($model->fecha_fin_vigencia)->toDateString() : null,
            'fecha_inicio_vigencia' => $model->fecha_inicio_vigencia ? Carbon::parse($model->fecha_inicio_vigencia)->toDateString() : null,
            'fecha_notificacion' => $model->fecha_notificacion ? Carbon::parse($model->fecha_notificacion)->toDateString() : null,
            'fecha_recepcion' => $expediente && $expediente->fecha_recepcion ? Carbon::parse($expediente->fecha_recepcion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'hora_desde' => (int) $model->hora_desde,
            'hora_hasta' => (int) $model->hora_hasta,
            'id' => $model->id,
            'licencia_principal_id' => $model->licencia_principal_id,
            'nivel_riesgo_id' => $model->nivel_riesgo_id,
            'nivel_riesgo_nombre' => $nivelRiesgo ? $nivelRiesgo->nombre : null,
            'nombre_comercial' => $model->nombre_comercial,
            'numero_expediente' => $expediente ? $expediente->numero_expediente : null,
            'numero_licencia' => $model->numero_licencia,
            'numero_recibo_pago' => $model->numero_recibo_pago,
            'observaciones' => $model->observaciones,
            'resolucion_numero' => $model->resolucion_numero,
            'se_puede_publicar' => (bool) $model->se_puede_publicar,
            'tipo_licencia_id' => $model->tipo_licencia_id,
            'tipo_licencia_nombre' => $tipoLicencia ? $tipoLicencia->nombre : null,
            'titular_id' => $model->titular_id,
            'titular_nombre' => $titularNombre,
            'titular_ruc' => null,
            'usuario_id' => $model->usuario_id,
            'zonificacion_id' => $model->zonificacion_id,
            'zonificacion_nombre' => $zonificacion ? $zonificacion->nombre : null,
        ];
    }
}
