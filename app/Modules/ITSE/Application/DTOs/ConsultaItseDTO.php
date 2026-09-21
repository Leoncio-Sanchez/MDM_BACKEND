<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\DTOs;

final readonly class ConsultaItseDTO
{
    public function __construct(
        public ?int $numeroItse,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            numeroItse: isset($data['numero_itse']) ? (int) $data['numero_itse'] : null,
        );
    }
}
