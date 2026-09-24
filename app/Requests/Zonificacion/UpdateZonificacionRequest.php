<?php

declare(strict_types=1);

namespace App\Requests\Zonificacion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateZonificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'sometimes|string|max:30',
            'nombre' => 'sometimes|string|max:150',
            'esta_activo' => 'sometimes|boolean',
        ];
    }
}
