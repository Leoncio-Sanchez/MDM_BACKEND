<?php

declare(strict_types=1);

namespace App\Requests\TipoLicencia;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTipoLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'codigo' => 'sometimes|string|max:20',
            'nombre' => 'sometimes|string|max:150',
            'esta_activo' => 'sometimes|boolean',
        ];
    }
}
