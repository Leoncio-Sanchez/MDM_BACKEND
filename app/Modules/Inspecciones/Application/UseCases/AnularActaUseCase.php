<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Inspecciones\Domain\Contracts\ActaInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\ActaInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\ActaNoEncontrada;

/**
 * El jefe de ITSE anula un acta registrada con un error. Queda como constancia, con sus
 * fotos, pero deja de contar: el expediente vuelve a esperar esa visita y el inspector
 * asignado registra el acta correcta.
 */
final readonly class AnularActaUseCase
{
    public const MOTIVO_MINIMO = 10;

    public const MOTIVO_MAXIMO = 500;

    public function __construct(
        private ActaInspeccionRepositoryInterface $actas,
        private AsignacionInspeccionRepositoryInterface $asignaciones,
    ) {}

    /** @return array<string, mixed> el acta anulada */
    public function execute(int $actaId, string $motivo, ?string $documento, int $usuarioId): array
    {
        $motivo = trim($motivo);
        $largo = mb_strlen($motivo);
        if ($largo < self::MOTIVO_MINIMO || $largo > self::MOTIVO_MAXIMO) {
            throw ActaInvalida::motivoAnulacion(self::MOTIVO_MINIMO, self::MOTIVO_MAXIMO);
        }

        $acta = $this->actas->buscar($actaId) ?? throw ActaNoEncontrada::id($actaId);
        $numeroActa = (string) $acta['numero_acta'];
        if ($acta['anulada']) {
            throw ActaInvalida::yaAnulada($numeroActa);
        }

        // Sobre el dictamen ya se resolvió: anularlo dejaría un certificado o denegatoria sin sustento
        $expedienteId = (int) $acta['expediente_id'];
        if (! $this->asignaciones->expedientePendienteDeResolucion($expedienteId)) {
            throw ActaInvalida::expedienteResuelto((int) $acta['numero_expediente']);
        }

        // Anular la 1.ª visita con una reinspección vigente dejaría la reinspección sin base
        $vigentes = $this->actas->deExpediente($expedienteId, soloVigentes: true);
        if ((int) $vigentes[array_key_last($vigentes)]['id'] !== $actaId) {
            throw ActaInvalida::noEsLaUltima($numeroActa);
        }

        $documento = $documento === null || trim($documento) === '' ? null : trim($documento);
        if (! $this->actas->anular($actaId, $motivo, $documento, $usuarioId)) {
            throw ActaInvalida::yaAnulada($numeroActa);
        }

        return (array) $this->actas->buscar($actaId);
    }
}
