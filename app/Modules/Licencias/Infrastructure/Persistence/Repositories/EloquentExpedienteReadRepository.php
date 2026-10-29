<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Repositories;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\Licencias\Infrastructure\Persistence\Models\ExpedienteReadModel;
final readonly class EloquentExpedienteReadRepository implements ExpedienteReadRepositoryInterface {

    public function buscarPorId(int $id): ?object { return ExpedienteReadModel::find($id); }
    public function tieneAutorizacionImprocedente(int $expedienteId, string $tipo): bool
    {
        return \Illuminate\Support\Facades\DB::table("autorizaciones_improcedentes")->where("expediente_id", $expedienteId)->where("tipo_autorizacion", $tipo)->exists();
    }

    public function estaAnulado(int $expedienteId): bool
    {
        return \Illuminate\Support\Facades\DB::table('expedientes_anulaciones')->where('expediente_id', $expedienteId)->exists();
    }

    public function buscarPorNumeroYAnio(int $numero, int $anio): ?object {
        return ExpedienteReadModel::query()->where('numero', $numero)->whereYear('fecha_ingreso', $anio)->first();
    }
}
