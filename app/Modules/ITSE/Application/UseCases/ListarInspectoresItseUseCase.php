<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Application\UseCases;

/**
 * Django's ItseInspectoresView always returns [].
 * The table itse_inspectores exists but the feature was never implemented.
 * Replicating the bug per TD-010 (Stakeholder decision).
 */
final readonly class ListarInspectoresItseUseCase
{
    /**
     * Siempre retorna [] (replica el comportamiento de Django).
     * Ver TD-010.
     *
     * @return array<never>
     */
    public function execute(int $itseId): array
    {
        return [];
    }
}
