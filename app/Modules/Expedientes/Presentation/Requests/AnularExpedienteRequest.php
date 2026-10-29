<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Presentation\Requests;

use App\Modules\Expedientes\Domain\ValueObjects\AnulacionExpediente;
use Illuminate\Foundation\Http\FormRequest;

final class AnularExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:'.AnulacionExpediente::MOTIVO_MINIMO, 'max:'.AnulacionExpediente::MOTIVO_MAXIMO],
            'documento' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'motivo.required' => 'Indique el motivo de la anulación.',
            'motivo.min' => 'El motivo debe tener al menos :min caracteres.',
            'motivo.max' => 'El motivo no puede pasar de :max caracteres.',
            'documento.max' => 'El documento no puede pasar de :max caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Sin esto "          " pasaría el mínimo de caracteres
        if (is_string($this->input('motivo'))) {
            $this->merge(['motivo' => trim($this->input('motivo'))]);
        }
    }
}
