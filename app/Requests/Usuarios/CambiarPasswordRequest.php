<?php

declare(strict_types=1);

namespace App\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;

class CambiarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'password' => ['nullable', 'string'],
            'password_confirm' => ['nullable', 'string'],
        ];
    }
}
