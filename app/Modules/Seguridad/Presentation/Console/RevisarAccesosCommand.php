<?php

declare(strict_types=1);

namespace App\Modules\Seguridad\Presentation\Console;

use App\Modules\Seguridad\Application\UseCases\ListarPersonalSinAccesosUseCase;
use App\Modules\Seguridad\Domain\ValueObjects\PersonalSinAccesos;
use Illuminate\Console\Command;

/**
 * Lista al personal que al iniciar sesión solo vería el dashboard.
 * Conviene correrlo antes del pase a producción y después de cargar usuarios.
 */
final class RevisarAccesosCommand extends Command
{
    protected $signature = 'usuarios:revisar-accesos';

    protected $description = 'Lista al personal activo que no tiene ningún acceso del perfil (Expedientes, Licencias, ITSE o Admin)';

    public function handle(ListarPersonalSinAccesosUseCase $listar): int
    {
        $personal = $listar->execute();

        if ($personal === []) {
            $this->info('Todo el personal activo tiene al menos un acceso del perfil.');

            return self::SUCCESS;
        }

        $this->warn(count($personal) === 1
            ? '1 persona del personal no tiene accesos: al iniciar sesión solo verá el dashboard.'
            : count($personal).' personas del personal no tienen accesos: al iniciar sesión solo verán el dashboard.');

        $this->table(
            ['ID', 'Usuario', 'Nombre', 'Motivo'],
            array_map(fn (PersonalSinAccesos $p) => [$p->id, $p->username, $p->nombre !== '' ? $p->nombre : '—', $p->motivo], $personal),
        );

        $this->line('Para corregirlo: Administración → Usuarios y Permisos → editar al usuario y marcar sus accesos.');

        return self::SUCCESS;
    }
}
