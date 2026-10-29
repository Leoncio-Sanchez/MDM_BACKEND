<?php

declare(strict_types=1);

namespace App\Modules\Inspectores\Domain\Contracts;

use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

interface InspectorRepositoryInterface
{
    /**
     * @return array<int, UsuarioModel>
     */
    public function listar(?string $busqueda = null): array;

    public function buscarPorId(int $id): ?UsuarioModel;

    /**
     * Crea la cuenta con su perfil de inspector.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos, int $digitadorId): UsuarioModel;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(int $id, array $datos): ?UsuarioModel;

    /** Los usuarios no se borran: la cuenta queda inactiva. false si no es un inspector */
    public function desactivar(int $id): bool;
}
