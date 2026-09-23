<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;

final class NivelRiesgoReadModel extends Model
{
    use ReadOnlyTrait;

    protected $table = 'niveles_riesgo';
    public $timestamps = false;
}
