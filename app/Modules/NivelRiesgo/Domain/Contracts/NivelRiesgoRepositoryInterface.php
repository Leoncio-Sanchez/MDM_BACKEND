<?php

declare(strict_types=1);

namespace App\Modules\NivelRiesgo\Domain\Contracts;

use App\Modules\NivelRiesgo\Domain\Entities\NivelRiesgo;

interface NivelRiesgoRepositoryInterface
{
    /**
     * @return NivelRiesgo[]
     */
    public function obtenerTodos(): array;

    public function buscarPorId(int $id): ?NivelRiesgo;

    public function guardar(NivelRiesgo $nivelRiesgo): NivelRiesgo;

    public function actualizar(NivelRiesgo $nivelRiesgo): NivelRiesgo;

    public function eliminar(int $id): void;
}
