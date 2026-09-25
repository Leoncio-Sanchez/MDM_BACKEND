<?php

declare(strict_types=1);

namespace App\Requests\Pide;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConsultarSunatRequest extends FormRequest
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
            'ruc' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $ruc = $this->query('ruc') ?? $this->input('ruc');

        if ($ruc === null || trim((string) $ruc) === '') {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'ruc' es obligatorio."], 400)
            );
        }

        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
