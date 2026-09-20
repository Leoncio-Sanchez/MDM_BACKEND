import re
import os

base = 'app/Modules/Personas/Tests/Unit/Application'

dummy_persona = """Persona::crear(
        App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona::Natural,
        App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo::Masculino,
        'Juan', 'Dir', 'Dist', 'Prov', 'Dep', 1
    )"""

def replace_mock(filename):
    with open(f"{base}/{filename}", "r") as f:
        c = f.read()

    # This replaces $persona = Mockery::mock(Persona::class); with actual instance
    c = c.replace("$persona = Mockery::mock(Persona::class);", f"$persona = {dummy_persona};")
    c = c.replace("$personaFake = Mockery::mock(Persona::class);\n    $personaFake->id = 1;", f"$personaFake = {dummy_persona};")
    c = c.replace("$personaActualizada = Mockery::mock(Persona::class);", f"$personaActualizada = {dummy_persona};")
    c = c.replace("$persona->shouldReceive('actualizar')->once()->andReturn($personaActualizada);", "")
    
    with open(f"{base}/{filename}", "w") as f:
        f.write(c)

for file in os.listdir(base):
    if file.endswith('.php'):
        replace_mock(file)

