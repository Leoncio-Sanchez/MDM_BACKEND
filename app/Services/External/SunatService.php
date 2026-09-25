<?php

declare(strict_types=1);

namespace App\Services\External;

use App\Exceptions\Pide\SunatError;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Throwable;

class SunatService
{
    private const TIMEOUT = 10;

    private string $url;

    public function __construct(?string $url = null)
    {
        $this->url = $url ?? (string) config('services.sunat.url', 'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales');
    }

    /**
     * @return array<string, mixed>
     */
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
            if (str_contains(strtolower($e->getMessage()), 'time') || str_contains(strtolower($e->getMessage()), 'timed out')) {
                throw SunatError::timeout();
            }

            throw SunatError::conexionFallida();
        } catch (RequestException $e) {
            throw SunatError::httpError($e->getMessage());
        } catch (Throwable $e) {
            if ($e instanceof SunatError || $e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw SunatError::inesperado($e->getMessage());
        }
    }
}
