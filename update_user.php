<?php
$content = file_get_contents('app/Models/User.php');
$content = str_replace('use Tymon\JWTAuth\Contracts\JWTSubject;', "use Tymon\JWTAuth\Contracts\JWTSubject;\n\nclass User extends Authenticatable implements JWTSubject\n{\n    protected \$table = 'auth_user';\n    public \$timestamps = false;", $content);
// We need to make sure we don't duplicate the class definition
