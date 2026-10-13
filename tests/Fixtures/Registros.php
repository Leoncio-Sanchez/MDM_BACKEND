<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Support\Str;

/**
 * Filas válidas para el esquema real (database/sql/esquema_produccion.sql).
 *
 * Casi todas las columnas son NOT NULL y sin valor por defecto (herencia de Django), así que
 * cada método completa lo obligatorio y el test solo indica lo que le importa:
 *
 *   DB::table('personas')->insert(Registros::persona(['id' => 1, 'nombres' => 'TITULAR']));
 *
 * Las claves foráneas apuntan por defecto al id 1: el test debe crear esos registros antes.
 */
final class Registros
{
    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function unidadOrganica(array $d = []): array
    {
        return array_merge(['codigo' => 'UO-' . Str::random(6), 'nombre' => 'UNIDAD', 'sigla' => '', 'esta_activo' => true], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function tipoLicencia(array $d = []): array
    {
        return array_merge(['codigo' => 'TL-' . Str::random(4), 'nombre' => 'TIPO', 'esta_activo' => true], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function nivelRiesgo(array $d = []): array
    {
        return array_merge(['codigo' => 'NR' . Str::random(4), 'nombre' => 'NIVEL', 'esta_activo' => true], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function zonificacion(array $d = []): array
    {
        return array_merge([
            'codigo' => 'Z-' . Str::random(6), 'nombre' => 'ZONA', 'esta_activo' => true,
            'fecha_digitacion' => now(), 'usuario_id' => 1,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function estado(array $d = []): array
    {
        return array_merge(['nombre' => 'ESTADO', 'es_para_lf' => true, 'es_para_itse' => true, 'esta_activo' => true], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function giro(array $d = []): array
    {
        return array_merge(['nombre' => 'GIRO', 'esta_activo' => true, 'fecha_digitacion' => now(), 'usuario_id' => 1], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function tipoDocumentoIdentidad(array $d = []): array
    {
        return array_merge([
            'codigo' => Str::random(2), 'nombre' => 'DOC', 'esta_activo' => true,
            'es_para_natural' => true, 'es_para_juridica' => false,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function tipoProcedimientoTupa(array $d = []): array
    {
        return array_merge([
            'codigo' => 'PE-' . Str::random(6), 'nombre' => 'PROCEDIMIENTO', 'plazo_atencion_dias' => 7,
            'dias_alerta_vencimiento' => 2, 'esta_activo' => true, 'requiere_lf' => false, 'requiere_itse' => false,
            'fecha_digitacion' => now(), 'usuario_id' => 1, 'unidad_organica_id' => 1,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function persona(array $d = []): array
    {
        return array_merge([
            'tipo_persona' => 'N', 'sexo' => 'M', 'nombres' => 'PERSONA', 'direccion' => '', 'distrito' => '',
            'provincia' => '', 'departamento' => '', 'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
            'user_id' => 1,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function expediente(array $d = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'numero_expediente' => random_int(1, 999999),
            'fecha_recepcion' => now(), 'fecha_vencimiento' => now()->addDays(7)->toDateString(),
            'fecha_alerta' => now()->addDays(5)->toDateString(), 'fecha_digitacion' => now(),
            'usuario_id' => 1, 'solicitante_id' => 1, 'tipo_procedimiento_tupa_id' => 1,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function itse(array $d = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'tipo_itse_id' => 1, 'numero_itse' => random_int(1, 999999),
            'fecha_expedicion' => now()->toDateString(), 'fecha_solicitud_renovacion' => now()->toDateString(),
            'fecha_caducidad' => now()->addYears(2)->toDateString(), 'nombre_comercial' => 'COMERCIO',
            'direccion' => 'DIRECCION', 'resolucion_numero' => 'RES-1', 'area' => 10, 'numero_recibo_pago' => 'REC-' . Str::random(6),
            'observaciones' => '', 'se_puede_publicar' => false, 'capacidad_aforo' => 0, 'fecha_digitacion' => now(),
            'expediente_id' => 1, 'nivel_riesgo_id' => 1, 'conductor_id' => 1, 'titular_id' => 1, 'usuario_id' => 1,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function licencia(array $d = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'numero_licencia' => random_int(1, 999999), 'fecha_emision' => now()->toDateString(),
            'nombre_comercial' => 'COMERCIO', 'es_vigencia_indeterminada' => true, 'actividad' => 'ACTIVIDAD',
            'direccion' => 'DIRECCION', 'hora_desde' => 8, 'hora_hasta' => 20, 'resolucion_numero' => 'RES-1', 'area' => 10,
            'numero_recibo_pago' => 'REC-' . Str::random(6), 'se_puede_publicar' => false, 'fecha_digitacion' => now(),
            'expediente_id' => 1, 'usuario_id' => 1, 'nivel_riesgo_id' => 1, 'conductor_id' => 1, 'titular_id' => 1,
            'tipo_licencia_id' => 1, 'zonificacion_id' => 1,
        ], $d);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    public static function autorizacionImprocedente(array $d = []): array
    {
        return array_merge([
            'tipo_autorizacion' => 'LF', 'fecha_rechazo' => now()->toDateString(), 'documento' => 'DOC',
            'observaciones' => '', 'fecha_digitacion' => now(), 'usuario_id' => 1, 'expediente_id' => 1,
        ], $d);
    }
}
