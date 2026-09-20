<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Infrastructure\Persistence\Repositories;

use App\Modules\NivelRiesgo\Domain\Contracts\NivelRiesgoRepositoryInterface;
use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;
use App\Modules\NivelRiesgo\Infrastructure\Persistence\Models\NivelRiesgoModel;

class EloquentNivelRiesgoRepository implements NivelRiesgoRepositoryInterface
{
    public function obtenerTodos(): array
    {
        return NivelRiesgoModel::all()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function buscarPorId(int $id): ?NivelRiesgo
    {
        $m = NivelRiesgoModel::find($id);

        return $m ? $this->toDomain($m) : null;
    }

    public function guardar(NivelRiesgo $entity): NivelRiesgo
    {
        $m = NivelRiesgoModel::create([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
        ]);

        return $this->toDomain($m);
    }

    public function actualizar(NivelRiesgo $entity): NivelRiesgo
    {
        $m = NivelRiesgoModel::findOrFail($entity->id);
        $m->update([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
        ]);

        return $this->toDomain($m);
    }

    public function eliminar(int $id): void
    {
        NivelRiesgoModel::destroy($id);
    }

    private function toDomain(NivelRiesgoModel $m): NivelRiesgo
    {
        return new NivelRiesgo($m->id, $m->codigo, $m->nombre, $m->esta_activo);
    }
}
