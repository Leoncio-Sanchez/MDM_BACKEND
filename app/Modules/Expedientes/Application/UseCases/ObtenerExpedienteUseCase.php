<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;

final readonly class ObtenerExpedienteUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
    ) {}

    public function execute(int $id): Expediente
    {
        $expediente = $this->expedientes->buscarPorId($id);
        if (!$expediente) {
            throw ExpedienteNoEncontrado::porId($id);
        }

        return $expediente;
    }
}
