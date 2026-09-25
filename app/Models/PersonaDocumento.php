<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $persona_id
 * @property int $tipo_documento_identidad_id
 * @property string $numero_documento
 * @property-read Persona|null $persona
 * @property-read TipoDocumentoIdentidad|null $tipoDocumentoIdentidad
 *
 * @method static Builder<static> query()
 */
class PersonaDocumento extends Model
{
    protected $table = 'personas_documentos';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'tipo_documento_identidad_id',
        'numero_documento',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'persona_id' => 'integer',
            'tipo_documento_identidad_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Persona, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    /**
     * @return BelongsTo<TipoDocumentoIdentidad, $this>
     */
    public function tipoDocumentoIdentidad(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoIdentidad::class, 'tipo_documento_identidad_id', 'id');
    }
}
