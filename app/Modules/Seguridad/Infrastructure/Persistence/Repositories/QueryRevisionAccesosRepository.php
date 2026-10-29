<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Infrastructure\Persistence\Repositories;

use App\Modules\Seguridad\Domain\Contracts\RevisionAccesosRepositoryInterface;
use App\Modules\Seguridad\Domain\ValueObjects\PersonalSinAccesos;
use Illuminate\Support\Facades\DB;

final readonly class QueryRevisionAccesosRepository implements RevisionAccesosRepositoryInterface
{
    public function personalSinAccesos(): array
    {
        $filas = DB::table('auth_user as u')
            ->leftJoin('usuarios_perfiles as p', 'p.user_id', '=', 'u.id')
            ->where('u.is_active', true)
            ->where('u.is_staff', true)
            ->where('u.is_superuser', false) // el superusuario tiene todos los accesos
            ->where(fn ($q) => $q
                ->whereNull('p.id')
                ->orWhere(fn ($s) => $s->where('p.expedientes', false)->where('p.licencias', false)
                    ->where('p.itse', false)->where('p.admin', false)
                    // Un inspector o un usuario solo de reportes sí tiene a qué entrar
                    ->where('p.es_inspector', false)->where('p.reportes_completos', false)))
            ->orderBy('u.username')
            ->get(['u.id', 'u.username', 'u.first_name', 'u.last_name', 'p.id as perfil_id']);

        return array_values($filas->map(fn (object $f) => new PersonalSinAccesos(
            id: (int) $f->id,
            username: (string) $f->username,
            nombre: trim($f->first_name.' '.$f->last_name),
            motivo: $f->perfil_id === null ? PersonalSinAccesos::SIN_PERFIL : PersonalSinAccesos::SIN_ACCESOS,
        ))->all());
    }
}
