<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\View\View;

use App\Models\User;

class RegisterController extends Controller
{
    /**
     * Display a login form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);
        
        $username = $this->generateUsername($request->name);

        User::create([
            'name' => $request->name,
            'username' => $username,
            'imagepath' => "images/profiles/teste.png",
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'description' => "No description provided.",
            'balance' => 0.0,
            'avgrating' => 0.0,
            'isbanned' => false,
            'isadmin' => false,
            'number_of_bids' => 0.0,
            'number_of_auctions' => 0.0,
            'auctions_followed' => 0.0,
        ]);


        $credentials = $request->only('email', 'password');
        Auth::attempt($credentials);
        $request->session()->regenerate();
        return redirect()->route('home')
            ->withSuccess('You have successfully registered & logged in!');
    }

        /**
     * Generate a unique username based on the name.
     *
     * @param string $name
     * @return string
     */
    private function generateUsername(string $name): string
    {
        $baseUsername = \Str::of($name)->lower()->replace(' ', '');

        $username = $baseUsername . random_int(1000, 9999);

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . random_int(1000, 9999);
        }

        return $username;
    }
}
