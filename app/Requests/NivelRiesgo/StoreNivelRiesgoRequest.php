<?php

declare(strict_types=1);

namespace App\Requests\NivelRiesgo;

use Illuminate\Foundation\Http\FormRequest;

class StoreNivelRiesgoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:10',
            'nombre' => 'required|string|max:50',
            'esta_activo' => 'nullable|boolean',
        ];
    }
}
