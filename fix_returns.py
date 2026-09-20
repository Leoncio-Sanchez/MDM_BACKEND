import re

with open('app/Modules/Personas/Tests/Unit/Application/ActualizarPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

c = c.replace(
    "->shouldReceive('actualizar')->with(Mockery::any())->once();",
    "->shouldReceive('actualizar')->with(Mockery::any())->once()->andReturn($persona);"
)

with open('app/Modules/Personas/Tests/Unit/Application/ActualizarPersonaUseCaseTest.php', 'w') as f:
    f.write(c)

with open('app/Modules/Personas/Tests/Unit/Application/CrearPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

doc_fake = "new \\App\\Modules\\Personas\\Domain\\Entities\\PersonaDocumento(1, 1, 1, '123')"
c = c.replace(
    "->shouldReceive('crear')->with(Mockery::any())->once();",
    f"->shouldReceive('crear')->with(Mockery::any())->once()->andReturn({doc_fake});"
)

with open('app/Modules/Personas/Tests/Unit/Application/CrearPersonaUseCaseTest.php', 'w') as f:
    f.write(c)

