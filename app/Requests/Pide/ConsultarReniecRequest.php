<?php

declare(strict_types=1);

namespace App\Requests\Pide;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConsultarReniecRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'dni' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $dni = $this->query('dni') ?? $this->input('dni');

        if ($dni === null || trim((string) $dni) === '') {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'dni' es obligatorio."], 400)
            );
        }

        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
