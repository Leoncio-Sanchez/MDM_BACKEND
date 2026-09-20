<?php

declare(strict_types=1);

namespace App\Modules\Zonificacion\Infrastructure\Persistence\Repositories;

use App\Modules\Zonificacion\Domain\Contracts\ZonificacionRepositoryInterface;
use App\Modules\Zonificacion\Domain\Entities\Zonificacion;
use App\Modules\Zonificacion\Infrastructure\Persistence\Models\ZonificacionModel;
use DateTimeImmutable;

class EloquentZonificacionRepository implements ZonificacionRepositoryInterface
{
    /** @return Zonificacion[] */
    public function obtenerTodos(): array
    {
        return ZonificacionModel::all()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function buscarPorId(int $id): ?Zonificacion
    {
        $m = ZonificacionModel::find($id);

        return $m ? $this->toDomain($m) : null;
    }

    public function guardar(Zonificacion $entity): Zonificacion
    {
        $m = ZonificacionModel::create([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'fecha_digitacion' => $entity->fechaDigitacion,
            'usuario_id' => $entity->usuarioId,
        ]);

        return $this->toDomain($m);
    }

    public function actualizar(Zonificacion $entity): Zonificacion
    {
        $m = ZonificacionModel::findOrFail($entity->id);
        $m->update([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'esta_activo' => $entity->estaActivo,
            'fecha_digitacion' => $entity->fechaDigitacion,
            'usuario_id' => $entity->usuarioId,
        ]);

        return $this->toDomain($m);
    }

    public function eliminar(int $id): void
    {
        ZonificacionModel::destroy($id);
    }

    private function toDomain(ZonificacionModel $m): Zonificacion
    {
        return new Zonificacion(
            $m->id,
            $m->codigo,
            $m->nombre,
            $m->esta_activo,
            new DateTimeImmutable((string) $m->fecha_digitacion),
            $m->usuario_id
        );
    }
}
