<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Storage;

use App\Modules\Archivos\Domain\Contracts\ArchivoManagerInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class LocalStorageArchivoManager implements ArchivoManagerInterface
{
    public function __construct(
        private string $disk = 'media',
    ) {}

    /**
     * Guarda un archivo físico en disco y devuelve los metadatos.
     *
     * @param  string  $carpeta  Carpeta relativa (ej: "expedientes/27").
     * @return array{uuid: string, nombre_original: string, nombre_archivo: string, ruta_archivo: string, extension: string, tamanio_bytes: int}
     */
    public function guardar(UploadedFile $file, string $carpeta): array
    {
        $uuid = (string) Str::uuid();
        $nombreOriginal = $file->getClientOriginalName();
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $nombreArchivo = $extension !== '' ? "{$uuid}.{$extension}" : $uuid;
        $carpetaLimpia = trim($carpeta, '/');
        $rutaArchivo = "{$carpetaLimpia}/{$nombreArchivo}";

        $disk = Storage::disk($this->disk);
        $disk->putFileAs($carpetaLimpia, $file, $nombreArchivo);

        return [
            'uuid' => $uuid,
            'nombre_original' => $nombreOriginal,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaArchivo,
            'extension' => $extension,
            'tamanio_bytes' => $file->getSize() ?: 0,
        ];
    }

    /**
     * Elimina un archivo físico del disco.
     */
    public function eliminar(string $rutaRelativa): void
    {
        $disk = Storage::disk($this->disk);
        if ($disk->exists($rutaRelativa)) {
            $disk->delete($rutaRelativa);
        }
    }

    /**
     * Verifica si un archivo físico existe en disco.
     */
    public function existe(string $rutaRelativa): bool
    {
        return Storage::disk($this->disk)->exists($rutaRelativa);
    }

    /**
     * Devuelve el contenido binario o stream de un archivo.
     *
     * @return resource|string
     */
    public function leer(string $rutaRelativa): mixed
    {
        $stream = Storage::disk($this->disk)->readStream($rutaRelativa);

        if ($stream !== null) {
            return $stream;
        }

        return Storage::disk($this->disk)->get($rutaRelativa) ?? '';
    }
}
