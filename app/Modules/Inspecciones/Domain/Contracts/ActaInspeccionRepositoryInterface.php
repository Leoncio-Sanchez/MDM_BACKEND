<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Domain\Contracts;

use App\Modules\Inspecciones\Domain\Exceptions\ActaDuplicada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosActa;
use App\Modules\Inspecciones\Domain\ValueObjects\ResultadoActa;
use Illuminate\Http\UploadedFile;

interface ActaInspeccionRepositoryInterface
{
    /**
     * Actas del expediente en orden de visita, sin fotos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function deExpediente(int $expedienteId): array;

    /** @return array<string, mixed>|null el acta con sus fotos (sin contenido) */
    public function buscar(int $actaId): ?array;

    public function existeNumero(string $numeroActa): bool;

    /**
     * Guarda el acta y sus fotos en una sola operación: si algo falla, no queda
     * ni el registro ni los archivos.
     *
     * @param  array<int, array{tipo: string, archivo: UploadedFile}>  $fotos
     *
     * @throws ActaDuplicada
     */
    public function registrar(
        int $asignacionId,
        int $expedienteId,
        int $inspectorId,
        int $visita,
        DatosActa $datos,
        ResultadoActa $resultado,
        ?string $fechaLimiteSubsanacion,
        array $fotos,
    ): int;

    /** @return array{foto: array<string, mixed>, contenido: resource|string}|null */
    public function leerFoto(int $actaId, int $fotoId): ?array;

    /** El inspector ve las actas que registró y las del expediente que tiene asignado */
    public function inspectorPuedeVer(int $actaId, int $inspectorId): bool;
}
