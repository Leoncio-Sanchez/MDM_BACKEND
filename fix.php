<?php
$str = file_get_contents('app/Modules/Licencias/Presentation/Resources/LicenciaResource.php');
$str = str_replace("->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP')", "->setTimezone('America/Lima')->toIso8601String()", $str);
file_put_contents('app/Modules/Licencias/Presentation/Resources/LicenciaResource.php', $str);

$str = file_get_contents('app/Modules/Licencias/Presentation/Resources/LicenciaListResource.php');
$str = str_replace("->setTimezone('America/Lima')->format('Y-m-d\TH:i:s.uP')", "->toISOString()", $str);
file_put_contents('app/Modules/Licencias/Presentation/Resources/LicenciaListResource.php', $str);
