<?php

declare(strict_types=1);

namespace App\Modules\TipoProcedimientoTupa\Infrastructure\Persistence\Repositories;

use App\Modules\TipoProcedimientoTupa\Domain\Contracts\TipoProcedimientoTupaRepositoryInterface;
use App\Modules\TipoProcedimientoTupa\Domain\Entities\TipoProcedimientoTupa;
use App\Modules\TipoProcedimientoTupa\Infrastructure\Persistence\Models\TipoProcedimientoTupaModel;

class EloquentTipoProcedimientoTupaRepository implements TipoProcedimientoTupaRepositoryInterface
{
    /** @return TipoProcedimientoTupa[] */
    public function obtenerTodos(): array
    {
        return TipoProcedimientoTupaModel::orderBy('nombre')->get()->map(fn ($m) => $this->toDomain($m))->toArray();
    }

    public function buscarPorId(int $id): ?TipoProcedimientoTupa
    {
        $m = TipoProcedimientoTupaModel::find($id);

        return $m ? $this->toDomain($m) : null;
    }

    public function guardar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa
    {
        $m = TipoProcedimientoTupaModel::create([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'plazo_atencion_dias' => $entity->plazoAtencionDias,
            'dias_alerta_vencimiento' => $entity->diasAlertaVencimiento,
            'esta_activo' => $entity->estaActivo,
            'requiere_lf' => $entity->requiereLf,
            'requiere_itse' => $entity->requiereItse,
            'fecha_digitacion' => $entity->fechaDigitacion->format('Y-m-d H:i:s.u'),
            'unidad_organica_id' => $entity->unidadOrganicaId,
            'usuario_id' => $entity->usuarioId,
        ]);

        return $this->toDomain($m);
    }

    public function actualizar(TipoProcedimientoTupa $entity): TipoProcedimientoTupa
    {
        $m = TipoProcedimientoTupaModel::findOrFail($entity->id);
        $m->update([
            'codigo' => $entity->codigo,
            'nombre' => $entity->nombre,
            'plazo_atencion_dias' => $entity->plazoAtencionDias,
            'dias_alerta_vencimiento' => $entity->diasAlertaVencimiento,
            'esta_activo' => $entity->estaActivo,
            'requiere_lf' => $entity->requiereLf,
            'requiere_itse' => $entity->requiereItse,
            'unidad_organica_id' => $entity->unidadOrganicaId,
        ]);

        return $this->toDomain($m);
    }

    public function eliminar(int $id): void
    {
        TipoProcedimientoTupaModel::destroy($id);
    }

    private function toDomain(TipoProcedimientoTupaModel $m): TipoProcedimientoTupa
    {
        return new TipoProcedimientoTupa(
            $m->id,
            $m->codigo,
            $m->nombre,
            $m->plazo_atencion_dias,
            $m->dias_alerta_vencimiento,
            $m->esta_activo,
            $m->requiere_lf,
            $m->requiere_itse,
            new \DateTimeImmutable($m->fecha_digitacion->format('Y-m-d H:i:s.u')),
            $m->unidad_organica_id,
            $m->usuario_id
        );
    }
}
