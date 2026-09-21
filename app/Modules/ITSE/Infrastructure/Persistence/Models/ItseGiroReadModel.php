<?php
declare(strict_types=1);

namespace App\Modules\ITSE\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only Eloquent model for itse_giros.
 * No mutations allowed (ReadOnly pattern).
 */
final class ItseGiroReadModel extends Model
{
    protected $table = 'itse_giros';
    public $timestamps = false;

    protected $fillable = [];

    public function save(array $options = []): bool
    {
        throw new \LogicException('Read-only model: ItseGiroReadModel cannot be saved.');
    }

    public function delete(): bool|null
    {
        throw new \LogicException('Read-only model: ItseGiroReadModel cannot be deleted.');
    }
}
