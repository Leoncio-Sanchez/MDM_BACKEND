<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

use App\Modules\Expedientes\Domain\Entities\AutorizacionImprocedente;
use App\Modules\Expedientes\Domain\ValueObjects\TipoAutorizacion;

interface AutorizacionImprocedenteRepositoryInterface
{
    public function buscarPorExpedienteYTipo(int $expedienteId, TipoAutorizacion $tipo): ?AutorizacionImprocedente;

    public function crear(AutorizacionImprocedente $autorizacion): AutorizacionImprocedente;

    public function existe(int $expedienteId, TipoAutorizacion $tipo): bool;
}
