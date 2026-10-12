<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

use App\Modules\ITSE\Domain\Entities\ItseEstado;

interface ItseEstadoRepositoryInterface
{
    public function crear(ItseEstado $estado): ItseEstado;
    /** True si el ITSE ya registra algún estado inactivo (estados.esta_activo = false). */
    public function tieneEstadoInactivo(int $itseId): bool;

    /**
     * Lista historial de estados usando el SQL de Django (_SQL_LISTAR_ESTADOS_ITSE).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorItse(int $itseId): array;
}

