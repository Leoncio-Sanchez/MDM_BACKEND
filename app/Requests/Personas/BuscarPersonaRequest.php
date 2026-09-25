<?php

declare(strict_types=1);

namespace App\Requests\Personas;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BuscarPersonaRequest extends FormRequest
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
            'filtro' => ['required', 'string', 'in:NOMBRE,DOCUMENTO,ID,nombre,documento,id'],
            'valor' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $filtro = $this->query('filtro') ?? $this->input('filtro');
        $valor = $this->query('valor') ?? $this->input('valor');

        if ($filtro === null || trim((string) $filtro) === '') {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'filtro' es obligatorio."], 400)
            );
        }

        if ($valor === null || trim((string) $valor) === '') {
            throw new HttpResponseException(
                response()->json(['error' => "El parámetro 'valor' es obligatorio."], 400)
            );
        }

        $filtroUpper = strtoupper(trim((string) $filtro));
        if (! in_array($filtroUpper, ['NOMBRE', 'DOCUMENTO', 'ID'], true)) {
            throw new HttpResponseException(
                response()->json(['error' => "Filtro '{$filtro}' no válido. Opciones: NOMBRE, DOCUMENTO, ID"], 400)
            );
        }

        throw new HttpResponseException(
            response()->json($validator->errors(), 400)
        );
    }
}
