<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\ValueObjects;

use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;

/** Inspector responsable, acompañantes, local y programación de una inspección. */
final readonly class DatosAsignacion
{
    public const MAXIMO_ACOMPANANTES = 3;

    public string $nombreComercial;

    public string $direccion;

    public ?string $indicaciones;

    /** @var list<int> */
    public array $acompanantes;

    /** @param  array<int, int>  $acompanantes */
    public function __construct(
        public int $inspectorId,
        string $nombreComercial,
        string $direccion,
        public ?string $fechaProgramada = null,
        ?string $indicaciones = null,
        array $acompanantes = [],
    ) {
        $this->nombreComercial = mb_strtoupper(trim($nombreComercial));
        $this->direccion = mb_strtoupper(trim($direccion));
        $indicaciones = $indicaciones === null ? '' : trim($indicaciones);
        $this->indicaciones = $indicaciones === '' ? null : $indicaciones;
        $this->acompanantes = array_values(array_unique(array_map('intval', $acompanantes)));

        if ($this->nombreComercial === '' || $this->direccion === '') {
            throw new AsignacionInvalida('Indique el nombre comercial y la dirección del local a inspeccionar.');
        }
        if (in_array($this->inspectorId, $this->acompanantes, true)) {
            throw AsignacionInvalida::responsableComoAcompanante();
        }
        if (count($this->acompanantes) > self::MAXIMO_ACOMPANANTES) {
            throw AsignacionInvalida::demasiadosAcompanantes(self::MAXIMO_ACOMPANANTES);
        }
    }
}
