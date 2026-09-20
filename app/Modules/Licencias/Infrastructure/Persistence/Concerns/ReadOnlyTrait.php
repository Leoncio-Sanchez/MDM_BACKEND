<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Infrastructure\Persistence\Concerns;

trait ReadOnlyTrait
{
    public static function bootReadOnlyTrait(): void
    {
        static::creating(fn () => throw new \RuntimeException('Read-only model: no se permite crear'));
        static::updating(fn () => throw new \RuntimeException('Read-only model: no se permite actualizar'));
        static::deleting(fn () => throw new \RuntimeException('Read-only model: no se permite eliminar'));
    }
}
