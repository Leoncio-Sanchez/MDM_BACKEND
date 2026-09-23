<?php

declare(strict_types=1);

namespace App\Modules\Pide\Infrastructure\External;

use App\Modules\Pide\Domain\Contracts\ReniecClientInterface;
use App\Modules\Pide\Domain\Exceptions\ReniecError;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Throwable;

final readonly class ReniecHttpClient implements ReniecClientInterface
{
    public function __construct(
        private string $url,
        private string $dniUsuario,
        private string $rucUsuario,
        private string $password,
        private int $timeout,
    ) {}

    public function consultarPorDni(string $dni): array
    {
        $dni = trim($dni);

        if (! ctype_digit($dni) || strlen($dni) !== 8) {
            throw new InvalidArgumentException(
                'El DNI debe tener exactamente 8 dígitos numéricos.'
            );
        }

        if ($this->dniUsuario === '' || $this->rucUsuario === '' || $this->password === '') {
            throw ReniecError::credencialesNoConfiguradas();
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get($this->url, [
                    'nuDniConsulta' => $dni,
                    'nuDniUsuario' => $this->dniUsuario,
                    'nuRucUsuario' => $this->rucUsuario,
                    'password' => $this->password,
                    'out' => 'json',
                ]);

            $response->throw();

            /** @var array<string, mixed> $json */
            $json = (array) $response->json();

            return $json;
        } catch (ConnectionException $e) {
            if (str_contains(strtolower($e->getMessage()), 'time') || str_contains(strtolower($e->getMessage()), 'timed out')) {
                throw ReniecError::timeout();
            }

            throw ReniecError::conexionFallida();
        } catch (RequestException $e) {
            throw ReniecError::httpError($e->getMessage());
        } catch (Throwable $e) {
            if ($e instanceof ReniecError || $e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw ReniecError::inesperado($e->getMessage());
        }
    }
}
