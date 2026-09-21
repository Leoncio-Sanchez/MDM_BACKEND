<?php

declare(strict_types=1);

namespace App\Modules\Personas\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $persona_id
 * @property int $tipo_documento_identidad_id
 * @property string $numero_documento
 */
class PersonaDocumentoModel extends Model
{
    protected $table = 'personas_documentos';

    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'tipo_documento_identidad_id',
        'numero_documento',
    ];

    /** @return BelongsTo<PersonaModel, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id', 'id');
    }
}
