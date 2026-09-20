import re

with open('app/Modules/Personas/Application/UseCases/ActualizarPersonaUseCase.php', 'r') as f:
    c = f.read()

c = c.replace(
    "return $this->personas->buscarPorId($id);",
    "$ret = $this->personas->buscarPorId($id);\n            if (!$ret) throw PersonaNoEncontrada::porId($id);\n            return $ret;"
)

with open('app/Modules/Personas/Application/UseCases/ActualizarPersonaUseCase.php', 'w') as f:
    f.write(c)


with open('app/Modules/Personas/Application/UseCases/CrearPersonaUseCase.php', 'r') as f:
    c = f.read()

c = c.replace(
    "return $this->personas->buscarPorId((int)$persistida->id);",
    "$ret = $this->personas->buscarPorId((int)$persistida->id);\n            if (!$ret) throw new \\RuntimeException('Persona no encontrada tras crear');\n            return $ret;"
)

with open('app/Modules/Personas/Application/UseCases/CrearPersonaUseCase.php', 'w') as f:
    f.write(c)

