<?php

declare(strict_types=1);

namespace App\Modules\Licencias\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InactivarLicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'licencia_id' => ['required', 'integer'],
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
            'fecha_estado' => ['required', 'date'],
            'documento' => ['required', 'string', 'max:100'],
            'observaciones' => ['required', 'string', 'max:1000'],
        ];
    }
}
