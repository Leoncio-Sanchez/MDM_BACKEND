<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\Contracts;

use App\Modules\Pide\Domain\Exceptions\ReniecError;

interface ReniecClientInterface
{
    /**
     * Consulta los datos de una persona en RENIEC vía PIDE.
     *
     * @param  string  $dni  Número de DNI (8 dígitos).
     * @return array<string, mixed> Respuesta JSON de PIDE.
     *
     * @throws ReniecError
     * @throws \InvalidArgumentException Si el DNI no tiene 8 dígitos numéricos.
     */
    public function consultarPorDni(string $dni): array;
}
