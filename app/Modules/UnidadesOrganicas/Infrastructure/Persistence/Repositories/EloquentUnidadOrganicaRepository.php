<?php

declare(strict_types=1);

namespace App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Repositories;

use App\Modules\UnidadesOrganicas\Domain\Contracts\UnidadOrganicaRepositoryInterface;
use App\Modules\UnidadesOrganicas\Domain\Entities\UnidadOrganica;
use App\Modules\UnidadesOrganicas\Infrastructure\Persistence\Models\UnidadOrganicaModel;
use Illuminate\Support\Facades\DB;

final class EloquentUnidadOrganicaRepository implements UnidadOrganicaRepositoryInterface
{
    /** @return array<int, UnidadOrganica> */
    public function listar(): array
    {
        return UnidadOrganicaModel::orderBy('nombre', 'asc')
            ->get()
            ->map(fn (UnidadOrganicaModel $m) => $this->toDomain($m))
            ->all();
    }

    public function buscarPorId(int $id): ?UnidadOrganica
    {
        $model = UnidadOrganicaModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function guardar(UnidadOrganica $unidad): UnidadOrganica
    {
        return $this->toDomain(UnidadOrganicaModel::create($this->toAttributes($unidad)));
    }

    public function actualizar(UnidadOrganica $unidad): UnidadOrganica
    {
        $model = UnidadOrganicaModel::findOrFail($unidad->id);
        $model->update($this->toAttributes($unidad));

        return $this->toDomain($model);
    }

    public function eliminar(int $id): void
    {
        UnidadOrganicaModel::destroy($id);
    }

    /** @return array{procedimientos: int, usuarios: int} */
    public function contarDependencias(int $id): array
    {
        return [
            'procedimientos' => DB::table('tipos_procedimiento_tupa')->where('unidad_organica_id', $id)->count(),
            'usuarios' => DB::table('usuarios_perfiles')->where('unidad_organica_id', $id)->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function toAttributes(UnidadOrganica $unidad): array
    {
        return [
            'codigo' => $unidad->codigo,
            'nombre' => $unidad->nombre,
            'sigla' => $unidad->sigla,
            'esta_activo' => $unidad->estaActivo,
        ];
    }

    private function toDomain(UnidadOrganicaModel $m): UnidadOrganica
    {
        return new UnidadOrganica(
            id: $m->id,
            codigo: $m->codigo,
            nombre: $m->nombre,
            sigla: (string) ($m->sigla ?? ''),
            estaActivo: (bool) $m->esta_activo,
        );
    }
}
