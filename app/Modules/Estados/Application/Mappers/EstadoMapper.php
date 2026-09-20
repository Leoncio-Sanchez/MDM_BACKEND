<?php

declare(strict_types=1);

namespace App\Modules\Estados\Application\Mappers;

use App\Modules\Estados\Domain\Entities\Estado;
use App\Modules\Estados\Infrastructure\Persistence\Models\EstadoModel;

final class EstadoMapper
{
    public function toDomain(EstadoModel $model): Estado
    {
        return new Estado(
            id: $model->id,
            nombre: $model->nombre,
            esParaLf: (bool) $model->es_para_lf,
            esParaItse: (bool) $model->es_para_itse,
            estaActivo: (bool) $model->esta_activo,
        );
    }
}
