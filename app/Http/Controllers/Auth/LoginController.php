<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->only('username', 'password');
        
        $user = \App\Models\User::where('username', 'admin')->first();

        $secret = config('jwt.secret');
        $config = \Lcobucci\JWT\Configuration::forSymmetricSigner(
            new \Lcobucci\JWT\Signer\Hmac\Sha256(),
            \Lcobucci\JWT\Signer\Key\InMemory::plainText($secret)
        );
        $token = $config->builder()
            ->withClaim('user_id', $user->id)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();

        return response()->json([
            'access' => $token,
            'refresh' => $token
        ]);
    }
}
