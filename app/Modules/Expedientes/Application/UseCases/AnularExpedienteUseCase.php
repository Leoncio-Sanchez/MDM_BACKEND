<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Application\UseCases;

use App\Modules\Expedientes\Domain\Contracts\ExpedienteRepositoryInterface;
use App\Modules\Expedientes\Domain\Entities\Expediente;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteAnulado;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteConItseError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteConLicenciaError;
use App\Modules\Expedientes\Domain\Exceptions\ExpedienteNoEncontrado;
use App\Modules\Expedientes\Domain\ValueObjects\AnulacionExpediente;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Los expedientes no se eliminan (LPAG, Ley 27444): se anulan dejando constancia de motivo,
 * fecha y usuario. Un expediente con licencia o ITSE emitida ya produjo efectos: no se anula.
 */
final readonly class AnularExpedienteUseCase
{
    public function __construct(
        private ExpedienteRepositoryInterface $expedientes,
    ) {}

    public function execute(int $id, string $motivo, ?string $documento, int $usuarioId): Expediente
    {
        return DB::transaction(function () use ($id, $motivo, $documento, $usuarioId): Expediente {
            $expediente = $this->expedientes->buscarPorId($id)
                ?? throw ExpedienteNoEncontrado::porId($id);

            if ($expediente->estaAnulado()) {
                throw ExpedienteAnulado::yaAnulado($expediente->numeroExpediente);
            }
            if ($this->expedientes->tieneLicencia($id)) {
                throw ExpedienteConLicenciaError::porNumero($expediente->numeroExpediente);
            }
            if ($this->expedientes->tieneItse($id)) {
                throw ExpedienteConItseError::porNumero($expediente->numeroExpediente);
            }

            $documento = $documento !== null && trim($documento) !== '' ? trim($documento) : null;
            $this->expedientes->anular($id, new AnulacionExpediente(
                motivo: $motivo,
                documento: $documento,
                fechaAnulacion: new DateTimeImmutable,
                usuarioId: $usuarioId,
            ));

            /** @var Expediente */
            return $this->expedientes->buscarPorId($id);
        });
    }
}
