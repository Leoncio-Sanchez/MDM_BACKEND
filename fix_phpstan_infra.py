import os

with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaModel.php', 'r') as f:
    c = f.read()

phpdoc = """/**
 * @property int $id
 * @property string $tipo_persona
 * @property string $sexo
 * @property string|null $apellido_paterno
 * @property string|null $apellido_materno
 * @property string $nombres
 * @property string $direccion
 * @property string $distrito
 * @property string $provincia
 * @property string $departamento
 * @property string|null $telefono
 * @property string|null $correo_electronico
 * @property \\Carbon\\CarbonImmutable $fecha_creacion
 * @property \\Carbon\\CarbonImmutable $fecha_actualizacion
 * @property int $user_id
 * @property \\Illuminate\\Database\\Eloquent\\Collection<int, PersonaDocumentoModel> $documentos
 */
class PersonaModel"""
c = c.replace("class PersonaModel", phpdoc)
c = c.replace("HasMany", "HasMany<PersonaDocumentoModel, \$this>")
with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaModel.php', 'w') as f:
    f.write(c)


with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaDocumentoModel.php', 'r') as f:
    c = f.read()

phpdoc = """/**
 * @property int $id
 * @property int $persona_id
 * @property int $tipo_documento_identidad_id
 * @property string $numero_documento
 */
class PersonaDocumentoModel"""
c = c.replace("class PersonaDocumentoModel", phpdoc)
c = c.replace("BelongsTo", "BelongsTo<PersonaModel, \$this>")
with open('app/Modules/Personas/Infrastructure/Persistence/Models/PersonaDocumentoModel.php', 'w') as f:
    f.write(c)

