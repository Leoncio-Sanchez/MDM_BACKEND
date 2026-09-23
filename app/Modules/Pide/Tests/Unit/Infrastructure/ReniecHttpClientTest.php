<?php

declare(strict_types=1);

namespace App\Modules\Pide\Tests\Unit\Infrastructure;

use App\Modules\Pide\Domain\Exceptions\ReniecError;
use App\Modules\Pide\Infrastructure\External\ReniecHttpClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

uses(TestCase::class);

it('consulta RENIEC correctamente con DNI válido', function () {
    Http::fake([
        'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar*' => Http::response([
            'consultarResponse' => [
                'return' => [
                    'coResultado' => '0000',
                    'datosPersona' => [
                        'prenombres' => 'JUAN CARLOS',
                        'apPrimer' => 'PEREZ',
                        'apSegundo' => 'LOPEZ',
                    ],
                ],
            ],
        ], 200),
    ]);

    $client = new ReniecHttpClient(
        url: 'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar',
        dniUsuario: '00000000',
        rucUsuario: '20123456789',
        password: 'secret_password',
        timeout: 10,
    );

    $resultado = $client->consultarPorDni('12345678');

    expect($resultado)->toBeArray()
        ->and($resultado['consultarResponse']['return']['datosPersona']['prenombres'])->toBe('JUAN CARLOS');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'nuDniConsulta=12345678')
            && str_contains($request->url(), 'nuDniUsuario=00000000')
            && str_contains($request->url(), 'nuRucUsuario=20123456789')
            && str_contains($request->url(), 'password=secret_password')
            && str_contains($request->url(), 'out=json');
    });
});

it('trimea espacios en blanco del DNI correctamente', function () {
    Http::fake([
        '*' => Http::response(['status' => 'OK'], 200),
    ]);

    $client = new ReniecHttpClient('https://test.local', 'user', 'ruc', 'pass', 10);
    $resultado = $client->consultarPorDni('  87654321  ');

    expect($resultado)->toBe(['status' => 'OK']);

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'nuDniConsulta=87654321');
    });
});

it('lanza InvalidArgumentException si DNI tiene menos o más de 8 dígitos', function () {
    $client = new ReniecHttpClient('https://test.local', 'u', 'r', 'p', 10);

    expect(fn () => $client->consultarPorDni('123'))
        ->toThrow(InvalidArgumentException::class, 'El DNI debe tener exactamente 8 dígitos numéricos.');

    expect(fn () => $client->consultarPorDni('123456789'))
        ->toThrow(InvalidArgumentException::class, 'El DNI debe tener exactamente 8 dígitos numéricos.');
});

it('lanza InvalidArgumentException si DNI contiene caracteres no numéricos', function () {
    $client = new ReniecHttpClient('https://test.local', 'u', 'r', 'p', 10);

    expect(fn () => $client->consultarPorDni('1234567A'))
        ->toThrow(InvalidArgumentException::class, 'El DNI debe tener exactamente 8 dígitos numéricos.');
});

it('lanza ReniecError si alguna de las credenciales está vacía', function () {
    $client1 = new ReniecHttpClient('https://test.local', '', 'ruc', 'pass', 10);
    expect(fn () => $client1->consultarPorDni('12345678'))
        ->toThrow(ReniecError::class, 'Las credenciales RENIEC no están configuradas');

    $client2 = new ReniecHttpClient('https://test.local', 'user', '', 'pass', 10);
    expect(fn () => $client2->consultarPorDni('12345678'))
        ->toThrow(ReniecError::class, 'Las credenciales RENIEC no están configuradas');

    $client3 = new ReniecHttpClient('https://test.local', 'user', 'ruc', '', 10);
    expect(fn () => $client3->consultarPorDni('12345678'))
        ->toThrow(ReniecError::class, 'Las credenciales RENIEC no están configuradas');
});

it('lanza ReniecError con mensaje de timeout cuando se produce un timeout', function () {
    Http::fake([
        '*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out after 10000 milliseconds'),
    ]);

    $client = new ReniecHttpClient('https://test.local', 'u', 'r', 'p', 1);

    expect(fn () => $client->consultarPorDni('12345678'))
        ->toThrow(ReniecError::class, 'El servicio RENIEC no respondió en el tiempo esperado.');
});

it('lanza ReniecError con mensaje de conexión fallida cuando se cae la red', function () {
    Http::fake([
        '*' => fn () => throw new ConnectionException('Could not resolve host: ws2.pide.gob.pe'),
    ]);

    $client = new ReniecHttpClient('https://test.local', 'u', 'r', 'p', 10);

    expect(fn () => $client->consultarPorDni('12345678'))
        ->toThrow(ReniecError::class, 'No se pudo conectar con el servicio RENIEC.');
});

it('lanza ReniecError con mensaje de error HTTP cuando upstream devuelve código 500', function () {
    Http::fake([
        '*' => Http::response('Internal Server Error', 500),
    ]);

    $client = new ReniecHttpClient('https://test.local', 'u', 'r', 'p', 10);

    expect(fn () => $client->consultarPorDni('12345678'))
        ->toThrow(ReniecError::class, 'Error HTTP del servicio RENIEC:');
});
