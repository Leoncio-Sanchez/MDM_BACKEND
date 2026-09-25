<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExpedienteArchivo;
use App\Models\ItseArchivo;
use App\Models\LicenciaArchivo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ArchivoService
{
    private string $disk = 'media';

    /**
     * @return Collection<int, ExpedienteArchivo>|Collection<int, ItseArchivo>|Collection<int, LicenciaArchivo>
     */
    public function listar(string $entidad, int $parentId): Collection
    {
        $this->verificarExistenciaPadre($entidad, $parentId);

        return match ($entidad) {
            'expediente' => ExpedienteArchivo::where('expediente_id', $parentId)
                ->orderBy('fecha_digitacion', 'asc')
                ->get(),
            'itse' => ItseArchivo::where('itse_id', $parentId)
                ->orderBy('fecha_digitacion', 'asc')
                ->get(),
            default => LicenciaArchivo::where('licencia_funcionamiento_id', $parentId)
                ->orderBy('fecha_digitacion', 'asc')
                ->get(),
        };
    }

    public function subir(string $entidad, int $parentId, UploadedFile $file, int $usuarioId): Model
    {
        $this->verificarExistenciaPadre($entidad, $parentId);

        $carpeta = match ($entidad) {
            'expediente' => "expedientes/{$parentId}",
            'itse' => "itse/{$parentId}",
            default => "licencias-funcionamiento/{$parentId}",
        };

        $uuid = (string) Str::uuid();
        $nombreOriginal = $file->getClientOriginalName();
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $nombreArchivo = $extension !== '' ? "{$uuid}.{$extension}" : $uuid;
        $rutaArchivo = "{$carpeta}/{$nombreArchivo}";
        $tamanioBytes = (int) ($file->getSize() ?: 0);

        Storage::disk($this->disk)->putFileAs($carpeta, $file, $nombreArchivo);

        $fechaDigitacion = Carbon::now('UTC')->toDateTimeString();

        return match ($entidad) {
            'expediente' => ExpedienteArchivo::create([
                'uuid' => $uuid,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'extension' => $extension,
                'tamanio_bytes' => $tamanioBytes,
                'fecha_digitacion' => $fechaDigitacion,
                'expediente_id' => $parentId,
                'usuario_id' => $usuarioId,
            ]),
            'itse' => ItseArchivo::create([
                'uuid' => $uuid,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'extension' => $extension,
                'tamanio_bytes' => $tamanioBytes,
                'fecha_digitacion' => $fechaDigitacion,
                'itse_id' => $parentId,
                'usuario_id' => $usuarioId,
            ]),
            default => LicenciaArchivo::create([
                'uuid' => $uuid,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'extension' => $extension,
                'tamanio_bytes' => $tamanioBytes,
                'fecha_digitacion' => $fechaDigitacion,
                'licencia_funcionamiento_id' => $parentId,
                'usuario_id' => $usuarioId,
            ]),
        };
    }

    public function eliminar(string $entidad, int $archivoId): void
    {
        $archivo = match ($entidad) {
            'expediente' => ExpedienteArchivo::find($archivoId),
            'itse' => ItseArchivo::find($archivoId),
            default => LicenciaArchivo::find($archivoId),
        };

        if (! $archivo) {
            throw new HttpResponseException(
                response()->json(['error' => 'Archivo no encontrado.'], 404)
            );
        }

        $disk = Storage::disk($this->disk);
        if ($disk->exists($archivo->ruta_archivo)) {
            $disk->delete($archivo->ruta_archivo);
        }

        $archivo->delete();
    }

    public function descargar(string $entidad, string $uuid): SymfonyResponse
    {
        $archivo = match ($entidad) {
            'expediente' => ExpedienteArchivo::where('uuid', $uuid)->first(),
            'itse' => ItseArchivo::where('uuid', $uuid)->first(),
            default => LicenciaArchivo::where('uuid', $uuid)->first(),
        };

        if (! $archivo) {
            throw new HttpResponseException(
                response()->json(['error' => 'Archivo no encontrado.'], 404)
            );
        }

        $disk = Storage::disk($this->disk);
        if (! $disk->exists($archivo->ruta_archivo)) {
            throw new HttpResponseException(
                response()->json(['error' => 'El archivo físico no fue encontrado en el servidor de almacenamiento.'], 404)
            );
        }

        $mime = $this->guessMimeType($archivo->nombre_original);
        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$archivo->nombre_original.'"',
            'Content-Length' => (string) $archivo->tamanio_bytes,
        ];

        $stream = $disk->readStream($archivo->ruta_archivo);
        if (is_resource($stream)) {
            return response()->stream(function () use ($stream): void {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, $headers);
        }

        return response((string) $disk->get($archivo->ruta_archivo), 200, $headers);
    }

    private function verificarExistenciaPadre(string $entidad, int $parentId): void
    {
        $existe = match ($entidad) {
            'expediente' => DB::table('expedientes')->where('id', $parentId)->exists(),
            'itse' => DB::table('itse')->where('id', $parentId)->exists(),
            default => DB::table('licencias_funcionamiento')->where('id', $parentId)->exists(),
        };

        if (! $existe) {
            $nombreModelo = match ($entidad) {
                'expediente' => 'Expediente',
                'itse' => 'Itse',
                default => 'LicenciaFuncionamiento',
            };

            throw new HttpResponseException(
                response()->json(['detail' => "No {$nombreModelo} matches the given query."], 404)
            );
        }
    }

    public function guessMimeType(string $nombreOriginal): string
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
