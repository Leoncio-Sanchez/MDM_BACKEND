<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Persistence\Repositories;

use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
use App\Modules\Archivos\Domain\Entities\InactivacionArchivo;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class AbstractEloquentArchivoRepository implements ArchivoRepositoryInterface
{
    /**
     * @return class-string<Model>
     */
    abstract protected function getModelClass(): string;

    abstract protected function getParentForeignKey(): string;

    /** Tabla del registro al que pertenecen los archivos (expedientes, itse, …). */
    abstract protected function getParentTable(): string;

    public function existePadre(int $parentId): bool
    {
        return DB::table($this->getParentTable())->where('id', $parentId)->exists();
    }

    public function crear(ArchivoMetadata $metadata): ArchivoMetadata
    {
        $modelClass = $this->getModelClass();

        $attributes = [
            'uuid' => $metadata->uuid,
            'nombre_original' => $metadata->nombreOriginal,
            'nombre_archivo' => $metadata->nombreArchivo,
            'ruta_archivo' => $metadata->rutaArchivo,
            'extension' => $metadata->extension,
            'tamanio_bytes' => $metadata->tamanioBytes,
            'fecha_digitacion' => $metadata->fechaDigitacion->format('Y-m-d H:i:sP'),
            $this->getParentForeignKey() => $metadata->parentId,
            'usuario_id' => $metadata->usuarioId,
        ];

        /** @var Model $model */
        $model = $modelClass::query()->create($attributes);

        return $this->toEntity($model, null);
    }

    public function obtenerPorId(int $id): ?ArchivoMetadata
    {
        $modelClass = $this->getModelClass();
        /** @var Model|null $model */
        $model = $modelClass::query()->find($id);

        return $model !== null ? $this->conInactivaciones([$model])[0] : null;
    }

    public function obtenerPorUuid(string $uuid): ?ArchivoMetadata
    {
        $modelClass = $this->getModelClass();
        /** @var Model|null $model */
        $model = $modelClass::query()->where('uuid', $uuid)->first();

        return $model !== null ? $this->conInactivaciones([$model])[0] : null;
    }

    /**
     * Activos e inactivos: los dados de baja se siguen mostrando (marcados) para auditoría.
     *
     * @return ArchivoMetadata[]
     */
    public function listarPorParent(int $parentId): array
    {
        $modelClass = $this->getModelClass();

        // REGLA CRÍTICA: ordenar por fecha_digitacion ASC (replicando Django exacto)
        $models = $modelClass::query()
            ->where($this->getParentForeignKey(), $parentId)
            ->orderBy('fecha_digitacion', 'asc')
            ->get();

        return $this->conInactivaciones($models->all());
    }

    public function inactivar(int $id, InactivacionArchivo $inactivacion): void
    {
        DB::table($this->tablaInactivaciones())->insert([
            'archivo_id' => $id,
            'motivo' => trim($inactivacion->motivo),
            'fecha_inactivacion' => $inactivacion->fechaInactivacion->format('Y-m-d H:i:sP'),
            'usuario_id' => $inactivacion->usuarioId,
        ]);
    }

    /** expedientes_archivos → expedientes_archivos_inactivaciones (ver migración) */
    private function tablaInactivaciones(): string
    {
        $modelClass = $this->getModelClass();

        return (new $modelClass)->getTable().'_inactivaciones';
    }

    /**
     * Una sola consulta para las inactivaciones de todos los archivos.
     *
     * @param  array<int, Model>  $models
     * @return list<ArchivoMetadata>
     */
    private function conInactivaciones(array $models): array
    {
        $ids = array_map(fn (Model $m) => (int) $m->getAttribute('id'), $models);
        $inactivaciones = $ids === [] ? collect() : DB::table($this->tablaInactivaciones().' as ia')
            ->join('auth_user as u', 'u.id', '=', 'ia.usuario_id')
            ->whereIn('ia.archivo_id', $ids)
            ->select(['ia.archivo_id', 'ia.motivo', 'ia.fecha_inactivacion', 'ia.usuario_id'])
            ->selectRaw("COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.first_name, u.last_name)), ''), u.username) AS usuario_nombre")
            ->get()
            ->keyBy('archivo_id');

        return array_values(array_map(function (Model $m) use ($inactivaciones): ArchivoMetadata {
            /** @var \stdClass|null $fila */
            $fila = $inactivaciones->get((int) $m->getAttribute('id'));

            return $this->toEntity($m, $fila === null ? null : new InactivacionArchivo(
                motivo: (string) $fila->motivo,
                fechaInactivacion: new DateTimeImmutable((string) $fila->fecha_inactivacion),
                usuarioId: (int) $fila->usuario_id,
                usuarioNombre: (string) $fila->usuario_nombre,
            ));
        }, $models));
    }

    protected function toEntity(Model $model, ?InactivacionArchivo $inactivacion): ArchivoMetadata
    {
        $rawFecha = $model->getAttribute('fecha_digitacion');

        if ($rawFecha instanceof DateTimeInterface) {
            $fechaDigitacion = DateTimeImmutable::createFromInterface($rawFecha);
        } else {
            $fechaDigitacion = new DateTimeImmutable((string) $rawFecha);
        }

        return new ArchivoMetadata(
            id: (int) $model->getAttribute('id'),
            uuid: (string) $model->getAttribute('uuid'),
            nombreOriginal: (string) $model->getAttribute('nombre_original'),
            nombreArchivo: (string) $model->getAttribute('nombre_archivo'),
            rutaArchivo: (string) $model->getAttribute('ruta_archivo'),
            extension: (string) $model->getAttribute('extension'),
            tamanioBytes: (int) $model->getAttribute('tamanio_bytes'),
            fechaDigitacion: $fechaDigitacion,
            parentId: (int) $model->getAttribute($this->getParentForeignKey()),
            usuarioId: (int) $model->getAttribute('usuario_id'),
            inactivacion: $inactivacion,
        );
    }
}
