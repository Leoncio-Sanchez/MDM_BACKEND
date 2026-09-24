<?php

declare(strict_types=1);

namespace App\Requests\Zonificacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreZonificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:30',
            'nombre' => 'required|string|max:150',
            'esta_activo' => 'nullable|boolean',
        ];
    }
}
