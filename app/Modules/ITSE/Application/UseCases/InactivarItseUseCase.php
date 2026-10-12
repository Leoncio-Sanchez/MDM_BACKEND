<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\InactivarItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseEstadoRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Entities\ItseEstado;
use App\Modules\ITSE\Domain\Exceptions\ItseInvalido;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class InactivarItseUseCase
{
    public function __construct(
        private ItseRepositoryInterface $itses,
        private ItseEstadoRepositoryInterface $estados,
    ) {}

    public function execute(InactivarItseDTO $dto): Itse
    {
        return DB::transaction(function () use ($dto): Itse {
            $itse = $this->itses->buscarPorId($dto->itseId);
            if (!$itse) {
                throw ItseNoEncontrado::porId($dto->itseId);
            }

            if ($this->estados->tieneEstadoInactivo($dto->itseId)) {
                throw ItseInvalido::yaInactivo($dto->itseId);
            }

            $this->estados->crear(ItseEstado::crear(
                fechaEstado: $dto->fechaEstado,
                documento: $dto->documento,
                observaciones: $dto->observaciones,
                fechaDigitacion: CarbonImmutable::now(),
                estadoId: $dto->estadoId,
                itseId: $dto->itseId,
                usuarioId: $dto->usuarioId,
            ));

            /** @var Itse $itseRecargado */
            $itseRecargado = $this->itses->buscarPorId($dto->itseId);
            return $itseRecargado;
        });
    }
}
