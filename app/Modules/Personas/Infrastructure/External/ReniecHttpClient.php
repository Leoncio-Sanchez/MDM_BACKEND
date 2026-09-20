<?php
declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\External;

use App\Modules\Personas\Application\DTOs\PersonaReniecDTO;
use App\Modules\Personas\Domain\Contracts\ReniecClientInterface;
use App\Modules\Personas\Domain\Exceptions\ReniecNoDisponible;
use App\Modules\Personas\Domain\Exceptions\ReniecRespuestaInvalida;
use Illuminate\Support\Facades\Http;

final class ReniecHttpClient implements ReniecClientInterface
{
    public function __construct(
        private readonly string $url,
        private readonly string $dniUsuario,
        private readonly string $rucUsuario,
        private readonly string $password,
        private readonly int $timeout,
    ) {}

    public function consultarPorDni(string $dni): PersonaReniecDTO
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->url, [
                'nuDniConsulta' => $dni,
                'nuDniUsuario' => $this->dniUsuario,
                'nuRucUsuario' => $this->rucUsuario,
                'password' => $this->password,
                'out' => 'json',
            ]);
        } catch (\Exception $e) {
            throw ReniecNoDisponible::porDni($dni);
        }

        if (!$response->successful()) {
            throw ReniecNoDisponible::porDni($dni);
        }

        $data = $response->json();

        if (!is_array($data) || !isset($data['consultarResponse'])) {
            throw ReniecRespuestaInvalida::porDni($dni);
        }

        $personaData = $data['consultarResponse']['return'] ?? $data['consultarResponse'];

        return PersonaReniecDTO::fromArray($personaData);
    }
}
