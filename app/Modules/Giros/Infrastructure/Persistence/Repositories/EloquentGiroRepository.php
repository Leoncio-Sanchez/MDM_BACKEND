<?php

declare(strict_types=1);

namespace App\Modules\Giros\Infrastructure\Persistence\Repositories;

use App\Modules\Giros\Domain\Contracts\GiroRepositoryInterface;
use App\Modules\Giros\Domain\Entities\Giro;
use App\Modules\Giros\Infrastructure\Persistence\Models\GiroModel;

class EloquentGiroRepository implements GiroRepositoryInterface
{
    /** @return Giro[] */
    public function obtenerTodos(): array
    {
        return GiroModel::orderBy('nombre')->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function buscarPorId(int $id): ?Giro
    {
        $m = GiroModel::find($id);

        return $m ? $this->toDomain($m) : null;
    }

    public function guardar(Giro $entity): Giro
    {
        $m = GiroModel::create([
            'ciiu_id' => $entity->ciiuId,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'fecha_digitacion' => $entity->fechaDigitacion?->format('Y-m-d H:i:s.u'),
            'usuario_id' => $entity->usuarioId,
        ]);

        return $this->toDomain($m);
    }

    public function actualizar(Giro $entity): Giro
    {
        $m = GiroModel::findOrFail($entity->id);
        $m->update([
            'ciiu_id' => $entity->ciiuId,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'usuario_id' => $entity->usuarioId,
        ]);

        return $this->toDomain($m);
    }

    public function eliminar(int $id): void
    {
        GiroModel::destroy($id);
    }

    private function toDomain(GiroModel $m): Giro
    {
        return new Giro(
            $m->id,
            $m->ciiu_id,
            $m->nombre,
            $m->esta_activo,
            $m->fecha_digitacion ? new \DateTimeImmutable($m->fecha_digitacion->format('Y-m-d H:i:s.u')) : null,
            $m->usuario_id
        );
    }
}
