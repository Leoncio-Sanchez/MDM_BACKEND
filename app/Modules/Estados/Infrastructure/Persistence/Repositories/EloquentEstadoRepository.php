<?php

declare(strict_types=1);

namespace App\Modules\Estados\Infrastructure\Persistence\Repositories;

use App\Modules\Estados\Application\Mappers\EstadoMapper;
use App\Modules\Estados\Domain\Contracts\EstadoRepositoryInterface;
use App\Modules\Estados\Domain\Entities\Estado;
use App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModel;

final readonly class EloquentEstadoRepository implements EstadoRepositoryInterface
{
    public function __construct(
        private EstadoMapper $mapper,
    ) {}

    /** @return array{items: Estado[], total: int} */
    public function paginar(int $pagina, int $porPagina): array
    {
        $query = EstadoModel::query()->orderBy('id');
        $total = $query->count();
        $items = $query
            ->skip(($pagina - 1) * $porPagina)
            ->take($porPagina)
            ->get()
            ->map(fn (EstadoModel $m) => $this->mapper->toDomain($m))
            ->all();

        return ['items' => $items, 'total' => $total];
    }

    public function buscarPorId(int $id): ?Estado
    {
        $model = EstadoModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function crear(Estado $estado): Estado
    {
        $model = EstadoModel::create([
            'nombre' => $estado->nombre,
            'es_para_lf' => $estado->esParaLf,
            'es_para_itse' => $estado->esParaItse,
            'esta_activo' => $estado->estaActivo,
        ]);

        return $this->mapper->toDomain($model);
    }

    public function actualizar(Estado $estado): Estado
    {
        $model = EstadoModel::findOrFail($estado->id);
        $model->update([
            'nombre' => $estado->nombre,
            'es_para_lf' => $estado->esParaLf,
            'es_para_itse' => $estado->esParaItse,
            'esta_activo' => $estado->estaActivo,
        ]);

        return $this->mapper->toDomain($model->refresh());
    }

    public function eliminar(int $id): bool
    {
        return (bool) EstadoModel::destroy($id);
    }
}
