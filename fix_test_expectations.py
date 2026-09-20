import os

with open('app/Modules/Personas/Tests/Unit/Application/ActualizarPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

c = c.replace(
    "$repo->shouldReceive('actualizar')->with($personaActualizada)->once();",
    "$repo->shouldReceive('actualizar')->with(Mockery::type(Persona::class))->once();"
)
with open('app/Modules/Personas/Tests/Unit/Application/ActualizarPersonaUseCaseTest.php', 'w') as f:
    f.write(c)


with open('app/Modules/Personas/Tests/Unit/Application/CrearPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

# Change the id in dummy_persona to 1 instead of null, so that we can check `buscarPorId(1)`
c = c.replace(
    "$personaFake = Persona::crear(",
    "$personaFake = new Persona(1, App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona::Natural, App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', \\Carbon\\CarbonImmutable::now(), \\Carbon\\CarbonImmutable::now(), 1);"
)

c = c.replace(
    "$repo->shouldReceive('crear')->once()->andReturn($personaFake);",
    "$repo->shouldReceive('crear')->with(Mockery::type(Persona::class))->once()->andReturn($personaFake);"
)

c = c.replace(
    "$docRepo->shouldReceive('crear')->once();",
    "$docRepo->shouldReceive('crear')->with(Mockery::type(PersonaDocumento::class))->once();"
)

with open('app/Modules/Personas/Tests/Unit/Application/CrearPersonaUseCaseTest.php', 'w') as f:
    f.write(c)


with open('app/Modules/Personas/Tests/Unit/Application/EliminarPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

c = c.replace(
    "$persona = Persona::crear(",
    "$persona = new Persona(1, App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona::Natural, App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', \\Carbon\\CarbonImmutable::now(), \\Carbon\\CarbonImmutable::now(), 1);"
)

with open('app/Modules/Personas/Tests/Unit/Application/EliminarPersonaUseCaseTest.php', 'w') as f:
    f.write(c)


with open('app/Modules/Personas/Tests/Unit/Application/ListarDocumentosPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

c = c.replace(
    "$persona = Persona::crear(",
    "$persona = new Persona(1, App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona::Natural, App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', \\Carbon\\CarbonImmutable::now(), \\Carbon\\CarbonImmutable::now(), 1);"
)

with open('app/Modules/Personas/Tests/Unit/Application/ListarDocumentosPersonaUseCaseTest.php', 'w') as f:
    f.write(c)


with open('app/Modules/Personas/Tests/Unit/Application/ObtenerPersonaUseCaseTest.php', 'r') as f:
    c = f.read()

c = c.replace(
    "$persona = Persona::crear(",
    "$persona = new Persona(1, App\\Modules\\Personas\\Domain\\ValueObjects\\TipoPersona::Natural, App\\Modules\\Personas\\Domain\\ValueObjects\\Sexo::Masculino, 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', \\Carbon\\CarbonImmutable::now(), \\Carbon\\CarbonImmutable::now(), 1);"
)

with open('app/Modules/Personas/Tests/Unit/Application/ObtenerPersonaUseCaseTest.php', 'w') as f:
    f.write(c)

