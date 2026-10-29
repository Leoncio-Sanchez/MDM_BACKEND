<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Entities;

use App\Modules\Archivos\Domain\Exceptions\MotivoInactivacionInvalido;
use DateTimeImmutable;

/** Constancia de que un archivo adjunto se dio de baja: el archivo se conserva para auditoría. */
final readonly class InactivacionArchivo
{
    public const MOTIVO_MINIMO = 10;

    public const MOTIVO_MAXIMO = 500;

    public function __construct(
        public string $motivo,
        public DateTimeImmutable $fechaInactivacion,
        public int $usuarioId,
        /** Solo al leer: nombre de quien lo inactivó */
        public ?string $usuarioNombre = null,
    ) {
        $largo = mb_strlen(trim($motivo));
        if ($largo < self::MOTIVO_MINIMO || $largo > self::MOTIVO_MAXIMO) {
            throw MotivoInactivacionInvalido::largo(self::MOTIVO_MINIMO, self::MOTIVO_MAXIMO);
        }
    }
}
