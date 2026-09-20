<?php
declare(strict_types=1);
namespace App\Modules\Licencias\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;
final class LicenciaModel extends Model {
    protected $table = 'licencias_funcionamiento';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $incrementing = true;
    protected $keyType = 'int';
    /** @var list<string> */
    protected $fillable = [
        'numero_licencia', 'fecha_emision', 'nombre_comercial', 'titular_id',
        'expediente_id', 'tipo_licencia_id', 'nivel_riesgo_id', 'zonificacion_id', 'estado',
        'conductor_id', 'licencia_principal_id', 'es_vigencia_indeterminada', 'actividad',
        'direccion', 'hora_desde', 'hora_hasta', 'resolucion_numero', 'area', 'numero_recibo_pago',
        'se_puede_publicar', 'fecha_digitacion', 'usuario_id'
    ];
    /** @return array<string, string> */
    protected function casts(): array {
        return [
            'fecha_emision' => 'date',
            'fecha_notificacion' => 'datetime',
            'fecha_digitacion' => 'datetime',
        ];
    }
}
