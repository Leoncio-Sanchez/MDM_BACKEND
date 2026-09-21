<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

use App\Modules\ITSE\Domain\Entities\ItseEstado;

interface ItseEstadoRepositoryInterface
{
    public function crear(ItseEstado $estado): ItseEstado;
    public function tieneEstado(int $itseId, string $estadoNombre): bool;
    public function insertar(int $itseId, string $estado, string $fecha, string $observaciones, int $usuarioId): void;
}
