<?php
declare(strict_types=1);

namespace App\Shared\Infrastructure\Concerns;

use RuntimeException;

trait ReadOnlyTrait
{
    public static function bootReadOnlyTrait(): void
    {
        static::creating(fn () => throw new RuntimeException(static::class . ' es read-only. Laravel no puede escribir en tablas de Django.'));
        static::updating(fn () => throw new RuntimeException(static::class . ' es read-only. Laravel no puede actualizar tablas de Django.'));
        static::deleting(fn () => throw new RuntimeException(static::class . ' es read-only. Laravel no puede eliminar de tablas de Django.'));
    }
}
