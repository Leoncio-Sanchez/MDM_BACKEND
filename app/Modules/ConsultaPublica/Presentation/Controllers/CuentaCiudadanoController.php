<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Presentation\Controllers;

use App\Modules\ConsultaPublica\Application\UseCases\CuentaCiudadanoUseCase;
use App\Modules\ConsultaPublica\Presentation\Requests\CuentaCiudadanoRequest;
use App\Modules\ConsultaPublica\Presentation\Resources\TramitePublicoResource;
use App\Modules\Seguridad\Domain\Exceptions\CredencialesInvalidas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class CuentaCiudadanoController
{
    public function __construct(private CuentaCiudadanoUseCase $cuentas) {}

    public function registrar(CuentaCiudadanoRequest $request): JsonResponse
    {
        return new JsonResponse($this->cuentas->registrar(...$this->datos($request))->toArray(), 201);
    }

    public function restablecer(CuentaCiudadanoRequest $request): JsonResponse
    {
        return new JsonResponse($this->cuentas->restablecer(...$this->datos($request))->toArray());
    }

    public function ingresar(Request $request): JsonResponse
    {
        $datos = $request->validate(
            ['documento' => ['required', 'string', 'max:12'], 'clave' => ['required', 'string', 'max:100']],
            ['documento.required' => 'Ingrese su DNI o RUC.', 'clave.required' => 'Ingrese su contraseña.'],
        );

        try {
            return new JsonResponse($this->cuentas->ingresar((string) $datos['documento'], (string) $datos['clave'])->toArray());
        } catch (CredencialesInvalidas) {
            return new JsonResponse(['detail' => 'DNI/RUC o contraseña incorrectos.'], 401);
        }
    }

    public function misTramites(Request $request): JsonResponse
    {
        $tramites = $this->cuentas->misTramites((int) $request->user()?->getAuthIdentifier());

        return new JsonResponse([
            'documento' => $tramites['documento'],
            'expedientes' => TramitePublicoResource::collection($tramites['expedientes']),
            'licencias' => TramitePublicoResource::collection($tramites['licencias']),
            'itse' => TramitePublicoResource::collection($tramites['itse']),
        ]);
    }

    /** @return array{0: string, 1: int, 2: string} */
    private function datos(CuentaCiudadanoRequest $request): array
    {
        return [
            (string) $request->validated('documento'),
            (int) $request->validated('numero_expediente'),
            (string) $request->validated('clave'),
        ];
    }
}
