<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        $redirectUrl = sprintf(
            '%s/auth/google/callback',
            request()->getSchemeAndHttpHost()
        );

        // Configuração dinamica e redirecionamento
        return Socialite::driver('google')
            ->with(['redirect_uri' => $redirectUrl])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        // URI dinâmico
        $redirectUrl = sprintf(
            '%s/auth/google/callback',
            request()->getSchemeAndHttpHost()
        );
    
        // Obter dados do user via Google
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl($redirectUrl)
            ->user();
    
        // Criar um username baseado no email
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $googleUser->getEmail())[0]);
    
        // Verificar se o username já existe
        $uniqueUsername = $username;
        $count = 1;
        while (User::where('username', $uniqueUsername)->exists()) {
            $uniqueUsername = $username . $count;
            $count++;
        }
    
        // Criar o user
        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'username' => $uniqueUsername,
                'imagepath' => 'default.png',
                'description' => 'Default description', 
                'password' => bcrypt(uniqid()), 
            ]
        );
    
        Auth::login($user);
    
        return redirect()->intended('/');
    }
     
}
