<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Infrastructure\Persistence\Repositories;

use App\Modules\Reportes\Domain\Contracts\ReportesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\BajaRegistrada;
use App\Modules\Reportes\Domain\ValueObjects\Improcedencia;
use App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron;
use App\Modules\Reportes\Domain\ValueObjects\TipoAutorizacion;
use Illuminate\Support\Facades\DB;

final readonly class QueryReportesRepository implements ReportesRepositoryInterface
{
    public function padronLicencias(string $desde, string $hasta): array
    {
        // Días hábiles con la función del Corte 2 (fn_diferencia_dias_habiles); la recepción
        // se pasa a fecha de Perú porque se guarda en UTC.
        $nombre = self::nombrePersona('p');
        $documento = self::documentoPersona('p');
        $filas = DB::select(<<<SQL
            SELECT
                lf.numero_licencia,
                e.numero_expediente,
                lf.nombre_comercial,
                {$nombre} AS titular,
                {$documento} AS documento_titular,
                COALESCE((
                    SELECT STRING_AGG(g.nombre, ', ' ORDER BY g.nombre)
                    FROM licencias_funcionamiento_giros lg
                    JOIN giros g ON g.id = lg.giro_id
                    WHERE lg.licencia_funcionamiento_id = lf.id
                ), '') AS giros,
                z.nombre AS zonificacion,
                nr.nombre AS nivel_riesgo,
                tl.nombre AS tipo_licencia,
                (e.fecha_recepcion AT TIME ZONE 'America/Lima')::DATE AS fecha_recepcion,
                lf.fecha_emision,
                fn_diferencia_dias_habiles((e.fecha_recepcion AT TIME ZONE 'America/Lima')::DATE, lf.fecha_emision) AS dias_habiles,
                tpt.plazo_atencion_dias
            FROM licencias_funcionamiento lf
            JOIN expedientes e ON e.id = lf.expediente_id
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            JOIN personas p ON p.id = lf.titular_id
            JOIN zonificaciones z ON z.id = lf.zonificacion_id
            JOIN niveles_riesgo nr ON nr.id = lf.nivel_riesgo_id
            JOIN tipos_licencia tl ON tl.id = lf.tipo_licencia_id
            WHERE lf.fecha_emision BETWEEN ? AND ?
            ORDER BY lf.numero_licencia
            SQL, [$desde, $hasta]);

        return array_values(array_map(fn (\stdClass $f) => new LicenciaPadron(
            numeroLicencia: (int) $f->numero_licencia,
            numeroExpediente: (int) $f->numero_expediente,
            nombreComercial: (string) $f->nombre_comercial,
            titular: (string) $f->titular,
            documentoTitular: (string) $f->documento_titular,
            giros: (string) $f->giros,
            zonificacion: (string) $f->zonificacion,
            nivelRiesgo: (string) $f->nivel_riesgo,
            tipoLicencia: (string) $f->tipo_licencia,
            fechaRecepcion: (string) $f->fecha_recepcion,
            fechaEmision: (string) $f->fecha_emision,
            diasHabiles: (int) $f->dias_habiles,
            plazoDias: (int) $f->plazo_atencion_dias,
        ), $filas));
    }

    public function nivelesRiesgo(): array
    {
        return array_values(DB::table('niveles_riesgo')->orderBy('id')->pluck('nombre')->map(fn ($n) => (string) $n)->all());
    }

    public function bajas(TipoAutorizacion $tipo, string $desde, string $hasta): array
    {
        // Misma consulta para licencias e ITSE: cambian la tabla, su historial de estados y la columna del número
        [$tabla, $historial, $fk, $numero, $emision, $aplica] = match ($tipo) {
            TipoAutorizacion::Licencia => ['licencias_funcionamiento', 'licencias_funcionamiento_estados', 'licencia_funcionamiento_id', 'numero_licencia', 'fecha_emision', 'es_para_lf'],
            TipoAutorizacion::Itse => ['itse', 'itse_estados', 'itse_id', 'numero_itse', 'fecha_expedicion', 'es_para_itse'],
        };
        $nombre = self::nombrePersona('p');
        $documento = self::documentoPersona('p');
        $usuario = self::nombreUsuario('u');

        $filas = DB::select(<<<SQL
            SELECT
                h.fecha_estado,
                es.nombre AS estado,
                h.documento,
                h.observaciones AS motivo,
                t.{$numero} AS numero,
                e.numero_expediente,
                t.nombre_comercial,
                {$nombre} AS titular,
                {$documento} AS documento_titular,
                t.direccion,
                t.{$emision} AS fecha_emision,
                {$usuario} AS registrado_por,
                TO_CHAR(h.fecha_digitacion AT TIME ZONE 'America/Lima', 'YYYY-MM-DD HH24:MI') AS fecha_registro
            FROM {$historial} h
            JOIN estados es ON es.id = h.estado_id AND es.esta_activo = FALSE AND es.{$aplica} = TRUE
            JOIN {$tabla} t ON t.id = h.{$fk}
            JOIN expedientes e ON e.id = t.expediente_id
            JOIN personas p ON p.id = t.titular_id
            JOIN auth_user u ON u.id = h.usuario_id
            WHERE h.fecha_estado BETWEEN ? AND ?
            ORDER BY h.fecha_estado, h.id
            SQL, [$desde, $hasta]);

        return array_values(array_map(fn (\stdClass $f) => new BajaRegistrada(
            fechaEstado: (string) $f->fecha_estado,
            estado: (string) $f->estado,
            documento: (string) $f->documento,
            motivo: (string) $f->motivo,
            numero: (int) $f->numero,
            numeroExpediente: (int) $f->numero_expediente,
            nombreComercial: (string) $f->nombre_comercial,
            titular: (string) $f->titular,
            documentoTitular: (string) $f->documento_titular,
            direccion: (string) $f->direccion,
            fechaEmision: (string) $f->fecha_emision,
            registradoPor: (string) $f->registrado_por,
            fechaRegistro: (string) $f->fecha_registro,
        ), $filas));
    }

    public function estadosDeBaja(TipoAutorizacion $tipo): array
    {
        return array_values(DB::table('estados')
            ->where('esta_activo', false)
            ->where($tipo === TipoAutorizacion::Licencia ? 'es_para_lf' : 'es_para_itse', true)
            ->orderBy('nombre')
            ->pluck('nombre')
            ->map(fn ($n) => (string) $n)
            ->all());
    }

    public function improcedencias(TipoAutorizacion $tipo, string $desde, string $hasta): array
    {
        $nombre = self::nombrePersona('p');
        $documento = self::documentoPersona('p');
        $usuario = self::nombreUsuario('u');

        $filas = DB::select(<<<SQL
            SELECT
                ai.fecha_rechazo,
                ai.documento,
                ai.observaciones AS motivo,
                e.numero_expediente,
                tpt.nombre AS procedimiento,
                {$nombre} AS solicitante,
                {$documento} AS documento_solicitante,
                (e.fecha_recepcion AT TIME ZONE 'America/Lima')::DATE AS fecha_recepcion,
                {$usuario} AS registrado_por,
                TO_CHAR(ai.fecha_digitacion AT TIME ZONE 'America/Lima', 'YYYY-MM-DD HH24:MI') AS fecha_registro
            FROM autorizaciones_improcedentes ai
            JOIN expedientes e ON e.id = ai.expediente_id
            JOIN tipos_procedimiento_tupa tpt ON tpt.id = e.tipo_procedimiento_tupa_id
            JOIN personas p ON p.id = e.solicitante_id
            JOIN auth_user u ON u.id = ai.usuario_id
            WHERE ai.tipo_autorizacion = ? AND ai.fecha_rechazo BETWEEN ? AND ?
            ORDER BY ai.fecha_rechazo, ai.id
            SQL, [$tipo->value, $desde, $hasta]);

        return array_values(array_map(fn (\stdClass $f) => new Improcedencia(
            fechaRechazo: (string) $f->fecha_rechazo,
            documento: (string) $f->documento,
            motivo: (string) $f->motivo,
            numeroExpediente: (int) $f->numero_expediente,
            procedimiento: (string) $f->procedimiento,
            solicitante: (string) $f->solicitante,
            documentoSolicitante: (string) $f->documento_solicitante,
            fechaRecepcion: (string) $f->fecha_recepcion,
            registradoPor: (string) $f->registrado_por,
            fechaRegistro: (string) $f->fecha_registro,
        ), $filas));
    }

    /** Razón social (jurídica) o nombres y apellidos (natural) */
    private static function nombrePersona(string $p): string
    {
        return "CASE WHEN {$p}.tipo_persona = 'J' THEN {$p}.nombres
                ELSE TRIM(CONCAT_WS(' ', {$p}.nombres, {$p}.apellido_paterno, {$p}.apellido_materno)) END";
    }

    /** Un solo documento por persona: RUC primero, luego DNI, luego cualquier otro */
    private static function documentoPersona(string $p): string
    {
        return "COALESCE((
                SELECT pd.numero_documento
                FROM personas_documentos pd
                JOIN tipos_documento_identidad tdi ON tdi.id = pd.tipo_documento_identidad_id
                WHERE pd.persona_id = {$p}.id
                ORDER BY (tdi.codigo = '06') DESC, (tdi.codigo = '01') DESC, pd.id
                LIMIT 1
            ), '')";
    }

    /** Nombre del usuario que registró; su login si no tiene nombre cargado */
    private static function nombreUsuario(string $u): string
    {
        return "COALESCE(NULLIF(TRIM(CONCAT_WS(' ', {$u}.first_name, {$u}.last_name)), ''), {$u}.username)";
    }
}
