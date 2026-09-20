<?php
declare(strict_types=1);
namespace App\Modules\TipoLicencia\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

final class TipoLicenciaModel extends Model {
    protected $table = 'tipos_licencia';
    public $timestamps = false;
    protected $fillable = ['codigo', 'nombre', 'esta_activo'];
    protected $casts = ['esta_activo' => 'boolean'];
}
