<?php

declare(strict_types=1);

namespace App\Requests\Giros;

use Illuminate\Foundation\Http\FormRequest;

class StoreGiroRequest extends FormRequest
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
            'nombre' => 'required|string|max:200',
            'esta_activo' => 'required|boolean',
        ];
    }
}
