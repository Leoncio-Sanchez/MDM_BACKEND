<?php
declare(strict_types=1);

namespace App\Modules\Licencias\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class LicenciaModel extends Model
{
    protected $table = 'licencias_funcionamiento';
    protected $primaryKey = 'id';
    public $timestamps = false; // We manage timestamps manually if needed
    protected $guarded = [];
}
