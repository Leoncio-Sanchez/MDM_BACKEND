<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\ValueObjects;

use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;

/** Inspector, local y programación de una inspección. */
final readonly class DatosAsignacion
{
    public string $nombreComercial;

    public string $direccion;

    public ?string $indicaciones;

    public function __construct(
        public int $inspectorId,
        string $nombreComercial,
        string $direccion,
        public ?string $fechaProgramada = null,
        ?string $indicaciones = null,
    ) {
        $this->nombreComercial = mb_strtoupper(trim($nombreComercial));
        $this->direccion = mb_strtoupper(trim($direccion));
        $indicaciones = $indicaciones === null ? '' : trim($indicaciones);
        $this->indicaciones = $indicaciones === '' ? null : $indicaciones;

        if ($this->nombreComercial === '' || $this->direccion === '') {
            throw new AsignacionInvalida('Indique el nombre comercial y la dirección del local a inspeccionar.');
        }
    }
}
