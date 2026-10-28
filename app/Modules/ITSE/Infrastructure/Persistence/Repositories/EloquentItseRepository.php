<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Infrastructure\Persistence\Models\ItseModel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentItseRepository implements ItseRepositoryInterface
{
    public function crear(Itse $itse): Itse
    {
        $model = ItseModel::create([
            'uuid' => $itse->uuid,
            'numero_itse' => $itse->numeroItse,
            'fecha_expedicion' => $itse->fechaExpedicion->format('Y-m-d'),
            'fecha_solicitud_renovacion' => $itse->fechaSolicitudRenovacion?->format('Y-m-d'),
            'fecha_caducidad' => $itse->fechaCaducidad->format('Y-m-d'),
            'nombre_comercial' => $itse->nombreComercial,
            'direccion' => $itse->direccion,
            'resolucion_numero' => $itse->resolucionNumero,
            'area' => $itse->area,
            'capacidad_aforo' => $itse->capacidadAforo,
            'numero_recibo_pago' => $itse->numeroReciboPago,
            'observaciones' => $itse->observaciones ?? '', // columna NOT NULL en producción
            'se_puede_publicar' => $itse->sePuedePublicar,
            'fecha_notificacion' => $itse->fechaNotificacion?->format('Y-m-d'),
            'fecha_digitacion' => $itse->fechaDigitacion->format("Y-m-d H:i:s"),
            'tipo_itse_id' => $itse->tipoItseId,
            'expediente_id' => $itse->expedienteId,
            'itse_principal_id' => $itse->itsePrincipalId,
            'usuario_id' => $itse->usuarioId,
            'nivel_riesgo_id' => $itse->nivelRiesgoId,
            'conductor_id' => $itse->conductorId,
            'titular_id' => $itse->titularId,
        ]);

        return $this->toEntity($model);
    }

    public function actualizar(Itse $itse): Itse
    {
        $model = ItseModel::findOrFail($itse->id);
        $model->update([
            'numero_itse' => $itse->numeroItse,
            'fecha_expedicion' => $itse->fechaExpedicion->format('Y-m-d'),
            'fecha_solicitud_renovacion' => $itse->fechaSolicitudRenovacion?->format('Y-m-d'),
            'fecha_caducidad' => $itse->fechaCaducidad->format('Y-m-d'),
            'nombre_comercial' => $itse->nombreComercial,
            'direccion' => $itse->direccion,
            'resolucion_numero' => $itse->resolucionNumero,
            'area' => $itse->area,
            'capacidad_aforo' => $itse->capacidadAforo,
            'numero_recibo_pago' => $itse->numeroReciboPago,
            'observaciones' => $itse->observaciones ?? '', // columna NOT NULL en producción
            'se_puede_publicar' => $itse->sePuedePublicar,
            'fecha_notificacion' => $itse->fechaNotificacion?->format('Y-m-d'),
            'tipo_itse_id' => $itse->tipoItseId,
            'nivel_riesgo_id' => $itse->nivelRiesgoId,
            'conductor_id' => $itse->conductorId,
            'titular_id' => $itse->titularId,
        ]);

        return $this->toEntity($model);
    }

    public function buscarPorId(int $id): ?Itse
    {
        $model = ItseModel::find($id);
        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function existeNumero(int $numero, ?int $excluirId = null): bool
    {
        $query = ItseModel::where('numero_itse', $numero);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    public function existeRecibo(string $recibo, ?int $excluirId = null): bool
    {
        $query = ItseModel::where('numero_recibo_pago', $recibo);
        if ($excluirId) {
            $query->where('id', '!=', $excluirId);
        }
        return $query->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array
    {
        $validFilters = [
            'ID', 'NUMERO', 'EXPEDIENTE', 'NOMBRE_COMERCIAL', 'FECHA_EXPEDICION',
            'FECHA_EMISION', 'NOMBRES_TITULAR', 'RUC_TITULAR', 'NOMBRES_CONDUCTOR',
            'DIRECCION', 'RECIBO_PAGO', 'RESOLUCION_NUMERO'
        ];

        $filtro = strtoupper(trim($filtro));
        if (!in_array($filtro, $validFilters, true)) {
            throw new \InvalidArgumentException("Filtro '{$filtro}' no válido.");
        }

        $baseQuery = "
            SELECT
                i.id,
                i.expediente_id,
                i.tipo_itse_id,
                i.numero_itse,
                i.fecha_expedicion,
                i.fecha_solicitud_renovacion,
                i.fecha_caducidad,
                i.titular_id,
                i.conductor_id,
                i.itse_principal_id,
                i.nombre_comercial,
                i.nivel_riesgo_id,
                i.direccion,
                i.resolucion_numero,
                i.area,
                i.numero_recibo_pago,
                i.observaciones,
                i.se_puede_publicar,
                i.capacidad_aforo,
                i.fecha_notificacion,
                i.usuario_id,
                i.fecha_digitacion,
                e.numero_expediente,
                e.fecha_recepcion,
                TRIM(
                    COALESCE(ttitular.apellido_paterno, '') || ' ' ||
                    COALESCE(ttitular.apellido_materno, '') || ' ' ||
                    COALESCE(ttitular.nombres, '')
                ) AS titular_nombre,
                truc.numero_documento AS titular_ruc,
                TRIM(
                    COALESCE(tconductor.apellido_paterno, '') || ' ' ||
                    COALESCE(tconductor.apellido_materno, '') || ' ' ||
                    COALESCE(tconductor.nombres, '')
                ) AS conductor_nombre,
                CASE
                    WHEN titse_inactivos.itse_id IS NULL THEN TRUE
                    ELSE FALSE
                END AS esta_activo,
                CASE i.tipo_itse_id
                    WHEN 1 THEN 'ESTÁNDAR'
                    WHEN 2 THEN 'RENOVACIÓN'
                    ELSE 'DESCONOCIDO'
                END AS tipo_itse_nombre,
                nr.nombre AS nivel_riesgo_nombre
            FROM itse i
            LEFT JOIN expedientes e
                ON i.expediente_id = e.id
            LEFT JOIN personas AS ttitular
                ON i.titular_id = ttitular.id
            LEFT JOIN personas AS tconductor
                ON i.conductor_id = tconductor.id
            LEFT JOIN (
                SELECT
                    pd.id,
                    pd.persona_id,
                    pd.numero_documento
                FROM personas_documentos pd
                INNER JOIN tipos_documento_identidad tdi
                    ON pd.tipo_documento_identidad_id = tdi.id
                WHERE tdi.codigo = '06'
            ) AS truc
                ON i.titular_id = truc.persona_id
            LEFT JOIN (
                SELECT DISTINCT ie.itse_id
                FROM itse_estados ie
                INNER JOIN estados est
                    ON ie.estado_id = est.id
                WHERE est.esta_activo = FALSE
            ) AS titse_inactivos
                ON i.id = titse_inactivos.itse_id
            LEFT JOIN niveles_riesgo nr
                ON i.nivel_riesgo_id = nr.id
        ";

        $whereClause = "";
        $binding = $valor;

        $likeTransformer = function($v) {
            return '%' . str_replace(' ', '%', $v) . '%';
        };

        switch ($filtro) {
            case 'ID':
                $whereClause = "WHERE i.id = ?";
                $binding = (int) $valor;
                break;
            case 'NUMERO':
                $whereClause = "WHERE i.numero_itse = ?";
                $binding = (int) $valor;
                break;
            case 'EXPEDIENTE':
                $whereClause = "WHERE e.numero_expediente = ?";
                $binding = (int) $valor;
                break;
            case 'NOMBRE_COMERCIAL':
                $whereClause = "WHERE i.nombre_comercial ILIKE ?";
                $binding = $likeTransformer($valor);
                break;
            case 'FECHA_EXPEDICION':
            case 'FECHA_EMISION':
                $whereClause = "WHERE i.fecha_expedicion = ?";
                $binding = $valor;
                break;
            case 'NOMBRES_TITULAR':
                $whereClause = "WHERE TRIM(COALESCE(ttitular.apellido_paterno, '') || ' ' || COALESCE(ttitular.apellido_materno, '') || ' ' || COALESCE(ttitular.nombres, '')) ILIKE ?";
                $binding = $likeTransformer($valor);
                break;
            case 'RUC_TITULAR':
                $whereClause = "WHERE truc.numero_documento = ?";
                $binding = $valor;
                break;
            case 'NOMBRES_CONDUCTOR':
                $whereClause = "WHERE TRIM(COALESCE(tconductor.apellido_paterno, '') || ' ' || COALESCE(tconductor.apellido_materno, '') || ' ' || COALESCE(tconductor.nombres, '')) ILIKE ?";
                $binding = $likeTransformer($valor);
                break;
            case 'DIRECCION':
                $whereClause = "WHERE TRIM(i.direccion) ILIKE ?";
                $binding = $likeTransformer($valor);
                break;
            case 'RECIBO_PAGO':
                $whereClause = "WHERE TRIM(i.numero_recibo_pago) ILIKE ?";
                $binding = $likeTransformer($valor);
                break;
            case 'RESOLUCION_NUMERO':
                $whereClause = "WHERE TRIM(i.resolucion_numero) ILIKE ?";
                $binding = $likeTransformer($valor);
                break;
        }

        $query = $baseQuery . " " . $whereClause . " ORDER BY i.numero_itse DESC";
        $results = DB::select($query, [$binding]);

        return array_map(function ($row) {
            $arr = (array) $row;
            if (isset($arr['area'])) {
                $arr['area'] = (float) $arr['area'];
            }
            if (isset($arr['se_puede_publicar'])) {
                $arr['se_puede_publicar'] = (bool) $arr['se_puede_publicar'];
            }
            if (isset($arr['esta_activo'])) {
                $arr['esta_activo'] = (bool) $arr['esta_activo'];
            }
            if (isset($arr['fecha_digitacion'])) {
                $arr['fecha_digitacion'] = \Carbon\CarbonImmutable::parse($arr['fecha_digitacion'])->toISOString();
            }
            if (isset($arr['fecha_recepcion'])) {
                $arr['fecha_recepcion'] = \Carbon\CarbonImmutable::parse($arr['fecha_recepcion'])->toISOString();
            }
            return $arr;
        }, $results);
    }

    public function existePorId(int $id): bool
    {
        return ItseModel::where('id', $id)->exists();
    }

    /**
     * ITSE cuya fecha de caducidad está en el rango, sin inactivar y sin una renovación vigente.
     * Incluye tanto certificados originales como renovaciones (todos vencen).
     *
     * @return array<int, Itse>
     */
    public function listarPorRenovar(string $fechaDesde, string $fechaHasta): array
    {
        $inactivo = fn ($q) => $q->select(DB::raw(1))
            ->from('itse_estados as ie')
            ->join('estados as est', 'est.id', '=', 'ie.estado_id')
            ->where('est.esta_activo', false);

        return ItseModel::query()
            ->whereBetween('fecha_caducidad', [$fechaDesde, $fechaHasta])
            ->whereNotExists(fn ($q) => $inactivo($q)->whereColumn('ie.itse_id', 'itse.id'))
            // Ya renovado: existe otro ITSE que lo tiene como principal y no está inactivo
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('itse as renovacion')
                ->whereColumn('renovacion.itse_principal_id', 'itse.id')
                ->whereNotExists(fn ($r) => $inactivo($r)->whereColumn('ie.itse_id', 'renovacion.id')))
            ->orderBy('fecha_caducidad')
            ->get()
            ->map(fn ($m) => $this->toEntity($m))
            ->values()
            ->all();
    }

    /**
     * Consulta rápida con filtro opcional por numero_itse.
     * Replica ItseConsultaView de Django.
     *
     * @return array<int, Itse>
     */
    public function consultaRapida(?int $numeroItse): array
    {
        $query = ItseModel::query()->orderByDesc('id');

        if ($numeroItse !== null) {
            $query->where('numero_itse', $numeroItse);
        }

        return $query->get()->map(fn ($m) => $this->toEntity($m))->values()->all();
    }

    private function toEntity(ItseModel $model): Itse
    {
        $historialEstados = [];
        $giros = [];
        if ($model->id) {
            $giros = DB::table('itse_giros')
                ->where('itse_id', $model->id)
                ->get()
                ->map(fn ($g) => [
                    'id' => (int) $g->id,
                    'fecha_digitacion' => CarbonImmutable::parse($g->fecha_digitacion)
                        ->setTimezone(new \DateTimeZone('America/Lima'))
                        ->format('Y-m-d\TH:i:s.uP'),
                    'itse' => (int) $g->itse_id,
                    'giro' => (int) $g->giro_id,
                    'usuario' => (int) $g->usuario_id,
                ])
                ->all();

            $historialEstados = DB::table('itse_estados')
                ->where('itse_id', $model->id)
                ->get()
                ->map(fn ($e) => [
                    'id' => (int) $e->id,
                    'fecha_estado' => CarbonImmutable::parse($e->fecha_estado)->format('Y-m-d'),
                    'documento' => (string) $e->documento,
                    'observaciones' => (string) $e->observaciones,
                    'fecha_digitacion' => CarbonImmutable::parse($e->fecha_digitacion)
                        ->setTimezone(new \DateTimeZone('America/Lima'))
                        ->format('Y-m-d\TH:i:s.uP'),
                    'itse' => (int) $e->itse_id,
                    'estado' => (int) $e->estado_id,
                    'usuario' => (int) $e->usuario_id,
                ])
                ->all();
        }

        return new Itse(
            id: $model->id,
            uuid: $model->uuid,
            numeroItse: $model->numero_itse,
            fechaExpedicion: CarbonImmutable::parse($model->fecha_expedicion),
            fechaSolicitudRenovacion: $model->fecha_solicitud_renovacion ? CarbonImmutable::parse($model->fecha_solicitud_renovacion) : null,
            fechaCaducidad: CarbonImmutable::parse($model->fecha_caducidad),
            nombreComercial: $model->nombre_comercial,
            direccion: $model->direccion,
            resolucionNumero: $model->resolucion_numero,
            area: (float) $model->area,
            capacidadAforo: $model->capacidad_aforo,
            numeroReciboPago: $model->numero_recibo_pago,
            observaciones: $model->observaciones,
            sePuedePublicar: (bool) $model->se_puede_publicar,
            fechaNotificacion: $model->fecha_notificacion ? CarbonImmutable::parse($model->fecha_notificacion) : null,
            fechaDigitacion: CarbonImmutable::parse($model->fecha_digitacion),
            tipoItseId: $model->tipo_itse_id,
            expedienteId: $model->expediente_id,
            itsePrincipalId: $model->itse_principal_id,
            usuarioId: $model->usuario_id,
            nivelRiesgoId: $model->nivel_riesgo_id,
            conductorId: $model->conductor_id,
            titularId: $model->titular_id,
            archivos: [],
            historialEstados: $historialEstados,
            giros: $giros,
        );
    }
}
