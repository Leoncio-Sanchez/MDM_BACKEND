<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AutorizacionImprocedente;
use App\Models\Expediente;
use App\Models\TipoProcedimientoTupa;
use App\Repositories\ExpedienteRepository;
use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpedienteService
{
    public function __construct(
        private readonly ExpedienteRepository $repository,
        private readonly CalculadoraDiasHabiles $calculadora,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Expediente>
     */
    public function listar(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        return Expediente::query()
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPendientes(): array
    {
        $filas = $this->repository->listarPendientes();
        $hoy = new DateTimeImmutable('today');

        foreach ($filas as &$fila) {
            $vencimiento = new DateTimeImmutable((string) $fila['fecha_vencimiento']);
            $fila['dias_habiles_restantes'] = $this->calculadora->diferenciaDiasHabiles(
                $hoy,
                $vencimiento,
            );
        }
        unset($fila);

        return $filas;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscar(string $filtro, string $valor): array
    {
        try {
            $filas = $this->repository->buscarPorFiltro($filtro, $valor);
        } catch (\InvalidArgumentException $e) {
            throw new HttpResponseException(
                response()->json(['error' => $e->getMessage()], 400)
            );
        }

        $hoy = new DateTimeImmutable('today');

        foreach ($filas as &$fila) {
            $vencimiento = new DateTimeImmutable((string) $fila['fecha_vencimiento']);
            $fila['dias_habiles_restantes'] = $this->calculadora->diferenciaDiasHabiles(
                $hoy,
                $vencimiento,
            );
        }
        unset($fila);

        return $filas;
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, Expediente>
     */
    public function consulta(array $filtros): Collection
    {
        $query = Expediente::query()
            ->with('autorizacionesImprocedentes')
            ->orderBy('id', 'desc');

        $numero = $filtros['numero_expediente'] ?? ($filtros['numero'] ?? null);
        if ($numero !== null && trim((string) $numero) !== '') {
            $query->where('numero_expediente', (int) $numero);
        }

        return $query->get();
    }

    public function obtener(int $id): Expediente
    {
        $expediente = Expediente::with('autorizacionesImprocedentes')->find($id);
        if (! $expediente) {
            throw new HttpResponseException(
                response()->json(['error' => 'El expediente no existe.'], 404)
            );
        }

        return $expediente;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function crear(array $data, ?int $usuarioId): Expediente
    {
        return DB::transaction(function () use ($data, $usuarioId): Expediente {
            $tipoId = (int) $data['tipo_procedimiento_tupa_id'];
            $tipo = TipoProcedimientoTupa::find($tipoId);
            if (! $tipo) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El tipo de procedimiento TUPA no existe.'], 400)
                );
            }

            $solicitanteId = (int) $data['solicitante_id'];
            if (! DB::table('personas')->where('id', $solicitanteId)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El solicitante indicado no existe.'], 400)
                );
            }

            if (! empty($data['representante_id'])) {
                $repId = (int) $data['representante_id'];
                if (! DB::table('personas')->where('id', $repId)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => 'El representante indicado no existe.'], 400)
                    );
                }
            }

            $fechaRecepcion = Carbon::parse($data['fecha_recepcion']);
            $anio = $fechaRecepcion->year;

            $driver = DB::connection()->getDriverName();
            if (isset($data['numero_expediente']) && (int) $data['numero_expediente'] > 0) {
                $numero = (int) $data['numero_expediente'];
            } else {
                $queryMax = Expediente::query();
                if ($driver === 'pgsql') {
                    $queryMax->whereRaw('EXTRACT(YEAR FROM fecha_recepcion) = ?', [$anio]);
                } else {
                    $queryMax->whereRaw("strftime('%Y', fecha_recepcion) = ?", [(string) $anio]);
                }
                $numero = (int) ($queryMax->max('numero_expediente') ?? 0) + 1;
            }

            $queryDup = Expediente::where('numero_expediente', $numero);
            if ($driver === 'pgsql') {
                $queryDup->whereRaw('EXTRACT(YEAR FROM fecha_recepcion) = ?', [$anio]);
            } else {
                $queryDup->whereRaw("strftime('%Y', fecha_recepcion) = ?", [(string) $anio]);
            }
            if ($queryDup->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => "El expediente número {$numero} del año {$anio} ya se encuentra registrado."], 409)
                );
            }

            $plazoDias = (int) ($tipo->plazo_atencion_dias ?? 0);
            $diasAlerta = (int) ($tipo->dias_alerta_vencimiento ?? 0);

            $fechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                new DateTimeImmutable($fechaRecepcion->toDateString()),
                $plazoDias,
            );
            $fechaAlerta = $this->calculadora->calcularFechaAlerta(
                $fechaVencimiento,
                $diasAlerta,
            );

            $expediente = new Expediente;
            $expediente->uuid = (string) Str::uuid();
            $expediente->numero_expediente = $numero;
            $expediente->fecha_recepcion = $fechaRecepcion->toDateTimeString();
            $expediente->tipo_procedimiento_tupa_id = $tipoId;
            $expediente->solicitante_id = $solicitanteId;
            $expediente->representante_id = ! empty($data['representante_id']) ? (int) $data['representante_id'] : null;
            $expediente->observaciones = isset($data['observaciones']) ? (string) $data['observaciones'] : null;
            $expediente->fecha_vencimiento = $fechaVencimiento->format('Y-m-d');
            $expediente->fecha_alerta = $fechaAlerta->format('Y-m-d');
            $expediente->usuario_id = $usuarioId;
            $expediente->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
            $expediente->save();

            return $expediente;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizar(int $id, array $data): Expediente
    {
        return DB::transaction(function () use ($id, $data): Expediente {
            $expediente = Expediente::find($id);
            if (! $expediente) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente no existe.'], 404)
                );
            }

            $tipoId = isset($data['tipo_procedimiento_tupa_id']) ? (int) $data['tipo_procedimiento_tupa_id'] : (int) $expediente->tipo_procedimiento_tupa_id;
            $tipo = TipoProcedimientoTupa::find($tipoId);
            if (! $tipo) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El tipo de procedimiento TUPA no existe.'], 400)
                );
            }

            if (isset($data['solicitante_id'])) {
                $solicitanteId = (int) $data['solicitante_id'];
                if (! DB::table('personas')->where('id', $solicitanteId)->exists()) {
                    throw new HttpResponseException(
                        response()->json(['error' => 'El solicitante indicado no existe.'], 400)
                    );
                }
                $expediente->solicitante_id = $solicitanteId;
            }

            if (array_key_exists('representante_id', $data)) {
                if ($data['representante_id'] !== null) {
                    $repId = (int) $data['representante_id'];
                    if (! DB::table('personas')->where('id', $repId)->exists()) {
                        throw new HttpResponseException(
                            response()->json(['error' => 'El representante indicado no existe.'], 400)
                        );
                    }
                    $expediente->representante_id = $repId;
                } else {
                    $expediente->representante_id = null;
                }
            }

            $fechaRecepcion = isset($data['fecha_recepcion'])
                ? Carbon::parse($data['fecha_recepcion'])
                : Carbon::parse($expediente->fecha_recepcion);
            $anio = $fechaRecepcion->year;

            $numero = isset($data['numero_expediente'])
                ? (int) $data['numero_expediente']
                : (int) $expediente->numero_expediente;

            $driver = DB::connection()->getDriverName();
            $queryDup = Expediente::where('numero_expediente', $numero)->where('id', '!=', $id);
            if ($driver === 'pgsql') {
                $queryDup->whereRaw('EXTRACT(YEAR FROM fecha_recepcion) = ?', [$anio]);
            } else {
                $queryDup->whereRaw("strftime('%Y', fecha_recepcion) = ?", [(string) $anio]);
            }
            if ($queryDup->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => "El expediente número {$numero} del año {$anio} ya se encuentra registrado."], 409)
                );
            }

            $expediente->numero_expediente = $numero;
            $expediente->fecha_recepcion = $fechaRecepcion->toDateTimeString();
            $expediente->tipo_procedimiento_tupa_id = $tipoId;

            if (array_key_exists('observaciones', $data)) {
                $expediente->observaciones = $data['observaciones'] !== null ? (string) $data['observaciones'] : null;
            }

            $plazoDias = (int) ($tipo->plazo_atencion_dias ?? 0);
            $diasAlerta = (int) ($tipo->dias_alerta_vencimiento ?? 0);

            $fechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                new DateTimeImmutable($fechaRecepcion->toDateString()),
                $plazoDias,
            );

            if ($expediente->dias_ampliacion && (int) $expediente->dias_ampliacion > 0) {
                $fechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                    $fechaVencimiento,
                    (int) $expediente->dias_ampliacion,
                );
            }

            $fechaAlerta = $this->calculadora->calcularFechaAlerta(
                $fechaVencimiento,
                $diasAlerta,
            );

            $expediente->fecha_vencimiento = $fechaVencimiento->format('Y-m-d');
            $expediente->fecha_alerta = $fechaAlerta->format('Y-m-d');
            $expediente->save();

            return $expediente;
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $expediente = Expediente::find($id);
            if (! $expediente) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente no existe.'], 404)
                );
            }

            if (DB::table('licencias_funcionamiento')->where('expediente_id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'No se puede eliminar el expediente: tiene una Licencia de Funcionamiento asociada.'], 409)
                );
            }

            if (DB::table('itse')->where('expediente_id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'No se puede eliminar el expediente: tiene un ITSE asociado.'], 409)
                );
            }

            DB::table('autorizaciones_improcedentes')->where('expediente_id', $id)->delete();
            if (DB::getSchemaBuilder()->hasTable('expedientes_archivos')) {
                DB::table('expedientes_archivos')->where('expediente_id', $id)->delete();
            }

            $expediente->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function ampliarPlazo(int $id, array $data, ?int $usuarioId): Expediente
    {
        return DB::transaction(function () use ($id, $data, $usuarioId): Expediente {
            $expediente = Expediente::find($id);
            if (! $expediente) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente no existe.'], 404)
                );
            }

            $tipo = TipoProcedimientoTupa::find($expediente->tipo_procedimiento_tupa_id);
            $diasAlerta = (int) ($tipo->dias_alerta_vencimiento ?? 0);
            $diasAmpliacion = (int) $data['dias_ampliacion'];

            $nuevaFechaVencimiento = $this->calculadora->calcularFechaVencimiento(
                new DateTimeImmutable((string) $expediente->fecha_vencimiento),
                $diasAmpliacion,
            );

            $nuevaFechaAlerta = $this->calculadora->calcularFechaAlerta(
                $nuevaFechaVencimiento,
                $diasAlerta,
            );

            $expediente->fecha_suspension = Carbon::parse($data['fecha_suspension'])->toDateString();
            $expediente->dias_ampliacion = $diasAmpliacion;
            $expediente->motivo_ampliacion = (string) $data['motivo_ampliacion'];
            $expediente->fecha_vencimiento = $nuevaFechaVencimiento->format('Y-m-d');
            $expediente->fecha_alerta = $nuevaFechaAlerta->format('Y-m-d');
            $expediente->usuario_ampliacion = $usuarioId;
            $expediente->fecha_digitacion_ampliacion = Carbon::now('UTC')->toDateTimeString();
            $expediente->save();

            return $expediente;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function denegarLicencia(int $id, array $data, ?int $usuarioId): AutorizacionImprocedente
    {
        return DB::transaction(function () use ($id, $data, $usuarioId): AutorizacionImprocedente {
            if (! Expediente::where('id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente no existe.'], 404)
                );
            }

            if (DB::table('licencias_funcionamiento')->where('expediente_id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'No se puede registrar denegación: el expediente ya tiene una Licencia de Funcionamiento emitida.'], 400)
                );
            }

            $autorizacion = new AutorizacionImprocedente;
            $autorizacion->expediente_id = $id;
            $autorizacion->tipo_autorizacion = 'LF';
            $autorizacion->fecha_rechazo = Carbon::parse($data['fecha_rechazo'])->toDateString();
            $autorizacion->documento = (string) $data['documento'];
            $autorizacion->observaciones = (string) $data['observaciones'];
            $autorizacion->usuario_id = $usuarioId;
            $autorizacion->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
            $autorizacion->save();

            return $autorizacion;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function denegarItse(int $id, array $data, ?int $usuarioId): AutorizacionImprocedente
    {
        return DB::transaction(function () use ($id, $data, $usuarioId): AutorizacionImprocedente {
            if (! Expediente::where('id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'El expediente no existe.'], 404)
                );
            }

            if (DB::table('itse')->where('expediente_id', $id)->exists()) {
                throw new HttpResponseException(
                    response()->json(['error' => 'No se puede registrar denegación: el expediente ya tiene un ITSE emitido.'], 400)
                );
            }

            $autorizacion = new AutorizacionImprocedente;
            $autorizacion->expediente_id = $id;
            $autorizacion->tipo_autorizacion = 'ITSE';
            $autorizacion->fecha_rechazo = Carbon::parse($data['fecha_rechazo'])->toDateString();
            $autorizacion->documento = (string) $data['documento'];
            $autorizacion->observaciones = (string) $data['observaciones'];
            $autorizacion->usuario_id = $usuarioId;
            $autorizacion->fecha_digitacion = Carbon::now('UTC')->toDateTimeString();
            $autorizacion->save();

            return $autorizacion;
        });
    }

    public function buscarAutorizacion(int $id, string $tipo): ?AutorizacionImprocedente
    {
        $tipoUpper = strtoupper(trim($tipo));
        if (! in_array($tipoUpper, ['LF', 'ITSE'], true)) {
            throw new HttpResponseException(
                response()->json(['error' => "Tipo '{$tipo}' no válido. Opciones: LF, ITSE."], 400)
            );
        }

        return AutorizacionImprocedente::where('expediente_id', $id)
            ->where('tipo_autorizacion', $tipoUpper)
            ->first();
    }
}
