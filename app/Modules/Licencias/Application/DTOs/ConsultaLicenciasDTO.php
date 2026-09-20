<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Application\DTOs;
final readonly class ConsultaLicenciasDTO {
    public function __construct(public ?string $numeroLicencia) {}
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(numeroLicencia: isset($data['numero_licencia']) ? (string) $data['numero_licencia'] : null);
    }
}
