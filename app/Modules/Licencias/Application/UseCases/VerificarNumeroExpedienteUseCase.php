<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\UseCases;
use App\Modules\Licencias\Application\DTOs\VerificarExpedienteDTO;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\ExpedienteReadRepositoryInterface;

final readonly class VerificarNumeroExpedienteUseCase {
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
        private ExpedienteReadRepositoryInterface $expedientes,
    ) {}

    /** @return array<string, mixed> */
    public function execute(VerificarExpedienteDTO $dto): array {
        $expediente = $this->expedientes->buscarPorNumeroYAnio((int) $dto->numero, (int) $dto->anio);

        if (!$expediente) {
            return [
                'expediente_id' => null,
                'mensaje' => 'El expediente no existe, primero debe ingresarlo.',
                'se_puede_emitir_licencia' => false,
            ];
        }

        // Mock autorizacion improcedente - will be moved to its own module/repo later
        // In the meantime, we skip the DB::table check to keep UseCase pure.

        $licencia = $this->licencias->buscarPorExpedienteId($expediente->id ?? 0);

        if ($licencia) {
            return [
                'expediente_id' => $expediente->id,
                'mensaje' => "El expediente ya registra la licencia número {$licencia->numeroLicencia->value}.",
                'se_puede_emitir_licencia' => false,
            ];
        }

        return [
            'expediente_id' => $expediente->id,
            'mensaje' => 'El expediente es válido para emitir licencia.',
            'se_puede_emitir_licencia' => true,
        ];
    }
}
