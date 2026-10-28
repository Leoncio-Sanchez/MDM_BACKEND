<?php

declare(strict_types=1);

namespace App\Modules\Pide\Application\UseCases;

use App\Modules\Pide\Application\DTOs\ConsultaSunatDTO;
use App\Modules\Pide\Application\Mappers\PideMapper;
use App\Modules\Pide\Domain\Contracts\SunatClientInterface;
use App\Modules\Pide\Domain\ValueObjects\ContribuyenteSunat;

final readonly class ConsultarSunatUseCase
{
    public function __construct(
        private SunatClientInterface $sunat,
    ) {}

    /** Solo los datos que usa el formulario de Personas para una persona jurídica. */
    public function execute(ConsultaSunatDTO $dto): ContribuyenteSunat
    {
        return PideMapper::contribuyenteDesdeSunat($dto->ruc, $this->sunat->consultarPorRuc($dto->ruc));
    }
}
