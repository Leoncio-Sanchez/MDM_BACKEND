<?php

declare(strict_types=1);

namespace App\Modules\Pide\Infrastructure\External;

use App\Modules\Pide\Domain\Contracts\SunatClientInterface;
use App\Modules\Pide\Domain\Exceptions\SunatError;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

final readonly class SunatHttpClient implements SunatClientInterface
{
    private const TIMEOUT = 10;

    public function __construct(
        private string $url,
    ) {}

    public function consultarPorRuc(string $ruc): array
    {
        $ruc = trim($ruc);

        if (! ctype_digit($ruc) || strlen($ruc) !== 11) {
            throw new InvalidArgumentException(
                'El RUC debe tener exactamente 11 dígitos numéricos.'
            );
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->get($this->url, [
                    'numruc' => $ruc,
                    'out' => 'json',
                ]);

            $response->throw();

            /** @var array<string, mixed> $json */
            $json = (array) $response->json();

            return $json;
        } catch (ConnectionException $e) {
            $this->registrar($e);
            if (str_contains(strtolower($e->getMessage()), 'time') || str_contains(strtolower($e->getMessage()), 'timed out')) {
                throw SunatError::timeout();
            }

            throw SunatError::conexionFallida();
        } catch (RequestException $e) {
            $this->registrar($e);
            throw SunatError::httpError();
        } catch (Throwable $e) {
            if ($e instanceof SunatError || $e instanceof InvalidArgumentException) {
                throw $e;
            }

            $this->registrar($e);
            throw SunatError::inesperado();
        }
    }

    /** El detalle técnico va al log del servidor, no al usuario. */
    private function registrar(Throwable $e): void
    {
        Log::warning('Error al consultar SUNAT (PIDE)', ['tipo' => $e::class, 'detalle' => $e->getMessage()]);
    }
}
