<?php

declare(strict_types=1);

namespace App\Requests\NivelRiesgo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNivelRiesgoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'sometimes|string|max:10',
            'nombre' => 'sometimes|string|max:50',
            'esta_activo' => 'sometimes|boolean',
        ];
    }
}
