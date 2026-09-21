<?php

declare(strict_types=1);

namespace App\Modules\Personas\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultarReniecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'dni' => ['required', 'string', 'regex:/^[0-9]{8}$/'],
        ];
    }
}
