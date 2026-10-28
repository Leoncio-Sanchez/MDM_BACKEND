<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Application\UseCases;

use App\Modules\ConsultaPublica\Application\DTOs\ConsultaPublicaDTO;
use App\Modules\ConsultaPublica\Domain\Contracts\ConsultaPublicaRepositoryInterface;
use App\Modules\ConsultaPublica\Domain\Exceptions\TramiteNoEncontrado;
use App\Modules\ConsultaPublica\Domain\ValueObjects\TipoTramite;
use App\Modules\ConsultaPublica\Domain\ValueObjects\TramitePublico;

final readonly class ConsultarTramitePublicoUseCase
{
    public function __construct(private ConsultaPublicaRepositoryInterface $consultas) {}

    /**
     * @return list<TramitePublico>
     *
     * @throws TramiteNoEncontrado
     */
    public function execute(ConsultaPublicaDTO $dto): array
    {
        $resultados = match ($dto->tipo) {
            TipoTramite::Expediente => $this->consultas->expedientes($dto->numero, $dto->documento),
            TipoTramite::Licencia => $this->consultas->licencias($dto->numero, $dto->documento),
            TipoTramite::Itse => $this->consultas->itse($dto->numero, $dto->documento),
        };

        if ($resultados === []) {
            throw TramiteNoEncontrado::conEsosDatos();
        }

        return $resultados;
    }
}
