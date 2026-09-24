<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Domain\Contracts;

use Illuminate\Http\UploadedFile;

interface ArchivoManagerInterface
{
    /**
     * Guarda un archivo físico en disco y devuelve los metadatos.
     *
     * NO persiste en BD. Eso lo hace el repositorio dentro de la transacción.
     *
     * @param  string  $carpeta  Carpeta relativa (ej: "expedientes/27").
     * @return array{uuid: string, nombre_original: string, nombre_archivo: string, ruta_archivo: string, extension: string, tamanio_bytes: int}
     */
    public function guardar(UploadedFile $file, string $carpeta): array;

    /**
     * Elimina un archivo físico del disco.
     *
     * No lanza excepción si el archivo no existe.
     */
    public function eliminar(string $rutaRelativa): void;

    /**
     * Verifica si un archivo físico existe en disco.
     */
    public function existe(string $rutaRelativa): bool;

    /**
     * Devuelve el contenido binario o stream de un archivo.
     *
     * @return resource|string
     */
    public function leer(string $rutaRelativa): mixed;
}
