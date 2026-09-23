<?php
declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;

final class FeriadoRecurrenteReadModel extends Model
{
    use ReadOnlyTrait;

    protected $table = 'feriados_recurrentes';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = ['*'];
}
