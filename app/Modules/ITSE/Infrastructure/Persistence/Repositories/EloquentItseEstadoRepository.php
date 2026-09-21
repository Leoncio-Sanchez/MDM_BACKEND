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
        $model = ItseEstadoModel::create([
            'itse_id' => $estado->itseId,
            'estado' => $estado->estadoId,
            'fecha' => $estado->fechaEstado->format('Y-m-d'),
            'observaciones' => $estado->observaciones,
            'usuario_id' => $estado->usuarioId,
        ]);

        return new ItseEstado(
            id: $model->id,
            fechaEstado: CarbonImmutable::parse($model->fecha),
            documento: '',
            observaciones: (string) $model->observaciones,
            fechaDigitacion: CarbonImmutable::now(), // mock
            estadoId: (int) $model->estado,
            itseId: $model->itse_id,
            usuarioId: $model->usuario_id,
        );
    }

    public function tieneEstado(int $itseId, string $estadoNombre): bool
    {
        return ItseEstadoModel::where('itse_id', $itseId)
            ->where('estado', $estadoNombre)
            ->exists();
    }

    public function insertar(int $itseId, string $estado, string $fecha, string $observaciones, int $usuarioId): void
    {
        ItseEstadoModel::create([
            'itse_id' => $itseId,
            'estado' => $estado,
            'fecha' => $fecha,
            'observaciones' => $observaciones,
            'usuario_id' => $usuarioId,
        ]);
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
