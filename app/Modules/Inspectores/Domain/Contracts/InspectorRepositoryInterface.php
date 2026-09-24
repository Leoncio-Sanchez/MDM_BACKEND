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
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): UsuarioModel;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(int $id, array $datos): ?UsuarioModel;

    public function eliminar(int $id): bool;
}
