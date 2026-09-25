<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\LicenciaFuncionamiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class LicenciaListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource)
            ? $this->resource
            : (is_object($this->resource) ? (array) $this->resource : []);

        $id = $data['id'] ?? null;
        if (! $id) {
            return [];
        }

        // If data already contains joined fields from repository query
        if (array_key_exists('titular_nombre', $data)) {
            return [
                'actividad' => $data['actividad'] ?? '',
                'area' => isset($data['area']) ? number_format((float) $data['area'], 1, '.', '') : '0.0',
                'conductor_id' => $data['conductor_id'] ?? null,
                'conductor_nombre' => $data['conductor_nombre'] ?? null,
                'direccion' => $data['direccion'] ?? '',
                'es_vigencia_indeterminada' => (bool) ($data['es_vigencia_indeterminada'] ?? false),
                'esta_activo' => (bool) ($data['esta_activo'] ?? true),
                'expediente_id' => $data['expediente_id'] ?? null,
                'fecha_digitacion' => ! empty($data['fecha_digitacion']) ? Carbon::parse($data['fecha_digitacion'])->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
                'fecha_emision' => ! empty($data['fecha_emision']) ? Carbon::parse($data['fecha_emision'])->toDateString() : null,
                'fecha_fin_vigencia' => ! empty($data['fecha_fin_vigencia']) ? Carbon::parse($data['fecha_fin_vigencia'])->toDateString() : null,
                'fecha_inicio_vigencia' => ! empty($data['fecha_inicio_vigencia']) ? Carbon::parse($data['fecha_inicio_vigencia'])->toDateString() : null,
                'fecha_notificacion' => ! empty($data['fecha_notificacion']) ? Carbon::parse($data['fecha_notificacion'])->toDateString() : null,
                'fecha_recepcion' => ! empty($data['fecha_recepcion']) ? Carbon::parse($data['fecha_recepcion'])->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
                'hora_desde' => (int) ($data['hora_desde'] ?? 0),
                'hora_hasta' => (int) ($data['hora_hasta'] ?? 0),
                'id' => (int) $data['id'],
                'licencia_principal_id' => $data['licencia_principal_id'] ?? null,
                'nivel_riesgo_id' => $data['nivel_riesgo_id'] ?? null,
                'nivel_riesgo_nombre' => $data['nivel_riesgo_nombre'] ?? null,
                'nombre_comercial' => $data['nombre_comercial'] ?? '',
                'numero_expediente' => $data['numero_expediente'] ?? null,
                'numero_licencia' => $data['numero_licencia'] ?? null,
                'numero_recibo_pago' => $data['numero_recibo_pago'] ?? '',
                'observaciones' => $data['observaciones'] ?? '',
                'resolucion_numero' => $data['resolucion_numero'] ?? '',
                'se_puede_publicar' => (bool) ($data['se_puede_publicar'] ?? false),
                'tipo_licencia_id' => $data['tipo_licencia_id'] ?? null,
                'tipo_licencia_nombre' => $data['tipo_licencia_nombre'] ?? null,
                'titular_id' => $data['titular_id'] ?? null,
                'titular_nombre' => $data['titular_nombre'] ?? null,
                'titular_ruc' => $data['titular_ruc'] ?? null,
                'usuario_id' => $data['usuario_id'] ?? null,
                'zonificacion_id' => $data['zonificacion_id'] ?? null,
                'zonificacion_nombre' => $data['zonificacion_nombre'] ?? null,
            ];
        }

        $model = LicenciaFuncionamiento::find($id);
        if (! $model instanceof LicenciaFuncionamiento) {
            return [];
        }

        $expediente = is_numeric($model->expediente_id) ? DB::table('expedientes')->find((int) $model->expediente_id) : null;
        $titular = is_numeric($model->titular_id) ? DB::table('personas')->find((int) $model->titular_id) : null;
        $conductor = is_numeric($model->conductor_id) ? DB::table('personas')->find((int) $model->conductor_id) : null;
        $tipoLicencia = is_numeric($model->tipo_licencia_id) ? DB::table('tipos_licencia')->find((int) $model->tipo_licencia_id) : null;
        $nivelRiesgo = is_numeric($model->nivel_riesgo_id) ? DB::table('niveles_riesgo')->find((int) $model->nivel_riesgo_id) : null;
        $zonificacion = is_numeric($model->zonificacion_id) ? DB::table('zonificaciones')->find((int) $model->zonificacion_id) : null;

        $titularObj = is_object($titular) ? (object) $titular : null;
        $conductorObj = is_object($conductor) ? (object) $conductor : null;
        $expedienteObj = is_object($expediente) ? (object) $expediente : null;
        $tipoLicenciaObj = is_object($tipoLicencia) ? (object) $tipoLicencia : null;
        $nivelRiesgoObj = is_object($nivelRiesgo) ? (object) $nivelRiesgo : null;
        $zonificacionObj = is_object($zonificacion) ? (object) $zonificacion : null;

        $titularNombre = $titularObj
            ? trim((string) ($titularObj->apellido_paterno ?? '').' '.(string) ($titularObj->apellido_materno ?? '').' '.(string) ($titularObj->nombres ?? ''))
            : null;

        $conductorNombre = $conductorObj
            ? trim((string) ($conductorObj->apellido_paterno ?? '').' '.(string) ($conductorObj->apellido_materno ?? '').' '.(string) ($conductorObj->nombres ?? ''))
            : null;

        return [
            'actividad' => (string) ($model->actividad ?? ''),
            'area' => number_format((float) ($model->area ?? 0), 1, '.', ''),
            'conductor_id' => $model->conductor_id,
            'conductor_nombre' => $conductorNombre,
            'direccion' => (string) ($model->direccion ?? ''),
            'es_vigencia_indeterminada' => (bool) $model->es_vigencia_indeterminada,
            'esta_activo' => true,
            'expediente_id' => $model->expediente_id,
            'fecha_digitacion' => $model->fecha_digitacion ? Carbon::parse($model->fecha_digitacion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'fecha_emision' => $model->fecha_emision ? Carbon::parse($model->fecha_emision)->toDateString() : null,
            'fecha_fin_vigencia' => $model->fecha_fin_vigencia ? Carbon::parse($model->fecha_fin_vigencia)->toDateString() : null,
            'fecha_inicio_vigencia' => $model->fecha_inicio_vigencia ? Carbon::parse($model->fecha_inicio_vigencia)->toDateString() : null,
            'fecha_notificacion' => $model->fecha_notificacion ? Carbon::parse($model->fecha_notificacion)->toDateString() : null,
            'fecha_recepcion' => $expedienteObj && isset($expedienteObj->fecha_recepcion) ? Carbon::parse($expedienteObj->fecha_recepcion)->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP') : null,
            'hora_desde' => (int) ($model->hora_desde ?? 0),
            'hora_hasta' => (int) ($model->hora_hasta ?? 0),
            'id' => (int) $model->id,
            'licencia_principal_id' => $model->licencia_principal_id,
            'nivel_riesgo_id' => $model->nivel_riesgo_id,
            'nivel_riesgo_nombre' => $nivelRiesgoObj->nombre ?? null,
            'nombre_comercial' => (string) ($model->nombre_comercial ?? ''),
            'numero_expediente' => $expedienteObj->numero_expediente ?? null,
            'numero_licencia' => $model->numero_licencia,
            'numero_recibo_pago' => (string) ($model->numero_recibo_pago ?? ''),
            'observaciones' => (string) ($model->observaciones ?? ''),
            'resolucion_numero' => (string) ($model->resolucion_numero ?? ''),
            'se_puede_publicar' => (bool) $model->se_puede_publicar,
            'tipo_licencia_id' => $model->tipo_licencia_id,
            'tipo_licencia_nombre' => $tipoLicenciaObj->nombre ?? null,
            'titular_id' => $model->titular_id,
            'titular_nombre' => $titularNombre,
            'titular_ruc' => null,
            'usuario_id' => $model->usuario_id,
            'zonificacion_id' => $model->zonificacion_id,
            'zonificacion_nombre' => $zonificacionObj->nombre ?? null,
        ];
    }
}
