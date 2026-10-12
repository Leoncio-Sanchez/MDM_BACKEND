<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\ItseEstado;
use App\Modules\ITSE\Infrastructure\Persistence\Models\ItseEstadoModel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class EloquentItseEstadoRepository implements ItseEstadoRepositoryInterface
{
    public function crear(ItseEstado $estado): ItseEstado
    {
        // Columnas del esquema Django (las mismas que lee listarPorItse)
        $model = ItseEstadoModel::create([
            'itse_id' => $estado->itseId,
            'estado_id' => $estado->estadoId,
            'fecha_estado' => $estado->fechaEstado->format('Y-m-d'),
            'documento' => $estado->documento,
            'observaciones' => $estado->observaciones,
            'usuario_id' => $estado->usuarioId,
            'fecha_digitacion' => $estado->fechaDigitacion,
        ]);

        return new ItseEstado(
            id: $model->id,
            fechaEstado: CarbonImmutable::parse($model->fecha_estado),
            documento: (string) $model->documento,
            observaciones: (string) $model->observaciones,
            fechaDigitacion: CarbonImmutable::parse($model->fecha_digitacion),
            estadoId: (int) $model->estado_id,
            itseId: $model->itse_id,
            usuarioId: $model->usuario_id,
        );
    }

    public function tieneEstadoInactivo(int $itseId): bool
    {
        return DB::table('itse_estados as ie')
            ->join('estados as est', 'ie.estado_id', '=', 'est.id')
            ->where('ie.itse_id', $itseId)
            ->where('est.esta_activo', false)
            ->exists();
    }

    /**
     * Lista historial de estados usando el mismo SQL que Django (_SQL_LISTAR_ESTADOS_ITSE).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorItse(int $itseId): array
    {
        $sql = <<<'SQL'
SELECT
    ie.id,
    ie.itse_id,
    ie.estado_id,
    ie.fecha_estado,
    ie.documento,
    ie.observaciones,
    ie.usuario_id,
    ie.fecha_digitacion,
    est.nombre  AS estado_nombre,
    est.es_para_lf,
    est.es_para_itse,
    est.esta_activo
FROM itse_estados ie
LEFT JOIN estados est
    ON ie.estado_id = est.id
WHERE ie.itse_id = ?
ORDER BY ie.fecha_digitacion DESC
SQL;

        $results = DB::select($sql, [$itseId]);

        return array_map(function ($row) {
            $arr = (array) $row;
            $arr['es_para_lf']   = (bool) ($arr['es_para_lf'] ?? false);
            $arr['es_para_itse'] = (bool) ($arr['es_para_itse'] ?? false);
            $arr['esta_activo']  = (bool) ($arr['esta_activo'] ?? false);
            if (isset($arr['fecha_digitacion'])) {
                $arr['fecha_digitacion'] = \Carbon\CarbonImmutable::parse($arr['fecha_digitacion'])->toISOString();
            }
            return $arr;
        }, $results);
    }
}
