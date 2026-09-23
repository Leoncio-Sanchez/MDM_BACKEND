<?php

declare(strict_types=1);

namespace App\Modules\Expedientes\Infrastructure\Persistence\Models;

use App\Shared\Infrastructure\Concerns\ReadOnlyTrait;
use Illuminate\Database\Eloquent\Model;

final class FeriadoAnualReadModel extends Model
{
    use ReadOnlyTrait;

    protected $table = 'feriados_anuales';

    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = ['*'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'feriado' => 'date',
        ];
    }
}
