with open('app/Modules/TipoDocumentoIdentidad/Presentation/Controllers/TipoDocumentoIdentidadController.php', 'r') as f:
    c = f.read()

import re
c = re.sub(
    r"if \(! in_array\(\$tipoPersona, \['N', 'J'\], true\)\) \{\n            return new JsonResponse\(\n                \['error' => \"El parámetro 'tipo_persona' es obligatorio\.\"\],\n                400\n            \);\n        \}",
    r"""if ($tipoPersona === null || $tipoPersona === '') {
            return new JsonResponse(['error' => "El parámetro 'tipo_persona' es obligatorio."], 400);
        }
        if (! in_array($tipoPersona, ['N', 'J'], true)) {
            return new JsonResponse(['error' => "tipo_persona '{$tipoPersona}' no válido. Opciones: N (natural), J (jurídica)."], 400);
        }""", c)

with open('app/Modules/TipoDocumentoIdentidad/Presentation/Controllers/TipoDocumentoIdentidadController.php', 'w') as f:
    f.write(c)

