<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Domain\ValueObjects;

use App\Modules\Expedientes\Domain\Exceptions\ExpedienteInvalido;
use DateTimeImmutable;

/** Constancia de anulación de un expediente: motivo, sustento, fecha y quién la hizo. */
final readonly class AnulacionExpediente
{
    public const MOTIVO_MINIMO = 10;

    public const MOTIVO_MAXIMO = 500;

    public function __construct(
        public string $motivo,
        public ?string $documento,
        public DateTimeImmutable $fechaAnulacion,
        public int $usuarioId,
        /** Solo al leer: nombre de quien anuló */
        public ?string $usuarioNombre = null,
    ) {
        $largo = mb_strlen(trim($motivo));
        if ($largo < self::MOTIVO_MINIMO || $largo > self::MOTIVO_MAXIMO) {
            throw ExpedienteInvalido::motivoAnulacion(self::MOTIVO_MINIMO, self::MOTIVO_MAXIMO);
        }
    }
}
