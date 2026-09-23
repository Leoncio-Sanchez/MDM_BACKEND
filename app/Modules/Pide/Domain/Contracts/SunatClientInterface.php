<?php

declare(strict_types=1);

namespace App\Modules\Pide\Domain\Contracts;

use App\Modules\Pide\Domain\Exceptions\SunatError;

interface SunatClientInterface
{
    /**
     * Consulta los datos principales de un contribuyente en SUNAT vía PIDE.
     *
     * @param  string  $ruc  Número de RUC (11 dígitos).
     * @return array<string, mixed> Respuesta JSON de PIDE.
     *
     * @throws SunatError
     * @throws \InvalidArgumentException Si el RUC no tiene 11 dígitos numéricos.
     */
    public function consultarPorRuc(string $ruc): array;
}
