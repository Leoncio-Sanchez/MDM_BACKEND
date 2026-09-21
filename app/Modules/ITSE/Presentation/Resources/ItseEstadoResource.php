<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ItseEstadoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return (array) parent::toArray($request);
    }
}
