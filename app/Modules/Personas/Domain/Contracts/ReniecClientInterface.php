<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\Contracts;

use App\Modules\Personas\Application\DTOs\PersonaReniecDTO;

interface ReniecClientInterface
{
    public function consultarPorDni(string $dni): PersonaReniecDTO;
}
