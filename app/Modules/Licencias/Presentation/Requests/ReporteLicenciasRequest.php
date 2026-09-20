<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Presentation\Requests;
use Illuminate\Foundation\Http\FormRequest;
final class ReporteLicenciasRequest extends FormRequest {
    public function authorize(): bool { return true; }
    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
    ];
    }
}
