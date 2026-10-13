<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Domain\Contracts;
use App\Modules\Licencias\Domain\Entities\Licencia;

interface LicenciaRepositoryInterface
{
    /** @return array{items: Licencia[], total: int} */
    public function paginar(int $pagina, int $porPagina): array;
    public function buscarPorId(int $id): ?Licencia;
    public function buscarPorExpedienteId(int $expedienteId): ?Licencia;
    /** @return Licencia[] */
    public function buscarPorFiltro(string $filtro, string $valor): array;
    /** @return Licencia[] */
    public function buscarParaReporte(string $fechaInicio, string $fechaFin): array;
    /** @param int $usuarioId Usuario que registra la licencia (auditoría: usuario_id, fecha_digitacion). */
    public function crear(Licencia $licencia, int $usuarioId): Licencia;
    public function actualizar(Licencia $licencia): Licencia;
    public function eliminar(int $id): void;

    public function existeNumeroLicencia(int $numero, ?int $exceptoId = null): bool;
    public function existeReciboPago(string $numero, ?int $exceptoId = null): bool;
    public function tieneLicenciasDependientes(int $id): bool;
    
    /** @return array<int, array<string, mixed>> */
    public function listarEstados(int $licenciaId): array;
    public function existeEstado(int $licenciaId, int $estadoId): bool;
    public function insertarEstado(int $licenciaId, int $estadoId, string $fecha, string $documento, string $observaciones, ?int $usuarioId = null): void;
    public function actualizarFechaNotificacion(int $licenciaId, string $fecha): void;
}
