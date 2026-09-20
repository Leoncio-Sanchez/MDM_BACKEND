with open('app/Modules/TipoDocumentoIdentidad/Infrastructure/Persistence/Models/TipoDocumentoIdentidadModel.php', 'r') as f:
    c = f.read()

import re
c = re.sub(r"class TipoDocumentoIdentidadModel extends Model", r"""/**
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property bool $esta_activo
 * @property bool $es_para_natural
 * @property bool $es_para_juridica
 */
class TipoDocumentoIdentidadModel extends Model""", c)

with open('app/Modules/TipoDocumentoIdentidad/Infrastructure/Persistence/Models/TipoDocumentoIdentidadModel.php', 'w') as f:
    f.write(c)

with open('app/Modules/TipoDocumentoIdentidad/Tests/Feature/TipoDocumentoIdentidadTest.php', 'r') as f:
    c = f.read()

c = c.replace('return User::find($id);', '/** @var User $user */\n        $user = User::find($id);\n        return $user;')

with open('app/Modules/TipoDocumentoIdentidad/Tests/Feature/TipoDocumentoIdentidadTest.php', 'w') as f:
    f.write(c)

