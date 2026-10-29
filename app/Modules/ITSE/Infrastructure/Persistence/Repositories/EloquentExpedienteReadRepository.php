<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Repositories;

use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Infrastructure\Persistence\Models\ExpedienteReadModel;

final class EloquentExpedienteReadRepository implements ExpedienteReadRepositoryInterface
{
    public function existe(int $id): bool
    {
        return ExpedienteReadModel::where('id', $id)->exists();
    }

    public function estaAnulado(int $id): bool
    {
        return \Illuminate\Support\Facades\DB::table('expedientes_anulaciones')->where('expediente_id', $id)->exists();
    }

    public function buscarPorNumeroYAnio(int $numero, int $anio): ?\stdClass
    {
        $model = ExpedienteReadModel::where('numero_expediente', $numero)
            ->whereYear('fecha_recepcion', $anio)
            ->first();
            
        return $model ? (object) $model->toArray() : null;
    }

    public function buscarPorId(int $id): ?\stdClass
    {
        $model = ExpedienteReadModel::find($id);
        return $model ? (object) $model->toArray() : null;
    }
}
