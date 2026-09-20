<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Application\UseCases;

use App\Modules\Licencias\Domain\Contracts\LicenciaArchivoRepositoryInterface;
use App\Modules\Licencias\Domain\Contracts\LicenciaRepositoryInterface;
use App\Modules\Licencias\Domain\Exceptions\LicenciaNoEncontrada;
use App\Modules\Licencias\Domain\Exceptions\LicenciaTieneDependientesError;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final readonly class EliminarLicenciaUseCase
{
    public function __construct(
        private LicenciaRepositoryInterface $licencias,
        private LicenciaArchivoRepositoryInterface $archivos,
    ) {}

    public function execute(int $licenciaId): void
    {
        $rutas = DB::transaction(function () use ($licenciaId): array {
            $licencia = $this->licencias->buscarPorId($licenciaId);
            if (!$licencia) {
                throw LicenciaNoEncontrada::porId($licenciaId);
            }

            if ($this->licencias->tieneLicenciasDependientes($licenciaId)) {
                throw LicenciaTieneDependientesError::porId($licenciaId);
            }

            $rutas = $this->archivos->obtenerRutas($licenciaId);

            $this->licencias->eliminar($licenciaId);

            return $rutas;
        });

        // POST-transacción: borrado físico de archivos
        foreach ($rutas as $ruta) {
            if (Storage::disk('public')->exists($ruta)) {
                Storage::disk('public')->delete($ruta);
            }
        }
    }
}
