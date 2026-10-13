<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

/**
 * Reglas comunes de los datos de una licencia (crear y modificar).
 *
 * Todo campo que lee CrearLicenciaDTO / ModificarLicenciaDTO debe estar aquí:
 * `$request->validated()` descarta lo que no tiene regla, y el DTO lo reemplaza
 * por '' o 0 sin avisar. Los límites coinciden con las columnas de
 * `licencias_funcionamiento`.
 */
trait ReglasDatosLicencia
{
    /** @return array<string, mixed> */
    protected function reglasDatosLicencia(): array
    {
        return [
            'nombre_comercial' => ['required', 'string', 'max:200'],
            'tipo_licencia_id' => ['required', 'integer', 'exists:tipos_licencia,id'],
            'titular_id' => ['required', 'integer', 'exists:personas,id'],
            // Si no se envía, el DTO usa al titular como conductor
            'conductor_id' => ['nullable', 'integer', 'exists:personas,id'],
            'expediente_id' => ['required', 'integer', 'exists:expedientes,id'],
            'nivel_riesgo_id' => ['required', 'integer', 'exists:niveles_riesgo,id'],
            'zonificacion_id' => ['required', 'integer', 'exists:zonificaciones,id'],
            'licencia_principal_id' => ['nullable', 'integer', 'exists:licencias_funcionamiento,id'],
            'es_vigencia_indeterminada' => ['sometimes', 'boolean'],
            'fecha_inicio_vigencia' => ['nullable', 'required_if:es_vigencia_indeterminada,false', 'date'],
            'fecha_fin_vigencia' => ['nullable', 'required_if:es_vigencia_indeterminada,false', 'date', 'after_or_equal:fecha_inicio_vigencia'],
            'actividad' => ['required', 'string', 'max:50'],
            'direccion' => ['required', 'string', 'max:250'],
            // Horario de atención en horas enteras (0-24). hora_hasta puede ser menor que hora_desde (horario nocturno).
            'hora_desde' => ['required', 'integer', 'between:0,24'],
            'hora_hasta' => ['required', 'integer', 'between:0,24'],
            'resolucion_numero' => ['required', 'string', 'max:50'],
            'area' => ['required', 'numeric', 'gt:0', 'max:9999999999999999.99'],
            'numero_recibo_pago' => ['required', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
            'se_puede_publicar' => ['sometimes', 'boolean'],
            'giros' => ['sometimes', 'array'],
            'giros.*' => ['integer', 'distinct', 'exists:giros,id'],
        ];
    }
}
