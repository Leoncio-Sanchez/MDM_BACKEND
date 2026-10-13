<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Application\Mappers\LicenciaMapper;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Entities\Licencia;
use App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel;

final readonly class EloquentLicenciaRepository implements LicenciaRepositoryInterface {
    public function __construct(private LicenciaMapper $mapper) {}

    /** @return array{items: Licencia[], total: int} */
    public function paginar(int $pagina, int $porPagina): array {
        $query = LicenciaModel::query()->orderBy('id', 'desc');
        $total = $query->count();
        /** @var \Illuminate\Database\Eloquent\Collection<int, LicenciaModel> $models */
        $models = $query->skip(($pagina - 1) * $porPagina)->take($porPagina)->get();
        $items = $models->map(fn (LicenciaModel $m) => $this->mapper->toDomain($m))->all();
        return ['items' => $items, 'total' => $total];
    }

    public function buscarPorId(int $id): ?Licencia {
        /** @var LicenciaModel|null $model */
        $model = LicenciaModel::find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    /** @return Licencia[] */
    public function buscarPorExpedienteId(int $expedienteId): ?\App\Modules\Licencias\Domain\Entities\Licencia
    {
        $model = \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::where("expediente_id", $expedienteId)->first();
        return $model ? \App\Modules\Licencias\Application\Mappers\LicenciaMapper::toDomain($model) : null;
    }

    public function buscarParaReporte(string $fechaInicio, string $fechaFin): array
    {
        $models = \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::query()->whereBetween("fecha_emision", [$fechaInicio, $fechaFin])->get();
        return $models->map(fn ($m) => $this->mapper->toDomain($m))->all();
    }

    public function buscarPorFiltro(string $filtro, string $valor): array {
        $query = LicenciaModel::query();
        match ($filtro) {
            'NUMERO_LICENCIA' => $query->where('numero_licencia', (int) $valor),
            'NOMBRE_COMERCIAL' => $query->where('nombre_comercial', 'ILIKE', "%{$valor}%"),
            'EXPEDIENTE' => $query->whereIn('expediente_id', \Illuminate\Support\Facades\DB::table('expedientes')->where('numero_expediente', (int) $valor)->pluck('id')),
            default => $query->whereRaw('1 = 0'),
        };
        /** @var \Illuminate\Database\Eloquent\Collection<int, LicenciaModel> $models */
        $models = $query->limit(50)->get();
        return $models->map(fn (LicenciaModel $m) => $this->mapper->toDomain($m))->all();
    }

    public function crear(Licencia $licencia): Licencia {
        /** @var LicenciaModel $model */
        $data = $this->mapper->toPersistence($licencia);
        $model = new LicenciaModel();
        $model->fill($data);
        $model->uuid = $licencia->uuid;
        $model->save();
        return $this->mapper->toDomain($model);
    }

    public function actualizar(Licencia $licencia): Licencia {
        /** @var LicenciaModel $model */
        $model = LicenciaModel::findOrFail($licencia->id);
        $model->update($this->mapper->toPersistence($licencia));
        return $this->mapper->toDomain($model->refresh());
    }

    public function eliminar(int $id): void {
        LicenciaModel::destroy($id);
    }

    public function inactivar(int $id): Licencia {
        /** @var LicenciaModel $model */
        $model = LicenciaModel::findOrFail($id);
        $model->update(['estado' => 'INACTIVA']);
        return $this->mapper->toDomain($model->refresh());
    }


    public function existeNumeroLicencia(int $numero, ?int $exceptoId = null): bool
    {
        $query = \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::where('numero_licencia', $numero);
        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }
        return $query->exists();
    }

    public function existeReciboPago(string $numero, ?int $exceptoId = null): bool
    {
        $query = \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::where('numero_recibo_pago', $numero);
        if ($exceptoId !== null) {
            $query->where('id', '!=', $exceptoId);
        }
        return $query->exists();
    }

    public function tieneLicenciasDependientes(int $id): bool
    {
        return \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::where('licencia_principal_id', $id)->exists();
    }

    public function listarEstados(int $licenciaId): array
    {
        $sql = "
            SELECT
                lfe.id,
                lfe.licencia_funcionamiento_id,
                lfe.estado_id,
                lfe.fecha_estado,
                lfe.documento,
                lfe.observaciones,
                lfe.usuario_id,
                lfe.fecha_digitacion,
                e.nombre AS estado_nombre,
                e.es_para_lf,
                e.es_para_itse,
                e.esta_activo
            FROM licencias_funcionamiento_estados lfe
            INNER JOIN estados e ON e.id = lfe.estado_id
            WHERE lfe.licencia_funcionamiento_id = ?
            ORDER BY lfe.fecha_digitacion DESC
        ";

        return array_map(
            fn ($row) => (array) $row,
            \Illuminate\Support\Facades\DB::select($sql, [$licenciaId]),
        );
    }

    public function existeEstado(int $licenciaId, int $estadoId): bool
    {
        return \Illuminate\Support\Facades\DB::table('licencias_funcionamiento_estados')
            ->where('licencia_funcionamiento_id', $licenciaId)
            ->where('estado_id', $estadoId)
            ->exists();
    }

    public function insertarEstado(int $licenciaId, int $estadoId, string $fecha, string $documento, string $observaciones, ?int $usuarioId = null): void
    {
        \Illuminate\Support\Facades\DB::table('licencias_funcionamiento_estados')->insert([
            'licencia_funcionamiento_id' => $licenciaId,
            'estado_id' => $estadoId,
            'fecha_estado' => $fecha,
            'documento' => $documento,
            'observaciones' => $observaciones,
            'usuario_id' => $usuarioId,
            'fecha_digitacion' => now(),
        ]);
    }

    public function actualizarFechaNotificacion(int $licenciaId, string $fecha): void
    {
        \App\Modules\Licencias\Infrastructure\Persistence\Models\LicenciaModel::where('id', $licenciaId)->update(['fecha_notificacion' => $fecha]);
    }

}
