with open('app/Modules/TipoProcedimientoTupa/Presentation/Resources/TipoProcedimientoTupaResource.php', 'r') as f:
    c = f.read()

import re

c = c.replace("fechaDigitacion?->setTimezone", "fechaDigitacion->setTimezone")

with open('app/Modules/TipoProcedimientoTupa/Presentation/Resources/TipoProcedimientoTupaResource.php', 'w') as f:
    f.write(c)
