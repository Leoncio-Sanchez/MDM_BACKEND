<?php
declare(strict_types=1);

namespace App\Modules\Personas\Domain\Contracts;

use App\Modules\Personas\Domain\Entities\Persona;

interface PersonaRepositoryInterface
{
    /** @return array{items: Persona[], total: int} */
    public function paginar(int $pagina, int $porPagina): array;

    public function buscarPorId(int $id): ?Persona;

    /** @return array<int, array<string, mixed>> */
    public function buscarPorFiltro(string $filtro, string $valor): array;
    public function tieneDependientes(int $id): bool;

    public function crear(Persona $persona): Persona;

    public function actualizar(Persona $persona): Persona;

    public function eliminar(int $id): void;
}
