<?php

declare(strict_types=1);

namespace App\Requests\Giros;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ciiu_id' => 'nullable|integer',
            'nombre' => 'nullable|string|max:200',
            'esta_activo' => 'nullable|boolean',
        ];
    }
}
