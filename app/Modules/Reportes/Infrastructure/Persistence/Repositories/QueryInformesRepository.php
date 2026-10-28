<?php

declare(strict_types=1);

namespace App\Modules\Reportes\Infrastructure\Persistence\Repositories;

use App\Modules\Reportes\Domain\Contracts\InformesRepositoryInterface;
use App\Modules\Reportes\Domain\ValueObjects\InformeEmitido;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final readonly class QueryInformesRepository implements InformesRepositoryInterface
{
    public function emitir(string $tipo, string $desde, string $hasta, int $usuarioId): InformeEmitido
    {
        return DB::transaction(function () use ($tipo, $desde, $hasta, $usuarioId): InformeEmitido {
            // Un solo correlativo a la vez por tipo de informe (dos personas imprimiendo al mismo tiempo
            // no pueden recibir el mismo número). El bloqueo se libera al terminar la transacción.
            DB::statement('SELECT pg_advisory_xact_lock(hashtext(?))', ['informes_emitidos:'.$tipo]);

            $existente = DB::table('informes_emitidos')
                ->where(['tipo' => $tipo, 'periodo_desde' => $desde, 'periodo_hasta' => $hasta])
                ->first();
            if ($existente !== null) {
                return $this->aInforme($existente, esReimpresion: true);
            }

            $anio = (int) CarbonImmutable::now()->setTimezone('America/Lima')->format('Y');
            $numero = (int) DB::table('informes_emitidos')->where(['tipo' => $tipo, 'anio' => $anio])->max('numero') + 1;

            $id = DB::table('informes_emitidos')->insertGetId([
                'tipo' => $tipo, 'anio' => $anio, 'numero' => $numero,
                'periodo_desde' => $desde, 'periodo_hasta' => $hasta,
                'usuario_id' => $usuarioId, 'fecha_emision' => now(),
            ]);

            return $this->aInforme(DB::table('informes_emitidos')->where('id', $id)->first(), esReimpresion: false);
        });
    }

    private function aInforme(?\stdClass $fila, bool $esReimpresion): InformeEmitido
    {
        if ($fila === null) {
            throw new \RuntimeException('No se pudo registrar el informe.');
        }

        return new InformeEmitido(
            tipo: (string) $fila->tipo,
            anio: (int) $fila->anio,
            numero: (int) $fila->numero,
            periodoDesde: (string) $fila->periodo_desde,
            periodoHasta: (string) $fila->periodo_hasta,
            fechaEmision: CarbonImmutable::parse((string) $fila->fecha_emision),
            esReimpresion: $esReimpresion,
        );
    }
}
