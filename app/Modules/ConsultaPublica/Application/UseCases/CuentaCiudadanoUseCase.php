<?php

declare(strict_types=1);

namespace App\Modules\ConsultaPublica\Application\UseCases;

use App\Modules\ConsultaPublica\Domain\Contracts\ConsultaPublicaRepositoryInterface;
use App\Modules\ConsultaPublica\Domain\Contracts\CuentaCiudadanoRepositoryInterface;
use App\Modules\ConsultaPublica\Domain\Exceptions\CuentaCiudadanoInvalida;
use App\Modules\ConsultaPublica\Domain\ValueObjects\TramitePublico;
use App\Modules\Seguridad\Application\DTOs\LoginDTO;
use App\Modules\Seguridad\Application\UseCases\AutenticarUsuarioUseCase;
use App\Modules\Seguridad\Domain\Contracts\PasswordHasherInterface;
use App\Modules\Seguridad\Domain\Exceptions\CredencialesInvalidas;
use App\Modules\Seguridad\Domain\ValueObjects\TokenPair;

/**
 * Cuenta del ciudadano. Para crearla o restablecer la contraseña prueba que el documento es
 * suyo con el número de uno de sus expedientes (está en su cargo de recepción): la misma
 * prueba que pide la consulta pública, sin correo ni RENIEC.
 */
final readonly class CuentaCiudadanoUseCase
{
    public function __construct(
        private CuentaCiudadanoRepositoryInterface $cuentas,
        private ConsultaPublicaRepositoryInterface $consultas,
        private PasswordHasherInterface $hasher,
        private AutenticarUsuarioUseCase $autenticar,
    ) {}

    /** Crea la cuenta y deja la sesión iniciada */
    public function registrar(string $documento, int $numeroExpediente, string $clave): TokenPair
    {
        $documento = self::normalizar($documento);
        $this->exigirPrueba($documento, $numeroExpediente, $clave);
        if ($this->cuentas->existeCuenta($documento)) {
            throw CuentaCiudadanoInvalida::yaExiste();
        }

        $this->cuentas->crear($documento, $this->hasher->hash($clave));

        return $this->ingresar($documento, $clave);
    }

    /** Nueva contraseña con la misma prueba del registro; deja la sesión iniciada */
    public function restablecer(string $documento, int $numeroExpediente, string $clave): TokenPair
    {
        $documento = self::normalizar($documento);
        $this->exigirPrueba($documento, $numeroExpediente, $clave);
        if (! $this->cuentas->existeCuenta($documento)) {
            throw CuentaCiudadanoInvalida::noExiste();
        }

        $this->cuentas->cambiarClave($documento, $this->hasher->hash($clave));

        return $this->ingresar($documento, $clave);
    }

    /** @throws CredencialesInvalidas */
    public function ingresar(string $documento, string $clave): TokenPair
    {
        return $this->autenticar->execute(new LoginDTO(
            username: CuentaCiudadanoRepositoryInterface::PREFIJO_USUARIO.self::normalizar($documento),
            password: $clave,
        ));
    }

    /**
     * Todo lo que está a nombre del documento de la cuenta. Sin cuenta vinculada (p. ej. un
     * usuario antiguo no es personal), listas vacías.
     *
     * @return array{documento: string|null, expedientes: list<TramitePublico>, licencias: list<TramitePublico>, itse: list<TramitePublico>}
     */
    public function misTramites(int $usuarioId): array
    {
        $documento = $this->cuentas->documentoDelUsuario($usuarioId);
        if ($documento === null) {
            return ['documento' => null, 'expedientes' => [], 'licencias' => [], 'itse' => []];
        }

        return [
            'documento' => $documento,
            'expedientes' => $this->consultas->expedientes(null, $documento),
            'licencias' => $this->consultas->licencias(null, $documento),
            'itse' => $this->consultas->itse(null, $documento),
        ];
    }

    private function exigirPrueba(string $documento, int $numeroExpediente, string $clave): void
    {
        if (mb_strtoupper($clave) === $documento) {
            throw CuentaCiudadanoInvalida::claveIgualAlDocumento();
        }
        if (! $this->cuentas->esParteDeExpediente($documento, $numeroExpediente)) {
            throw CuentaCiudadanoInvalida::datosNoCoinciden();
        }
    }

    private static function normalizar(string $documento): string
    {
        return mb_strtoupper(trim($documento));
    }
}
