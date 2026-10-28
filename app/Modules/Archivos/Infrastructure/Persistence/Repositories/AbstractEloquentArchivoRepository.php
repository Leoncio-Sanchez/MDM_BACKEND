<?php

declare(strict_types=1);

namespace App\Modules\Archivos\Infrastructure\Persistence\Repositories;

use App\Modules\Archivos\Domain\Contracts\ArchivoRepositoryInterface;
use App\Modules\Archivos\Domain\Entities\ArchivoMetadata;
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

        return $this->toEntity($model);
    }

    public function obtenerPorId(int $id): ?ArchivoMetadata
    {
        $modelClass = $this->getModelClass();
        /** @var Model|null $model */
        $model = $modelClass::query()->find($id);

        return $model !== null ? $this->toEntity($model) : null;
    }

    public function obtenerPorUuid(string $uuid): ?ArchivoMetadata
    {
        $modelClass = $this->getModelClass();
        /** @var Model|null $model */
        $model = $modelClass::query()->where('uuid', $uuid)->first();

        return $model !== null ? $this->toEntity($model) : null;
    }

    /**
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

        return $models->map(fn (Model $m) => $this->toEntity($m))->all();
    }

    public function eliminar(int $id): void
    {
        $modelClass = $this->getModelClass();
        $modelClass::query()->where('id', $id)->delete();
    }

    protected function toEntity(Model $model): ArchivoMetadata
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
        );
    }
}
