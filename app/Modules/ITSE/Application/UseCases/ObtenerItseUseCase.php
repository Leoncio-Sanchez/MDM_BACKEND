<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;
use App\Modules\ITSE\Domain\Entities\Itse;
use App\Modules\ITSE\Domain\Exceptions\ItseNoEncontrado;

final readonly class ObtenerItseUseCase
{
    public function __construct(private ItseRepositoryInterface $itses) {}

    public function execute(int $id): Itse
    {
        $itse = $this->itses->buscarPorId($id);
        if (!$itse) {
            throw ItseNoEncontrado::porId($id);
        }
        return $itse;
    }
}
