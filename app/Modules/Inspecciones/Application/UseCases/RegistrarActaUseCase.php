<?php

declare(strict_types=1);

namespace App\Modules\Inspecciones\Application\UseCases;

use App\Modules\Expedientes\Domain\Services\CalculadoraDiasHabilesService;
use App\Modules\Inspecciones\Domain\Contracts\ActaInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Contracts\AsignacionInspeccionRepositoryInterface;
use App\Modules\Inspecciones\Domain\Exceptions\ActaDuplicada;
use App\Modules\Inspecciones\Domain\Exceptions\ActaInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionInvalida;
use App\Modules\Inspecciones\Domain\Exceptions\AsignacionNoEncontrada;
use App\Modules\Inspecciones\Domain\ValueObjects\DatosActa;
use App\Modules\Inspecciones\Domain\ValueObjects\ResultadoActa;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\UploadedFile;

/**
 * El inspector asignado registra el acta de su visita. La 1.ª visita y la reinspección
 * se distinguen solas por las actas que ya tiene el expediente.
 */
final readonly class RegistrarActaUseCase
{
    public function __construct(
        private AsignacionInspeccionRepositoryInterface $asignaciones,
        private ActaInspeccionRepositoryInterface $actas,
        private CalculadoraDiasHabilesService $diasHabiles,
    ) {}

    /**
     * @param  array<int, array{tipo: string, archivo: UploadedFile}>  $fotos
     * @return array<string, mixed> el acta registrada
     */
    public function execute(int $asignacionId, int $inspectorId, DatosActa $datos, array $fotos): array
    {
        // Solo el responsable de la asignación vigente; al acompañante se le explica, para cualquier otro "no existe"
        $asignacion = $this->asignaciones->buscar($asignacionId);
        if ($asignacion === null || ! $asignacion['vigente']) {
            throw AsignacionNoEncontrada::id($asignacionId);
        }
        if ((int) $asignacion['inspector_id'] !== $inspectorId) {
            if (in_array($inspectorId, array_column((array) $asignacion['acompanantes'], 'id'), true)) {
                throw ActaInvalida::soloResponsable((string) $asignacion['inspector_nombre']);
            }
            throw AsignacionNoEncontrada::id($asignacionId);
        }
        $expedienteId = (int) $asignacion['expediente_id'];
        $numeroExpediente = (int) $asignacion['numero_expediente'];

        $previas = $this->actas->deExpediente($expedienteId, soloVigentes: true);
        $ultima = $previas === [] ? null : $previas[array_key_last($previas)];
        if ($ultima !== null && ResultadoActa::from((string) $ultima['resultado'])->esFinal()) {
            throw ActaInvalida::dictamenFinal($numeroExpediente);
        }
        if (! $this->asignaciones->expedienteEsperaInspeccion($expedienteId)) {
            throw AsignacionInvalida::expedienteSinInspeccionPendiente($numeroExpediente);
        }
        if ($ultima !== null && $datos->fechaVisita <= new DateTimeImmutable((string) $ultima['fecha_visita'])) {
            throw ActaInvalida::fechaAnteriorALaPrimeraVisita();
        }

        $visita = count($previas) + 1;
        $resultado = $datos->resultado($visita);
        $fechaLimite = null;
        if ($resultado === ResultadoActa::Observado) {
            if (! in_array($datos->plazoSubsanacionDias, DatosActa::PLAZOS_SUBSANACION, true)) {
                throw ActaInvalida::faltaPlazo();
            }
            $fechaLimite = $this->diasHabiles
                ->calcularFechaVencimiento($datos->fechaVisita->setTimezone(new DateTimeZone('America/Lima'))->setTime(0, 0), (int) $datos->plazoSubsanacionDias)
                ->format('Y-m-d');
        }

        $tiposRecibidos = array_column($fotos, 'tipo');
        foreach ($datos->fotosObligatorias() as $tipo => $deQue) {
            if (! in_array($tipo, $tiposRecibidos, true)) {
                throw ActaInvalida::faltaFoto($deQue);
            }
        }

        if ($this->actas->existeNumero($datos->numeroActa)) {
            throw ActaDuplicada::numero($datos->numeroActa);
        }

        $id = $this->actas->registrar($asignacionId, $expedienteId, $inspectorId, $visita, $datos, $resultado, $fechaLimite, $fotos);

        return (array) $this->actas->buscar($id);
    }
}
