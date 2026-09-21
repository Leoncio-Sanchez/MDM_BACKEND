<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

use App\Modules\ITSE\Application\DTOs\VerificarExpedienteItseDTO;
use App\Modules\ITSE\Domain\Contracts\AutorizacionImprocedenteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ExpedienteReadRepositoryInterface;
use App\Modules\ITSE\Domain\Contracts\ItseRepositoryInterface;

final readonly class VerificarExpedienteItseUseCase
{
    public function __construct(
        private ExpedienteReadRepositoryInterface $expedientes,
        private AutorizacionImprocedenteReadRepositoryInterface $improcedentes,
        private ItseRepositoryInterface $itses,
    ) {}

    /** @return array<string, mixed> */
    public function execute(VerificarExpedienteItseDTO $dto): array
    {
        /** @var \stdClass|null $expediente */
        $expediente = $this->expedientes->buscarPorNumeroYAnio(
            $dto->numeroExpediente,
            $dto->anio,
        );

        if (!$expediente) {
            return [
                'se_puede_emitir_itse' => false,
                'expediente_id' => null,
                'mensaje' => 'El expediente no existe, primero debe ingresarlo.',
            ];
        }

        if ($this->improcedentes->existe($expediente->id, 'ITSE')) {
            return [
                'se_puede_emitir_itse' => false,
                'expediente_id' => $expediente->id,
                'mensaje' => 'El expediente registra ITSE denegado.',
            ];
        }

        $itseData = current($this->itses->buscarPorFiltro('EXPEDIENTE', (string) $dto->numeroExpediente));
        if ($itseData) {
            return [
                'se_puede_emitir_itse' => false,
                'expediente_id' => $expediente->id,
                'mensaje' => 'El expediente ya registra el ITSE número ' . $itseData['numero_itse'] . '.',
            ];
        }

        return [
            'se_puede_emitir_itse' => true,
            'expediente_id' => $expediente->id,
            'mensaje' => '',
        ];
    }
}
