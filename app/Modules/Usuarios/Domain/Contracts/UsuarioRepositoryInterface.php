<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Domain\Contracts;

use App\Modules\Usuarios\Infrastructure\Persistence\Models\UsuarioModel;

interface UsuarioRepositoryInterface
{
    /**
     * @return array<int, UsuarioModel>
     */
    public function listar(?string $busqueda = null): array;

    public function buscarPorId(int $id): ?UsuarioModel;

    public function buscarPorUsername(string $username): ?UsuarioModel;

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function crear(array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): UsuarioModel;

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>|null  $perfilDatos
     */
    public function actualizar(int $id, array $datos, ?array $perfilDatos = null, ?int $digitadorId = null): ?UsuarioModel;

    public function desactivar(int $id): bool;

    public function actualizarPassword(int $id, string $hashedPassword): bool;

    /**
     * @return array{expedientes: bool, licencias: bool, itse: bool, admin: bool}
     */
    public function obtenerOpcionesUsuario(int $id): array;
}
