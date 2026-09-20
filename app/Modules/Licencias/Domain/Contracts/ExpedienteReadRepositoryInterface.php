<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Contracts;

interface ExpedienteReadRepositoryInterface
{
    public function buscarPorId(int $id): ?object;
    public function buscarPorNumeroYAnio(int $numero, int $anio): ?object;
    public function tieneAutorizacionImprocedente(int $expedienteId, string $tipo): bool;
}
