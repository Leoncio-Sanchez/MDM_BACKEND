<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Infrastructure\Persistence\Repositories;

use App\Modules\ConsultaPublica\Domain\Contracts\CuentaCiudadanoRepositoryInterface;
use App\Modules\ConsultaPublica\Domain\Exceptions\CuentaCiudadanoInvalida;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final readonly class QueryCuentaCiudadanoRepository implements CuentaCiudadanoRepositoryInterface
{
    public function esParteDeExpediente(string $documento, int $numeroExpediente): bool
    {
        $personas = DB::table('personas_documentos')->select('persona_id')->whereRaw('UPPER(numero_documento) = ?', [$documento]);

        return DB::table('expedientes as e')
            ->where('e.numero_expediente', $numeroExpediente)
            ->where(fn ($q) => $q->whereIn('e.solicitante_id', $personas)->orWhereIn('e.representante_id', $personas))
            ->exists();
    }

    public function existeCuenta(string $documento): bool
    {
        return DB::table('ciudadanos_cuentas')->where('documento', $documento)->exists();
    }

    public function crear(string $documento, string $hashClave): void
    {
        try {
            $this->insertar($documento, $hashClave);
        } catch (QueryException $e) {
            // Dos registros simultáneos del mismo documento: el índice único detiene el segundo
            if (($e->errorInfo[0] ?? null) === '23505') {
                throw CuentaCiudadanoInvalida::yaExiste();
            }
            throw $e;
        }
    }

    private function insertar(string $documento, string $hashClave): void
    {
        DB::transaction(function () use ($documento, $hashClave): void {
            $usuarioId = (int) DB::table('auth_user')->insertGetId([
                'username' => self::PREFIJO_USUARIO.$documento,
                'password' => $hashClave,
                'first_name' => '', 'last_name' => '', 'email' => '',
                'is_superuser' => false, 'is_staff' => false, 'is_active' => true,
                'date_joined' => now(),
            ]);
            DB::table('ciudadanos_cuentas')->insert([
                'user_id' => $usuarioId,
                'documento' => $documento,
                'fecha_registro' => now(),
            ]);
        });
    }

    public function cambiarClave(string $documento, string $hashClave): void
    {
        $usuarioId = DB::table('ciudadanos_cuentas')->where('documento', $documento)->value('user_id');
        DB::transaction(function () use ($usuarioId, $documento, $hashClave): void {
            DB::table('auth_user')->where('id', $usuarioId)->update(['password' => $hashClave]);
            DB::table('ciudadanos_cuentas')->where('documento', $documento)->update(['fecha_restablecimiento' => now()]);
        });
    }

    public function documentoDelUsuario(int $usuarioId): ?string
    {
        $documento = DB::table('ciudadanos_cuentas')->where('user_id', $usuarioId)->value('documento');

        return $documento === null ? null : (string) $documento;
    }
}
