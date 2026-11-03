<?php

declare(strict_types=1);

use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Producción: la API corre detrás de nginx-proxy, sin puertos propios. Solo sus cabeceras cuentan:
 * si se confiara en cualquiera, un atacante evadiría el límite de intentos con solo cambiar la IP.
 */
const IP_DEL_PROXY = '172.30.0.5';

beforeEach(function () {
    Cache::flush();
    config(['trustedproxy.proxies' => '172.30.0.0/24']);
    Route::get('/_prueba/cliente', fn (Request $request) => [
        'ip' => $request->ip(),
        'https' => $request->secure(),
        'url' => url('/x'),
    ]);
});

/** Lo que recibe Laravel de un pedido que viene por nginx-proxy */
function porElProxy(string $cadenaDeIps, array $cabeceras = []): array
{
    return ['REMOTE_ADDR' => IP_DEL_PROXY, 'HTTP_X_FORWARDED_FOR' => $cadenaDeIps] + $cabeceras;
}

it('toma la IP real y el HTTPS que informa el proxy', function () {
    $this->withServerVariables(porElProxy('200.48.1.10', ['HTTP_X_FORWARDED_PROTO' => 'https']))
        ->getJson('http://api.ejemplo.gob.pe/_prueba/cliente')
        ->assertOk()
        ->assertJson(['ip' => '200.48.1.10', 'https' => true, 'url' => 'https://api.ejemplo.gob.pe/x']);
});

it('ignora la IP que el cliente inventa: nginx-proxy agrega la real al final', function () {
    $this->withServerVariables(porElProxy('1.2.3.4, 200.48.1.10'))
        ->getJson('/_prueba/cliente')
        ->assertJsonPath('ip', '200.48.1.10');
});

it('no acepta cabeceras de quien no es el proxy', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '200.48.1.99', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4', 'HTTP_X_FORWARDED_PROTO' => 'https'])
        ->getJson('/_prueba/cliente')
        ->assertJson(['ip' => '200.48.1.99', 'https' => false]);
});

it('no permite falsear el dominio con X-Forwarded-Host', function () {
    $this->withServerVariables(porElProxy('200.48.1.10', ['HTTP_X_FORWARDED_HOST' => 'malicioso.test']))
        ->getJson('http://api.ejemplo.gob.pe/_prueba/cliente')
        ->assertJsonPath('url', 'http://api.ejemplo.gob.pe/x');
});

it('el límite de login cuenta por IP real: cambiar la IP inventada no lo evade', function () {
    $intentar = fn (string $cadena) => $this->withServerVariables(porElProxy($cadena))
        ->postJson('/api/auth/jwt/create/', ['username' => 'nadie', 'password' => 'incorrecta']);

    foreach (range(1, 5) as $intento) {
        $intentar("9.9.9.{$intento}, 200.48.1.10")->assertUnauthorized();
    }
    $intentar('9.9.9.99, 200.48.1.10')->assertTooManyRequests();

    // Otra persona, detrás del mismo proxy, sigue pudiendo ingresar
    $intentar('200.48.1.11')->assertUnauthorized();
});

it('CORS responde solo a los orígenes configurados', function () {
    // Dos orígenes: con uno solo, Laravel siempre responde ese y es el navegador quien compara
    config(['cors.allowed_origins' => ['https://lf-itse.ejemplo.gob.pe', 'https://pruebas.ejemplo.gob.pe']]);
    $preguntar = fn (string $origen) => $this->call('OPTIONS', '/api/auth/jwt/create/', server: [
        'HTTP_ORIGIN' => $origen,
        'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
    ]);

    expect($preguntar('https://lf-itse.ejemplo.gob.pe')->headers->get('Access-Control-Allow-Origin'))
        ->toBe('https://lf-itse.ejemplo.gob.pe')
        ->and($preguntar('https://otro-sitio.test')->headers->get('Access-Control-Allow-Origin'))->toBeNull();
});

it('el seeder con contraseña conocida no corre en producción', function () {
    $this->app['env'] = 'production';

    expect(fn () => (new DatabaseSeeder)->run())->toThrow(RuntimeException::class, 'no corre en producción');
    expect(DB::table('auth_user')->count())->toBe(0);
});
