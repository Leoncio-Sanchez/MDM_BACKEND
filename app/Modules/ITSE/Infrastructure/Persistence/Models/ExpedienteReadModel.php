<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Models;

use App\Modules\Licencias\Infrastructure\Persistence\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;

final class ExpedienteReadModel extends Model
{
    use ReadOnlyTrait;

    protected $table = 'expedientes';
    public $timestamps = false;
}
