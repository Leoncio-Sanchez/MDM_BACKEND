with open('app/Modules/Giros/Tests/Feature/GiroTest.php', 'r') as f:
    c = f.read()

import re

c = re.sub(r"private function getAuthUser\(\): User \{.*?\s+return \$user;\n    \}", r"""private function getAuthUser(): User {
        $existing = User::where('username', 'test_user')->first();
        if ($existing) return $existing;

        $id = DB::table('auth_user')->insertGetId([
            'username' => 'test_user',
            'password' => 'secret',
            'is_active' => true,
        ]);
        return User::find($id);
    }""", c, flags=re.DOTALL)

with open('app/Modules/Giros/Tests/Feature/GiroTest.php', 'w') as f:
    f.write(c)
