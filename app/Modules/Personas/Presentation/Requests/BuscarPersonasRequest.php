<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuscarPersonasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'filtro' => ['required', 'string', 'in:NOMBRE,DOCUMENTO,ID'],
            'valor' => ['required', 'string'],
        ];
    }
}
