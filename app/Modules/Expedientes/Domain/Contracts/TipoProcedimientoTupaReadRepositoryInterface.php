<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\Contracts;

interface TipoProcedimientoTupaReadRepositoryInterface
{
    /**
     * @return object|null Objeto con id, plazo_atencion_dias, dias_alerta_vencimiento, esta_activo
     */
    public function buscarPorId(int $id): ?object;

    public function existe(int $id): bool;
}
