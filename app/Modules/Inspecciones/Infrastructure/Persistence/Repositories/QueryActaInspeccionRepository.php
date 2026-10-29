<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Infrastructure\Persistence\Repositories;

use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use App\Modules\Inspecciones\Domain\Contracts\ActaInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\ActaDuplicada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosActa;
use App\Modules\Inspecciones\Domain\ValueObjects\ResultadoActa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final readonly class QueryActaInspeccionRepository implements ActaInspeccionRepositoryInterface
{
    public function __construct(private ArchivoManagerInterface $archivos) {}

    public function deExpediente(int $expedienteId, bool $soloVigentes = false): array
    {
        return $this->consultar('ac.expediente_id = ?'.($soloVigentes ? ' AND ac.fecha_anulacion IS NULL' : ''), [$expedienteId]);
    }

    public function buscar(int $actaId): ?array
    {
        $acta = $this->consultar('ac.id = ?', [$actaId])[0] ?? null;
        if ($acta === null) {
            return null;
        }

        $acta['fotos'] = DB::table('inspecciones_actas_fotos')
            ->where('acta_id', $actaId)
            ->orderBy('id')
            ->get(['id', 'tipo', 'nombre_original', 'extension', 'tamanio_bytes'])
            ->map(static fn ($foto): array => [
                'id' => (int) $foto->id,
                'tipo' => (string) $foto->tipo,
                'nombre_original' => (string) $foto->nombre_original,
                'extension' => (string) $foto->extension,
                'tamanio_bytes' => (int) $foto->tamanio_bytes,
            ])
            ->all();

        return $acta;
    }

    public function existeNumero(string $numeroActa): bool
    {
        return DB::table('inspecciones_actas')
            ->whereNull('fecha_anulacion')
            ->whereRaw('UPPER(numero_acta) = ?', [mb_strtoupper($numeroActa)])
            ->exists();
    }

    public function anular(int $actaId, string $motivo, ?string $documento, int $usuarioId): bool
    {
        // La condición evita que dos anulaciones simultáneas se pisen la constancia
        return DB::table('inspecciones_actas')
            ->where('id', $actaId)
            ->whereNull('fecha_anulacion')
            ->update([
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $motivo,
                'documento_anulacion' => $documento,
                'anulado_por' => $usuarioId,
            ]) === 1;
    }

    public function registrar(
        int $asignacionId,
        int $expedienteId,
        int $inspectorId,
        int $visita,
        DatosActa $datos,
        ResultadoActa $resultado,
        ?string $fechaLimiteSubsanacion,
        array $fotos,
    ): int {
        $guardados = [];

        try {
            return DB::transaction(function () use ($asignacionId, $expedienteId, $inspectorId, $visita, $datos, $resultado, $fechaLimiteSubsanacion, $fotos, &$guardados): int {
                $actaId = (int) DB::table('inspecciones_actas')->insertGetId(array_merge([
                    'expediente_id' => $expedienteId,
                    'asignacion_id' => $asignacionId,
                    'inspector_id' => $inspectorId,
                    'numero_acta' => $datos->numeroActa,
                    'visita' => $visita,
                    'fecha_visita' => $datos->fechaVisita->format('Y-m-d H:i:sP'),
                    'area_m2' => $datos->areaM2,
                    'aforo' => $datos->aforo,
                    'observaciones' => $datos->observaciones,
                    'resultado' => $resultado->value,
                    'fecha_limite_subsanacion' => $fechaLimiteSubsanacion,
                    'fecha_registro' => now(),
                ], array_map(static fn ($estado) => $estado->value, $datos->checklist)));

                // Quiénes acompañaron la visita, aunque después cambie el equipo de la asignación
                DB::insert(<<<'SQL'
                    INSERT INTO inspecciones_actas_acompanantes (acta_id, inspector_id)
                    SELECT ?, inspector_id FROM inspecciones_asignaciones_acompanantes
                    WHERE asignacion_id = ? AND fecha_retiro IS NULL
                    SQL, [$actaId, $asignacionId]);

                foreach ($fotos as $foto) {
                    $meta = $this->archivos->guardar($foto['archivo'], "inspecciones/actas/{$actaId}");
                    $guardados[] = $meta['ruta_archivo'];
                    DB::table('inspecciones_actas_fotos')->insert([
                        'acta_id' => $actaId,
                        'tipo' => $foto['tipo'],
                        'uuid' => $meta['uuid'],
                        'nombre_original' => mb_substr($meta['nombre_original'], 0, 255),
                        'ruta_archivo' => $meta['ruta_archivo'],
                        'extension' => $meta['extension'],
                        'tamanio_bytes' => $meta['tamanio_bytes'],
                        'fecha_registro' => now(),
                    ]);
                }

                return $actaId;
            });
        } catch (\Throwable $e) {
            // La transacción ya deshizo los registros: quitar también los archivos escritos
            foreach ($guardados as $ruta) {
                $this->archivos->eliminar($ruta);
            }
            if ($e instanceof QueryException && ($e->errorInfo[0] ?? null) === '23505') {
                // Dos envíos simultáneos: el número de acta o la visita ya se registraron
                throw str_contains($e->getMessage(), 'numero_unico')
                    ? ActaDuplicada::numero($datos->numeroActa)
                    : ActaDuplicada::visita((int) DB::table('expedientes')->where('id', $expedienteId)->value('numero_expediente'));
            }
            throw $e;
        }
    }

    public function leerFoto(int $actaId, int $fotoId): ?array
    {
        $foto = DB::table('inspecciones_actas_fotos')->where('acta_id', $actaId)->where('id', $fotoId)->first();
        if ($foto === null || ! $this->archivos->existe((string) $foto->ruta_archivo)) {
            return null;
        }

        return [
            'foto' => ['nombre_original' => (string) $foto->nombre_original, 'extension' => (string) $foto->extension],
            'contenido' => $this->archivos->leer((string) $foto->ruta_archivo),
        ];
    }

    public function inspectorPuedeVer(int $actaId, int $inspectorId): bool
    {
        return DB::table('inspecciones_actas as ac')
            ->where('ac.id', $actaId)
            ->where(function ($q) use ($inspectorId): void {
                $q->where('ac.inspector_id', $inspectorId)
                    ->orWhereExists(function ($sub) use ($inspectorId): void {
                        $sub->selectRaw('1')->from('inspecciones_actas_acompanantes as aca')
                            ->whereColumn('aca.acta_id', 'ac.id')
                            ->where('aca.inspector_id', $inspectorId);
                    })
                    ->orWhereExists(function ($sub) use ($inspectorId): void {
                        // Responsable o acompañante de la inspección vigente del expediente
                        $sub->selectRaw('1')->from('inspecciones_asignaciones as a')
                            ->whereColumn('a.expediente_id', 'ac.expediente_id')
                            ->whereNull('a.fecha_reasignacion')
                            ->where(function ($equipo) use ($inspectorId): void {
                                $equipo->where('a.inspector_id', $inspectorId)
                                    ->orWhereExists(function ($acomp) use ($inspectorId): void {
                                        $acomp->selectRaw('1')->from('inspecciones_asignaciones_acompanantes as aa')
                                            ->whereColumn('aa.asignacion_id', 'a.id')
                                            ->where('aa.inspector_id', $inspectorId)
                                            ->whereNull('aa.fecha_retiro');
                                    });
                            });
                    });
            })
            ->exists();
    }

    /**
     * @param  array<int, mixed>  $parametros
     * @return array<int, array<string, mixed>>
     */
    private function consultar(string $condicion, array $parametros): array
    {
        $filas = DB::select(<<<SQL
            SELECT
                ac.id, ac.expediente_id, e.numero_expediente, ac.asignacion_id, ac.inspector_id,
                COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.first_name, u.last_name)), ''), u.username) AS inspector_nombre,
                ac.numero_acta, ac.visita, ac.fecha_visita, ac.area_m2, ac.aforo,
                ac.extintores, ac.senalizacion, ac.luces_emergencia, ac.tableros_electricos,
                ac.observaciones, ac.resultado, ac.fecha_limite_subsanacion, ac.fecha_registro,
                ac.fecha_anulacion IS NOT NULL AS anulada,
                ac.fecha_anulacion, ac.motivo_anulacion, ac.documento_anulacion,
                COALESCE(NULLIF(TRIM(CONCAT_WS(' ', un.first_name, un.last_name)), ''), un.username) AS anulado_por,
                COALESCE((
                    SELECT json_agg(json_build_object(
                        'id', ux.id,
                        'nombre', COALESCE(NULLIF(TRIM(CONCAT_WS(' ', ux.first_name, ux.last_name)), ''), ux.username)
                    ) ORDER BY ux.id)
                    FROM inspecciones_actas_acompanantes aca JOIN auth_user ux ON ux.id = aca.inspector_id
                    WHERE aca.acta_id = ac.id
                ), '[]') AS acompanantes
            FROM inspecciones_actas ac
            JOIN expedientes e ON e.id = ac.expediente_id
            JOIN auth_user u ON u.id = ac.inspector_id
            LEFT JOIN auth_user un ON un.id = ac.anulado_por
            WHERE {$condicion}
            ORDER BY ac.visita, ac.fecha_registro
            SQL, $parametros);

        return array_map(static function ($fila): array {
            $acta = (array) $fila;
            $acta['area_m2'] = (float) $acta['area_m2'];
            $acta['aforo'] = (int) $acta['aforo'];
            $acta['visita'] = (int) $acta['visita'];
            $acta['anulada'] = (bool) $acta['anulada'];
            $acta['acompanantes'] = json_decode((string) $acta['acompanantes'], true, flags: JSON_THROW_ON_ERROR);

            return $acta;
        }, $filas);
    }
}
