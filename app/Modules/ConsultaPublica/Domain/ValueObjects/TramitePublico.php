<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Domain\ValueObjects;

use DateTimeImmutable;

/**
 * Lo que un ciudadano puede ver de un trámite sin iniciar sesión.
 *
 * Por la Ley de Protección de Datos Personales NO incluye DNI/RUC, nombres de personas,
 * direcciones, teléfonos ni correos: solo datos del trámite y del negocio.
 */
final readonly class TramitePublico
{
    /** @param list<string> $giros */
    public function __construct(
        public TipoTramite $tipo,
        public int $numero,
        public string $estado,
        public ?string $nombreComercial = null,
        public ?string $procedimiento = null,
        public array $giros = [],
        public ?DateTimeImmutable $fechaEmision = null,
        public ?DateTimeImmutable $fechaVencimiento = null,
        public bool $vigenciaIndeterminada = false,
    ) {}
}
