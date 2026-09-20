<?php

declare(strict_types=1);

namespace App\Modules\TipoLicencia\Infrastructure\Persistence\Repositories;

use App\Modules\TipoLicencia\Domain\Contracts\TipoLicenciaRepositoryInterface;
use App\Modules\TipoLicencia\Domain\Entities\TipoLicencia;
use App\Modules\TipoLicencia\Infrastructure\Persistence\Models\TipoLicenciaModel;

final class EloquentTipoLicenciaRepository implements TipoLicenciaRepositoryInterface
{
    public function listar(?bool $estaActivo): array
    {
        $query = TipoLicenciaModel::query();
        if ($estaActivo !== null) {
            $query->where('esta_activo', $estaActivo);
        }

        return $query->orderBy('id')->get()->map(fn ($m) => $this->toEntity($m))->toArray();
    }

    public function obtenerPorId(int $id): ?TipoLicencia
    {
        $model = TipoLicenciaModel::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function guardar(TipoLicencia $tipo): TipoLicencia
    {
        $model = TipoLicenciaModel::findOrNew($tipo->id);
        $model->fill([
            'codigo' => $tipo->codigo,
            'nombre' => $tipo->nombre,
            'esta_activo' => $tipo->estaActivo,
        ]);
        $model->save();

        return $this->toEntity($model);
    }

    public function eliminar(int $id): void
    {
        TipoLicenciaModel::destroy($id);
    }

    private function toEntity(TipoLicenciaModel $model): TipoLicencia
    {
        return new TipoLicencia(
            id: $model->id,
            codigo: $model->codigo,
            nombre: $model->nombre,
            estaActivo: $model->esta_activo
        );
    }
}
