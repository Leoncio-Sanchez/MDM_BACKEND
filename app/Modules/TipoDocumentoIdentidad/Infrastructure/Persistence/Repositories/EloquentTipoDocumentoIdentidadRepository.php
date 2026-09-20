<?php

declare(strict_types=1);

namespace App\Modules\TipoDocumentoIdentidad\Infrastructure\Persistence\Repositories;

use App\Modules\TipoDocumentoIdentidad\Domain\Contracts\TipoDocumentoIdentidadRepositoryInterface;
use App\Modules\TipoDocumentoIdentidad\Domain\Entities\TipoDocumentoIdentidad;
use App\Modules\TipoDocumentoIdentidad\Infrastructure\Persistence\Models\TipoDocumentoIdentidadModel;

class EloquentTipoDocumentoIdentidadRepository implements TipoDocumentoIdentidadRepositoryInterface
{
    /** @return TipoDocumentoIdentidad[] */
    public function obtenerTodos(string $tipoPersona): array
    {
        $query = TipoDocumentoIdentidadModel::query();
        if ($tipoPersona === 'N') {
            $query->where('es_para_natural', true);
        } else {
            $query->where('es_para_juridica', true);
        }

        return $query->orderBy('id')->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function buscarPorId(int $id): ?TipoDocumentoIdentidad
    {
        $m = TipoDocumentoIdentidadModel::find($id);

        return $m ? $this->toDomain($m) : null;
    }

    public function guardar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad
    {
        $m = TipoDocumentoIdentidadModel::create([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'es_para_natural' => $entity->esParaNatural,
            'es_para_juridica' => $entity->esParaJuridica,
        ]);

        return $this->toDomain($m);
    }

    public function actualizar(TipoDocumentoIdentidad $entity): TipoDocumentoIdentidad
    {
        $m = TipoDocumentoIdentidadModel::findOrFail($entity->id);
        $m->update([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'es_para_natural' => $entity->esParaNatural,
            'es_para_juridica' => $entity->esParaJuridica,
        ]);

        return $this->toDomain($m);
    }

    public function eliminar(int $id): void
    {
        TipoDocumentoIdentidadModel::destroy($id);
    }

    private function toDomain(TipoDocumentoIdentidadModel $m): TipoDocumentoIdentidad
    {
        return new TipoDocumentoIdentidad(
            $m->id,
            $m->codigo,
            $m->nombre,
            $m->esta_activo,
            $m->es_para_natural,
            $m->es_para_juridica
        );
    }
}
