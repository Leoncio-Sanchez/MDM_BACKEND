<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Infrastructure\Persistence\Repositories;

use App\Modules\ConsultaPublica\Domain\Contracts\ConsultaPublicaRepositoryInterface;
use App\Modules\ConsultaPublica\Domain\ValueObjects\AvanceInspeccion;
use App\Modules\ConsultaPublica\Domain\ValueObjects\EstadoTramite;
use App\Modules\ConsultaPublica\Domain\ValueObjects\TipoTramite;
use App\Modules\ConsultaPublica\Domain\ValueObjects\TramitePublico;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de solo lectura. El filtro por documento va en el SQL:
 * nunca se cargan datos de trámites que no son de quien consulta.
 */
final readonly class QueryConsultaPublicaRepository implements ConsultaPublicaRepositoryInterface
{
    /** @return list<TramitePublico> */
    public function expedientes(?int $numero, string $documento): array
    {
        $filas = DB::table('expedientes as e')
            ->join('tipos_procedimiento_tupa as tpt', 'tpt.id', '=', 'e.tipo_procedimiento_tupa_id')
            ->when($numero !== null, fn (Builder $q) => $q->where('e.numero_expediente', $numero))
            ->where(fn (Builder $q) => $q
                ->whereIn('e.solicitante_id', $this->personasConDocumento($documento))
                ->orWhereIn('e.representante_id', $this->personasConDocumento($documento)))
            ->select([
                'e.numero_expediente', 'e.fecha_recepcion', 'e.fecha_vencimiento',
                'tpt.nombre as procedimiento', 'tpt.requiere_lf', 'tpt.requiere_itse',
            ])
            ->selectRaw('EXISTS (SELECT 1 FROM licencias_funcionamiento lf WHERE lf.expediente_id = e.id) AS tiene_lf')
            ->selectRaw('EXISTS (SELECT 1 FROM itse i WHERE i.expediente_id = e.id) AS tiene_itse')
            ->selectRaw("EXISTS (SELECT 1 FROM autorizaciones_improcedentes ai WHERE ai.expediente_id = e.id AND ai.tipo_autorizacion = 'LF') AS lf_improcedente")
            ->selectRaw("EXISTS (SELECT 1 FROM autorizaciones_improcedentes ai WHERE ai.expediente_id = e.id AND ai.tipo_autorizacion = 'ITSE') AS itse_improcedente")
            ->selectRaw('EXISTS (SELECT 1 FROM expedientes_anulaciones ea WHERE ea.expediente_id = e.id) AS anulado')
            // Inspección ITSE: última acta vigente y asignación vigente
            ->selectRaw('(SELECT ac.resultado FROM inspecciones_actas ac WHERE ac.expediente_id = e.id AND ac.fecha_anulacion IS NULL ORDER BY ac.visita DESC LIMIT 1) AS acta_resultado')
            ->selectRaw('(SELECT ac.fecha_limite_subsanacion FROM inspecciones_actas ac WHERE ac.expediente_id = e.id AND ac.fecha_anulacion IS NULL ORDER BY ac.visita DESC LIMIT 1) AS acta_plazo')
            ->selectRaw('EXISTS (SELECT 1 FROM inspecciones_asignaciones a WHERE a.expediente_id = e.id AND a.fecha_reasignacion IS NULL) AS inspeccion_asignada')
            ->selectRaw('(SELECT a.fecha_programada FROM inspecciones_asignaciones a WHERE a.expediente_id = e.id AND a.fecha_reasignacion IS NULL LIMIT 1) AS visita_programada')
            ->orderByDesc('e.fecha_recepcion')
            ->get();

        return array_values($filas->map(fn (object $f) => new TramitePublico(
            tipo: TipoTramite::Expediente,
            numero: (int) $f->numero_expediente,
            estado: EstadoTramite::deExpediente(
                (bool) $f->requiere_lf, (bool) $f->requiere_itse,
                (bool) $f->tiene_lf, (bool) $f->tiene_itse,
                (bool) $f->lf_improcedente, (bool) $f->itse_improcedente,
                (bool) $f->anulado,
            ),
            procedimiento: (string) $f->procedimiento,
            fechaEmision: CarbonImmutable::parse($f->fecha_recepcion),
            fechaVencimiento: CarbonImmutable::parse($f->fecha_vencimiento),
            inspeccion: AvanceInspeccion::desde(
                itsePendiente: (bool) $f->requiere_itse && ! (bool) $f->tiene_itse && ! (bool) $f->itse_improcedente && ! (bool) $f->anulado,
                resultadoUltimaActa: $f->acta_resultado === null ? null : (string) $f->acta_resultado,
                plazoSubsanacion: $f->acta_plazo === null ? null : CarbonImmutable::parse($f->acta_plazo),
                asignada: (bool) $f->inspeccion_asignada,
                visitaProgramada: $f->visita_programada === null ? null : CarbonImmutable::parse($f->visita_programada),
            ),
        ))->all());
    }

    /** @return list<TramitePublico> */
    public function licencias(?int $numero, string $documento): array
    {
        $filas = DB::table('licencias_funcionamiento as lf')
            ->when($numero !== null, fn (Builder $q) => $q->where('lf.numero_licencia', $numero))
            ->where(fn (Builder $q) => $q
                ->whereIn('lf.titular_id', $this->personasConDocumento($documento))
                ->orWhereIn('lf.conductor_id', $this->personasConDocumento($documento)))
            ->select(['lf.id', 'lf.numero_licencia', 'lf.nombre_comercial', 'lf.fecha_emision', 'lf.es_vigencia_indeterminada', 'lf.fecha_fin_vigencia'])
            ->selectRaw($this->inactivoSql('licencias_funcionamiento_estados', 'licencia_funcionamiento_id', 'lf.id').' AS inactiva')
            ->get();

        return array_values($filas->map(function (object $f): TramitePublico {
            $indeterminada = (bool) $f->es_vigencia_indeterminada;
            $vencimiento = ! $indeterminada && $f->fecha_fin_vigencia !== null ? CarbonImmutable::parse($f->fecha_fin_vigencia) : null;

            return new TramitePublico(
                tipo: TipoTramite::Licencia,
                numero: (int) $f->numero_licencia,
                estado: EstadoTramite::deAutorizacion((bool) $f->inactiva, $vencimiento, CarbonImmutable::today()),
                nombreComercial: (string) $f->nombre_comercial,
                giros: $this->giros('licencias_funcionamiento_giros', 'licencia_funcionamiento_id', (int) $f->id),
                fechaEmision: CarbonImmutable::parse($f->fecha_emision),
                fechaVencimiento: $vencimiento,
                vigenciaIndeterminada: $indeterminada,
            );
        })->all());
    }

    /** @return list<TramitePublico> */
    public function itse(?int $numero, string $documento): array
    {
        $filas = DB::table('itse as i')
            ->when($numero !== null, fn (Builder $q) => $q->where('i.numero_itse', $numero))
            ->where(fn (Builder $q) => $q
                ->whereIn('i.titular_id', $this->personasConDocumento($documento))
                ->orWhereIn('i.conductor_id', $this->personasConDocumento($documento)))
            ->select(['i.id', 'i.numero_itse', 'i.nombre_comercial', 'i.fecha_expedicion', 'i.fecha_caducidad'])
            ->selectRaw($this->inactivoSql('itse_estados', 'itse_id', 'i.id').' AS inactivo')
            ->get();

        return array_values($filas->map(function (object $f): TramitePublico {
            $caducidad = CarbonImmutable::parse($f->fecha_caducidad);

            return new TramitePublico(
                tipo: TipoTramite::Itse,
                numero: (int) $f->numero_itse,
                estado: EstadoTramite::deAutorizacion((bool) $f->inactivo, $caducidad, CarbonImmutable::today()),
                nombreComercial: (string) $f->nombre_comercial,
                giros: $this->giros('itse_giros', 'itse_id', (int) $f->id),
                fechaEmision: CarbonImmutable::parse($f->fecha_expedicion),
                fechaVencimiento: $caducidad,
            );
        })->all());
    }

    /** Subconsulta: personas que tienen ese número de documento (DNI, RUC, CE…). */
    private function personasConDocumento(string $documento): Builder
    {
        return DB::table('personas_documentos')
            ->select('persona_id')
            ->whereRaw('UPPER(numero_documento) = ?', [$documento]);
    }

    /**
     * Inactiva si tiene un estado cuyo catálogo no está activo (clausura, revocación…).
     * Solo recibe nombres fijos del código (literal-string), nunca datos del usuario.
     *
     * @param  literal-string  $tablaEstados
     * @param  literal-string  $columna
     * @param  literal-string  $id
     * @return literal-string
     */
    private function inactivoSql(string $tablaEstados, string $columna, string $id): string
    {
        return "EXISTS (SELECT 1 FROM {$tablaEstados} te JOIN estados est ON est.id = te.estado_id
                 WHERE te.{$columna} = {$id} AND est.esta_activo = FALSE)";
    }

    /** @return list<string> */
    private function giros(string $tabla, string $columna, int $id): array
    {
        $nombres = DB::table($tabla.' as t')
            ->join('giros as g', 'g.id', '=', 't.giro_id')
            ->where('t.'.$columna, $id)
            ->orderBy('g.nombre')
            ->pluck('g.nombre')
            ->map(fn ($n) => (string) $n)
            ->values()
            ->all();

        return array_values($nombres);
    }
}
