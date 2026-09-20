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
    public function buscarPorFiltro(string $filtro, string $valor): array {
        $query = LicenciaModel::query();
        match ($filtro) {
            'NUMERO_LICENCIA' => $query->where('numero_licencia', (int) $valor),
            'NOMBRE_COMERCIAL' => $query->where('nombre_comercial', 'ILIKE', "%{$valor}%"),
            'EXPEDIENTE' => $query->where('expediente_id', (int) $valor),
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
}
