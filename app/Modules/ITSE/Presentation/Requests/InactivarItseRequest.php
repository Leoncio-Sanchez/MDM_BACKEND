<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InactivarItseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('api') !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'itse_id' => ['required', 'integer'],
            'estado_id' => ['required', 'integer', 'exists:estados,id'],
            'fecha_estado' => ['required', 'date'],
            'documento' => ['required', 'string', 'max:100'],
            'observaciones' => ['required', 'string', 'max:1000'],
        ];
    }
}
