<?php
use App\Modules\Personas\Infrastructure\External\ReniecHttpClient;
use App\Modules\Personas\Domain\Exceptions\ReniecNoDisponible;
use App\Modules\Personas\Domain\Exceptions\ReniecRespuestaInvalida;
use Illuminate\Support\Facades\Http;

uses(\Tests\TestCase::class);

it('consulta correctamente y mapea DTO', function () {
    Http::fake([
        '*' => Http::response([
            'consultarResponse' => [
                'return' => [
                    'nuDni' => '12345678',
                    'prenombres' => 'Juan',
                    'apePaterno' => 'Perez',
                    'apeMaterno' => 'Gomez',
                ]
            ]
        ], 200)
    ]);
    
    $client = new ReniecHttpClient('url', 'dni', 'ruc', 'pass', 10);
    $res = $client->consultarPorDni('12345678');
    
    expect($res->dni)->toBe('12345678');
    expect($res->nombres)->toBe('Juan');
});

it('lanza excepcion si respuesta no es 200', function () {
    Http::fake([
        '*' => Http::response([], 500)
    ]);
    
    $client = new ReniecHttpClient('url', 'dni', 'ruc', 'pass', 10);
    $client->consultarPorDni('12345678');
})->throws(ReniecNoDisponible::class);

it('lanza excepcion si respuesta no tiene consultarResponse', function () {
    Http::fake([
        '*' => Http::response(['data' => 'invalid'], 200)
    ]);
    
    $client = new ReniecHttpClient('url', 'dni', 'ruc', 'pass', 10);
    $client->consultarPorDni('12345678');
})->throws(ReniecRespuestaInvalida::class);
