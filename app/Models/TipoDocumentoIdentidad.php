<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TipoDocumentoIdentidadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 * @property bool $es_para_natural
 * @property bool $es_para_juridica
 *
 * @method static Builder<self> query()
 * @method static Builder<self> activos()
 * @method static Builder<self> inactivos()
 * @method static Builder<self> paraNatural()
 * @method static Builder<self> paraJuridica()
 */
class TipoDocumentoIdentidad extends Model
{
    /** @use HasFactory<TipoDocumentoIdentidadFactory> */
    use HasFactory;

    protected static function newFactory(): TipoDocumentoIdentidadFactory
    {
        return TipoDocumentoIdentidadFactory::new();
    }

    protected $table = 'tipos_documento_identidad';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'esta_activo',
        'es_para_natural',
        'es_para_juridica',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'codigo' => 'string',
            'nombre' => 'string',
            'esta_activo' => 'boolean',
            'es_para_natural' => 'boolean',
            'es_para_juridica' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('esta_activo', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('esta_activo', false);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeParaNatural(Builder $query): Builder
    {
        return $query->where('esta_activo', true)->where('es_para_natural', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeParaJuridica(Builder $query): Builder
    {
        return $query->where('esta_activo', true)->where('es_para_juridica', true);
    }

    public function estaActivo(): bool
    {
        return (bool) $this->esta_activo;
    }
}
