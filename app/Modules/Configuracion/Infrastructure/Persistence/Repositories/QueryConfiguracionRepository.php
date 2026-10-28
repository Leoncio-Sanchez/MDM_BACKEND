<?php

declare(strict_types=1);

namespace App\Modules\Configuracion\Infrastructure\Persistence\Repositories;

use App\Modules\Configuracion\Domain\Contracts\ConfiguracionRepositoryInterface;
use App\Modules\Configuracion\Domain\ValueObjects\ConfiguracionInforme;
use Illuminate\Support\Facades\DB;

final readonly class QueryConfiguracionRepository implements ConfiguracionRepositoryInterface
{
    public function informe(): ConfiguracionInforme
    {
        /** @var array<string, string> $valores */
        $valores = DB::table('configuracion_sistema')
            ->whereIn('clave', array_values(ConfiguracionInforme::CLAVES))
            ->pluck('valor', 'clave')
            ->map(fn ($v) => (string) $v)
            ->all();

        return ConfiguracionInforme::desdeClaves($valores);
    }

    public function guardarInforme(ConfiguracionInforme $configuracion, int $usuarioId): void
    {
        DB::transaction(function () use ($configuracion, $usuarioId): void {
            foreach ($configuracion->aClaves() as $clave => $valor) {
                DB::table('configuracion_sistema')->updateOrInsert(
                    ['clave' => $clave],
                    ['valor' => $valor, 'fecha_actualizacion' => now(), 'usuario_id' => $usuarioId],
                );
            }
        });
    }
}
