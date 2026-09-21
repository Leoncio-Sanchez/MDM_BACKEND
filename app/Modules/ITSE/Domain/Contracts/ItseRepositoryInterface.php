<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Domain\Contracts;

use App\Modules\ITSE\Domain\Entities\Itse;

interface ItseRepositoryInterface
{
    public function crear(Itse $itse): Itse;
    public function actualizar(Itse $itse): Itse;
    public function buscarPorId(int $id): ?Itse;
    
    public function existeNumero(int $numero, ?int $excluirId = null): bool;
    public function existeRecibo(string $recibo, ?int $excluirId = null): bool;

    /**
     * @param string $filtro
     * @param string $valor
     * @return array<int, array<string, mixed>>
     */
    public function buscarPorFiltro(string $filtro, string $valor): array;
}
