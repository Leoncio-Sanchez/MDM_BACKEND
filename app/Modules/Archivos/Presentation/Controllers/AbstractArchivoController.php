<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Presentation\Controllers;

use App\Modules\Archivos\Application\DTOs\SubirArchivoDTO;
use App\Modules\Archivos\Application\UseCases\DescargarArchivoUseCase;
use App\Modules\Archivos\Application\UseCases\EliminarArchivoUseCase;
use App\Modules\Archivos\Application\UseCases\ListarArchivosUseCase;
use App\Modules\Archivos\Application\UseCases\SubirArchivoUseCase;
use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Exceptions\ArchivoFisicoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ArchivoNoEncontrado;
use App\Modules\Archivos\Domain\Exceptions\ErrorAlGuardarArchivo;
use App\Modules\Archivos\Domain\Exceptions\RegistroPadreNoEncontrado;
use App\Modules\Archivos\Domain\PoliticaArchivos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class AbstractArchivoController
{
    public function __construct(
        protected ListarArchivosUseCase $listar,
        protected SubirArchivoUseCase $subir,
        protected EliminarArchivoUseCase $eliminar,
        protected DescargarArchivoUseCase $descargar,
    ) {}

    abstract protected function getRepository(): ArchivoRepositoryInterface;

    abstract protected function getCarpetaBase(): string;

    abstract protected function getParentFieldName(): string;

    /**
     * GET /{modulo}/{pk}/archivos/ — Listar
     */
    public function listar(Request $request, int $pk): JsonResponse
    {
        $archivos = $this->listar->execute($this->getRepository(), $pk);

        return response()->json(
            array_map(fn (ArchivoMetadata $a) => $this->serializeMetadata($a), $archivos),
        );
    }

    /**
     * POST /{modulo}/{pk}/archivos/ — Subir
     */
    public function subir(Request $request, int $pk): JsonResponse
    {
        // "mimes" revisa el contenido real del archivo, no solo la extensión del nombre
        $validator = Validator::make($request->all(), [
            'archivo' => ['required', 'file', 'max:'.PoliticaArchivos::TAMANIO_MAXIMO_KB, 'mimes:'.implode(',', PoliticaArchivos::EXTENSIONES)],
        ], [
            'archivo.required' => 'No se ha proporcionado ningún archivo.',
            'archivo.file' => 'El archivo no es válido.',
            'archivo.max' => 'El archivo supera el tamaño máximo de '.(PoliticaArchivos::TAMANIO_MAXIMO_KB / 1024).' MB.',
            'archivo.mimes' => 'Tipo de archivo no permitido. Se aceptan PDF, imágenes JPG o PNG, Word y Excel.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        /** @var UploadedFile $archivo */
        $archivo = $request->file('archivo');
        $usuario = $request->user();
        $usuarioId = $usuario !== null ? (int) $usuario->getAuthIdentifier() : 1;

        $dto = new SubirArchivoDTO(
            parentId: $pk,
            archivo: $archivo,
            usuarioId: $usuarioId,
            carpetaBase: $this->getCarpetaBase(),
        );

        try {
            $metadata = $this->subir->execute($this->getRepository(), $dto);
        } catch (RegistroPadreNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (ErrorAlGuardarArchivo $e) {
            // El detalle (puede traer SQL o rutas internas) queda en el log, no se muestra
            report($e);

            return response()->json(['error' => 'No se pudo guardar el archivo. Intente nuevamente.'], 500);
        }

        return response()->json(
            $this->serializeMetadata($metadata),
            201,
        );
    }

    /**
     * DELETE /{modulo}/archivos/{pk}/ — Eliminar
     */
    public function eliminar(int $pk): SymfonyResponse
    {
        try {
            $this->eliminar->execute($this->getRepository(), $pk);
        } catch (ArchivoNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        return response()->noContent();
    }

    /**
     * GET /{modulo}/archivos/{uuid}/descargar/ — Descargar
     */
    public function descargar(string $uuid): SymfonyResponse
    {
        try {
            $resultado = $this->descargar->execute($this->getRepository(), $uuid);
        } catch (ArchivoNoEncontrado|ArchivoFisicoNoEncontrado $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        $metadata = $resultado['metadata'];
        $contenido = $resultado['contenido'];
        $mime = $this->guessMimeType($metadata->nombreOriginal);

        if (is_resource($contenido)) {
            return response()->stream(function () use ($contenido) {
                fpassthru($contenido);
                if (is_resource($contenido)) {
                    fclose($contenido);
                }
            }, 200, $this->cabecerasDescarga($metadata, $mime));
        }

        return response((string) $contenido, 200, $this->cabecerasDescarga($metadata, $mime));
    }

    /**
     * PDF e imágenes se abren en el navegador; Word y Excel se descargan.
     * El nombre se limpia para que no pueda romper la cabecera (comillas, saltos de línea)
     * y "nosniff" evita que el navegador interprete el archivo como otro tipo.
     *
     * @return array<string, string>
     */
    private function cabecerasDescarga(ArchivoMetadata $metadata, string $mime): array
    {
        $tipo = PoliticaArchivos::seAbreEnNavegador($metadata->extension) ? 'inline' : 'attachment';
        $nombre = trim((string) preg_replace('/[\x00-\x1F\x7F"\\\\]/u', '', $metadata->nombreOriginal)) ?: 'archivo';
        $ascii = (string) preg_replace('/[^\x20-\x7E]/u', '_', $nombre);
        $disposicion = "{$tipo}; filename=\"{$ascii}\"".($ascii !== $nombre ? "; filename*=UTF-8''".rawurlencode($nombre) : '');

        return [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposicion,
            'Content-Length' => (string) $metadata->tamanioBytes,
            'X-Content-Type-Options' => 'nosniff',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeMetadata(ArchivoMetadata $m): array
    {
        return [
            'id' => $m->id,
            'uuid' => $m->uuid,
            'nombre_original' => $m->nombreOriginal,
            'nombre_archivo' => $m->nombreArchivo,
            'ruta_archivo' => $m->rutaArchivo,
            'extension' => $m->extension,
            'tamanio_bytes' => $m->tamanioBytes,
            'fecha_digitacion' => $m->fechaDigitacion->format('Y-m-d\TH:i:sP'),
            $this->getParentFieldName() => $m->parentId,
            'usuario' => $m->usuarioId,
        ];
    }

    protected function guessMimeType(string $nombreOriginal): string
    {
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            'json' => 'application/json',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }
}
