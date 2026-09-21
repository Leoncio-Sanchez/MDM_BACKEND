<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\NotificarItseDTO;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseFechaInvalida;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;
use Illuminate\Support\Facades\DB;

final readonly class NotificarItseUseCase
{
    public function __construct(private ItseRepositoryInterface $itses) {}

    public function execute(NotificarItseDTO $dto): Itse
    {
        return DB::transaction(function () use ($dto): Itse {
            $itse = $this->itses->buscarPorId($dto->itseId);
            if (!$itse) {
                throw ItseNoEncontrado::porId($dto->itseId);
            }

            if ($dto->fechaNotificacion < $itse->fechaExpedicion) {
                throw ItseFechaInvalida::notificacionAntesDeExpedicion();
            }

            $actualizada = $itse->conFechaNotificacion($dto->fechaNotificacion);
            return $this->itses->actualizar($actualizada);
        });
    }
}
