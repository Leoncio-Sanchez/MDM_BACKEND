<?php

declare(strict_types=1);

namespace App\Requests\Estados;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:100'],
            'es_para_lf' => ['sometimes', 'boolean'],
            'es_para_itse' => ['sometimes', 'boolean'],
            'esta_activo' => ['sometimes', 'boolean'],
        ];
    }
}
