<?php

declare(strict_types=1);

namespace App\Modules\Pide\Tests\Unit\Infrastructure;

use App\Modules\Pide\Domain\Exceptions\SunatError;
use App\Modules\Pide\Infrastructure\External\SunatHttpClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

uses(TestCase::class);

it('consulta SUNAT correctamente con RUC válido', function () {
    Http::fake([
        'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales*' => Http::response([
            'getDatosPrincipalesResponse' => [
                'return' => [
                    'ddp_numruc' => '20123456789',
                    'ddp_nombre' => 'EMPRESA EJEMPLO S.A.C.',
                    'ddp_estado' => 'ACTIVO',
                    'ddp_condicion' => 'HABIDO',
                ],
            ],
        ], 200),
    ]);

    $client = new SunatHttpClient('https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales');
    $resultado = $client->consultarPorRuc('20123456789');

    expect($resultado)->toBeArray()
        ->and($resultado['getDatosPrincipalesResponse']['return']['ddp_nombre'])->toBe('EMPRESA EJEMPLO S.A.C.');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'numruc=20123456789')
            && str_contains($request->url(), 'out=json');
    });
});

it('trimea espacios en blanco del RUC correctamente', function () {
    Http::fake([
        '*' => Http::response(['status' => 'OK'], 200),
    ]);

    $client = new SunatHttpClient('https://test.local');
    $resultado = $client->consultarPorRuc('  20123456789  ');

    expect($resultado)->toBe(['status' => 'OK']);

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'numruc=20123456789');
    });
});

it('lanza InvalidArgumentException si RUC tiene longitud distinta a 11 dígitos', function () {
    $client = new SunatHttpClient('https://test.local');

    expect(fn () => $client->consultarPorRuc('1234567890'))
        ->toThrow(InvalidArgumentException::class, 'El RUC debe tener exactamente 11 dígitos numéricos.');

    expect(fn () => $client->consultarPorRuc('123456789012'))
        ->toThrow(InvalidArgumentException::class, 'El RUC debe tener exactamente 11 dígitos numéricos.');
});

it('lanza InvalidArgumentException si RUC contiene letras', function () {
    $client = new SunatHttpClient('https://test.local');

    expect(fn () => $client->consultarPorRuc('2012345678A'))
        ->toThrow(InvalidArgumentException::class, 'El RUC debe tener exactamente 11 dígitos numéricos.');
});

it('lanza SunatError con mensaje de timeout cuando se produce un timeout', function () {
    Http::fake([
        '*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out after 10000 milliseconds'),
    ]);

    $client = new SunatHttpClient('https://test.local');

    expect(fn () => $client->consultarPorRuc('20123456789'))
        ->toThrow(SunatError::class, 'El servicio SUNAT no respondió en el tiempo esperado.');
});

it('lanza SunatError con mensaje de conexión fallida cuando se cae la red', function () {
    Http::fake([
        '*' => fn () => throw new ConnectionException('Could not resolve host: ws3.pide.gob.pe'),
    ]);

    $client = new SunatHttpClient('https://test.local');

    expect(fn () => $client->consultarPorRuc('20123456789'))
        ->toThrow(SunatError::class, 'No se pudo conectar con el servicio SUNAT.');
});

it('lanza SunatError con mensaje de error HTTP cuando upstream devuelve código 500', function () {
    Http::fake([
        '*' => Http::response('Internal Server Error', 500),
    ]);

    $client = new SunatHttpClient('https://test.local');

    expect(fn () => $client->consultarPorRuc('20123456789'))
        ->toThrow(SunatError::class, 'El servicio SUNAT respondió con un error. Intente nuevamente en unos minutos.');
});
