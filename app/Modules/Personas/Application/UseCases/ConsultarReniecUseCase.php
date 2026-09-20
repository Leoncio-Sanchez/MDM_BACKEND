<?php
declare(strict_types=1);

namespace App\Modules\Personas\Application\UseCases;

use App\Modules\Personas\Application\DTOs\ConsultarReniecDTO;
use App\Modules\Personas\Application\DTOs\PersonaReniecDTO;
use App\Modules\Personas\Domain\Contracts\ReniecClientInterface;
use App\Modules\Personas\Domain\Exceptions\DocumentoInvalido;
use App\Modules\Personas\Domain\Exceptions\ReniecNoDisponible;
use Psr\Log\LoggerInterface;

final readonly class ConsultarReniecUseCase
{
    public function __construct(
        private ReniecClientInterface $reniec,
        private LoggerInterface $logger,
    ) {}

    public function execute(ConsultarReniecDTO $dto): PersonaReniecDTO
    {
        if (!preg_match('/^\d{8}$/', $dto->dni)) {
            throw new DocumentoInvalido("El DNI debe tener exactamente 8 dígitos numéricos.");
        }

        $this->logger->info('Consultando RENIEC', ['dni' => $dto->dni]);

        try {
            return $this->reniec->consultarPorDni($dto->dni);
        } catch (ReniecNoDisponible $e) {
            $this->logger->error('RENIEC no disponible', ['dni' => $dto->dni]);
            throw $e;
        }
    }
}
