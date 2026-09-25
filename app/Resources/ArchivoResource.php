<?php

declare(strict_types=1);

namespace App\Resources;

use App\Models\ExpedienteArchivo;
use App\Models\ItseArchivo;
use App\Models\LicenciaArchivo;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ArchivoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ExpedienteArchivo|ItseArchivo|LicenciaArchivo|array<string, mixed>|object $model */
        $model = $this->resource;

        $id = (int) data_get($model, 'id');
        $uuid = (string) data_get($model, 'uuid');
        $nombreOriginal = (string) data_get($model, 'nombre_original');
        $nombreArchivo = (string) data_get($model, 'nombre_archivo');
        $rutaArchivo = (string) data_get($model, 'ruta_archivo');
        $extension = (string) data_get($model, 'extension');
        $tamanioBytes = (int) data_get($model, 'tamanio_bytes');

        $fechaDigitacion = data_get($model, 'fecha_digitacion');
        $fechaDigitacionStr = null;
        if ($fechaDigitacion !== null && $fechaDigitacion !== '') {
            $c = Carbon::parse($fechaDigitacion)->setTimezone(new DateTimeZone('America/Lima'));
            $fmt = ((int) $c->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP';
            $fechaDigitacionStr = $c->format($fmt);
        }

        $usuarioId = data_get($model, 'usuario_id') ?? data_get($model, 'usuario');
        if (is_object($usuarioId) && isset($usuarioId->id)) {
            $usuarioId = $usuarioId->id;
        }

        $data = [
            'id' => $id,
            'uuid' => $uuid,
            'nombre_original' => $nombreOriginal,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaArchivo,
            'extension' => $extension,
            'tamanio_bytes' => $tamanioBytes,
            'fecha_digitacion' => $fechaDigitacionStr,
        ];

        // Determinar la llave del parent según el tipo de entidad
        if ($model instanceof ExpedienteArchivo || data_get($model, 'expediente_id') !== null || data_get($model, 'expediente') !== null) {
            $data['expediente'] = (int) (data_get($model, 'expediente_id') ?? data_get($model, 'expediente'));
        } elseif ($model instanceof ItseArchivo || data_get($model, 'itse_id') !== null || data_get($model, 'itse') !== null) {
            $data['itse'] = (int) (data_get($model, 'itse_id') ?? data_get($model, 'itse'));
        } elseif ($model instanceof LicenciaArchivo || data_get($model, 'licencia_funcionamiento_id') !== null || data_get($model, 'licencia_funcionamiento') !== null) {
            $data['licencia_funcionamiento'] = (int) (data_get($model, 'licencia_funcionamiento_id') ?? data_get($model, 'licencia_funcionamiento'));
        }

        $data['usuario'] = (int) $usuarioId;

        return $data;
    }
}
