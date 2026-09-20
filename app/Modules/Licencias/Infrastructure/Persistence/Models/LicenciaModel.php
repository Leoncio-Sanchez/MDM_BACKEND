<?php
namespace App\Modules\Licencias\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class LicenciaModel extends Model
{
    protected $table = 'licencias_funcionamiento';
    public $timestamps = false;
    
    protected $fillable = [
        'numero_licencia',
        'fecha_emision',
        'nombre_comercial',
        'titular_id',
        'conductor_id',
        'licencia_principal_id',
        'expediente_id',
        'tipo_licencia_id',
        'nivel_riesgo_id',
        'zonificacion_id',
        'es_vigencia_indeterminada',
        'fecha_inicio_vigencia',
        'fecha_fin_vigencia',
        'actividad',
        'direccion',
        'hora_desde',
        'hora_hasta',
        'resolucion_numero',
        'area',
        'numero_recibo_pago',
        'observaciones',
        'se_puede_publicar',
        'fecha_notificacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_inicio_vigencia' => 'date',
            'fecha_fin_vigencia' => 'date',
            'fecha_notificacion' => 'datetime',
            'es_vigencia_indeterminada' => 'boolean',
            'se_puede_publicar' => 'boolean',
            'area' => 'decimal:2',
        ];
    }
}
