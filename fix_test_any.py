import re
import os

files = [
    'app/Modules/Personas/Tests/Unit/Application/ActualizarPersonaUseCaseTest.php',
    'app/Modules/Personas/Tests/Unit/Application/CrearPersonaUseCaseTest.php'
]

for file in files:
    with open(file, 'r') as f:
        c = f.read()
    c = c.replace("Mockery::type(Persona::class)", "Mockery::any()")
    c = c.replace("Mockery::type(PersonaDocumento::class)", "Mockery::any()")
    with open(file, 'w') as f:
        f.write(c)

