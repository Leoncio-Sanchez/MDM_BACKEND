<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteConItseError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteConLicenciaError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use Illuminate\Support\Facades\DB;

final readonly class EliminarExpedienteUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
    ) {}

    public function execute(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $expediente = $this->expedientes->buscarPorId($id);
            if (!$expediente) {
                throw ExpedienteNoEncontrado::porId($id);
            }

            if ($this->expedientes->tieneLicencia($id)) {
                throw ExpedienteConLicenciaError::porId($id);
            }

            if ($this->expedientes->tieneItse($id)) {
                throw ExpedienteConItseError::porId($id);
            }

            $this->expedientes->eliminar($id);
        });
    }
}
