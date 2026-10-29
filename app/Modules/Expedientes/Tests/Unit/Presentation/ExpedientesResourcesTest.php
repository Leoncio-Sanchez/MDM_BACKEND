<?php

declare(strict_types=1);

use App\Modules\Expedientes\Presentation\Resources\ExpedienteBusquedaResource;
use App\Modules\Expedientes\Presentation\Resources\ExpedientePendienteResource;
use Illuminate\Http\Request;

it('ExpedientePendienteResource expone exactamente 11 keys', function () {
    $data = [
        'id' => 1,
        'numero_expediente' => 101,
        'fecha_recepcion' => '2025-01-03T10:00:00-05:00',
        'nombre' => 'LICENCIA TIPO 1',
        'fecha_vencimiento' => '2025-01-15',
        'fecha_alerta' => '2025-01-12',
        'persona_nombre' => 'JUAN PEREZ',
        'licencia_pendiente' => true,
        'itse_pendiente' => false,
        'mostrar_alerta' => true,
        'dias_habiles_restantes' => 5,
    ];

    $resource = new ExpedientePendienteResource($data);
    $result = $resource->toArray(Request::create('/test'));

    expect($result)->toBeArray()
        ->and(count($result))->toBe(11)
        ->and(array_keys($result))->toBe([
            'id',
            'numero_expediente',
            'fecha_recepcion',
            'nombre',
            'fecha_vencimiento',
            'fecha_alerta',
            'persona_nombre',
            'licencia_pendiente',
            'itse_pendiente',
            'mostrar_alerta',
            'dias_habiles_restantes',
        ]);
});

it('ExpedienteBusquedaResource expone las 27 keys de Django más las de anulación', function () {
    $data = [
        'id' => 10,
        'tipo_procedimiento_tupa_id' => 2,
        'numero_expediente' => 202,
        'fecha_recepcion' => '2025-02-01T08:30:00-05:00',
        'solicitante_id' => 5,
        'representante_id' => null,
        'observaciones' => 'Ninguna',
        'fecha_vencimiento' => '2025-02-20',
        'fecha_alerta' => '2025-02-18',
        'fecha_suspension' => null,
        'dias_ampliacion' => null,
        'motivo_ampliacion' => null,
        'usuario_ampliacion' => null,
        'fecha_digitacion_ampliacion' => null,
        'usuario_id' => 1,
        'fecha_digitacion' => '2025-02-01T08:30:00-05:00',
        'nombre_procedimiento' => 'ITSE BASICA',
        'plazo_atencion_dias' => 15,
        'dias_alerta_vencimiento' => 2,
        'requiere_itse' => 1,
        'requiere_lf' => 0,
        'solicitante_nombre' => 'EMPRESA SAC',
        'solicitante_ruc' => '20123456789',
        'licencia_pendiente' => false,
        'itse_pendiente' => true,
        'mostrar_alerta' => false,
        'dias_habiles_restantes' => 10,
    ];

    $resource = new ExpedienteBusquedaResource($data);
    $result = $resource->toArray(Request::create('/test'));

    expect($result)->toBeArray()
        ->and(count($result))->toBe(29)
        ->and(array_keys($result))->toBe([
            'id',
            'tipo_procedimiento_tupa_id',
            'numero_expediente',
            'fecha_recepcion',
            'solicitante_id',
            'representante_id',
            'observaciones',
            'fecha_vencimiento',
            'fecha_alerta',
            'fecha_suspension',
            'dias_ampliacion',
            'motivo_ampliacion',
            'usuario_ampliacion',
            'fecha_digitacion_ampliacion',
            'usuario_id',
            'fecha_digitacion',
            'nombre_procedimiento',
            'plazo_atencion_dias',
            'dias_alerta_vencimiento',
            'requiere_itse',
            'requiere_lf',
            'solicitante_nombre',
            'solicitante_ruc',
            'licencia_pendiente',
            'itse_pendiente',
            'mostrar_alerta',
            'anulado',
            'motivo_anulacion',
            'dias_habiles_restantes',
        ]);
});

it('castea booleanos correctamente en ambos resources', function () {
    $pendiente = new ExpedientePendienteResource([
        'id' => 1,
        'licencia_pendiente' => 1,
        'itse_pendiente' => 0,
        'mostrar_alerta' => 'true',
    ]);
    $resPend = $pendiente->toArray(Request::create('/test'));
    expect($resPend['licencia_pendiente'])->toBeTrue()
        ->and($resPend['itse_pendiente'])->toBeFalse()
        ->and($resPend['mostrar_alerta'])->toBeTrue();

    $busqueda = new ExpedienteBusquedaResource([
        'requiere_itse' => 1,
        'requiere_lf' => 0,
        'licencia_pendiente' => true,
        'itse_pendiente' => false,
        'mostrar_alerta' => 0,
    ]);
    $resBusq = $busqueda->toArray(Request::create('/test'));
    expect($resBusq['requiere_itse'])->toBeTrue()
        ->and($resBusq['requiere_lf'])->toBeFalse()
        ->and($resBusq['licencia_pendiente'])->toBeTrue()
        ->and($resBusq['itse_pendiente'])->toBeFalse()
        ->and($resBusq['mostrar_alerta'])->toBeFalse();
});

it('preserva valores null en los campos correspondientes', function () {
    $busqueda = new ExpedienteBusquedaResource([
        'representante_id' => null,
        'observaciones' => null,
        'fecha_suspension' => null,
        'dias_ampliacion' => null,
        'motivo_ampliacion' => null,
        'usuario_ampliacion' => null,
        'fecha_digitacion_ampliacion' => null,
        'solicitante_ruc' => null,
    ]);
    $res = $busqueda->toArray(Request::create('/test'));

    expect($res['representante_id'])->toBeNull()
        ->and($res['observaciones'])->toBeNull()
        ->and($res['fecha_suspension'])->toBeNull()
        ->and($res['dias_ampliacion'])->toBeNull()
        ->and($res['motivo_ampliacion'])->toBeNull()
        ->and($res['usuario_ampliacion'])->toBeNull()
        ->and($res['fecha_digitacion_ampliacion'])->toBeNull()
        ->and($res['solicitante_ruc'])->toBeNull();
});

it('soporta input como objeto stdClass', function () {
    $obj = (object) [
        'id' => 99,
        'numero_expediente' => 999,
        'fecha_recepcion' => '2025-01-01',
        'nombre' => 'Test',
        'fecha_vencimiento' => '2025-01-10',
        'fecha_alerta' => '2025-01-08',
        'persona_nombre' => 'Pedro',
        'licencia_pendiente' => true,
        'itse_pendiente' => false,
        'mostrar_alerta' => false,
        'dias_habiles_restantes' => 3,
    ];

    $resource = new ExpedientePendienteResource($obj);
    $res = $resource->toArray(Request::create('/test'));

    expect($res['id'])->toBe(99)
        ->and($res['numero_expediente'])->toBe(999)
        ->and($res['dias_habiles_restantes'])->toBe(3);
});
