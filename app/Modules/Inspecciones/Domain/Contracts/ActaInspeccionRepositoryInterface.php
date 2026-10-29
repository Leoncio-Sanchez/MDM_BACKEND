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
     * Actas del expediente en orden de visita (las anuladas antes que la que las reemplazó), sin fotos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function deExpediente(int $expedienteId, bool $soloVigentes = false): array;

    /** @return array<string, mixed>|null el acta con sus fotos (sin contenido) */
    public function buscar(int $actaId): ?array;

    /** Solo entre las actas vigentes: el número de un acta anulada se puede volver a usar */
    public function existeNumero(string $numeroActa): bool;

    /** @return bool false si el acta ya estaba anulada */
    public function anular(int $actaId, string $motivo, ?string $documento, int $usuarioId): bool;

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
