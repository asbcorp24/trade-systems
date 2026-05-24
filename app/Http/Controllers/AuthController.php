<?php


namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // ЛОГИН
    public function loginPage()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        // --- SuperAdmin вход по .env ---
        if (
            $request->login === env('SUPERADMIN_LOGIN') &&
            $request->password === env('SUPERADMIN_PASSWORD')
        ) {
            $user = User::firstOrCreate(
                ['login' => env('SUPERADMIN_LOGIN')],
                [
                    'name' => 'SuperAdmin',
                    'password' => env('SUPERADMIN_PASSWORD'),
                    'role' => 'superadmin'
                ]
            );

            Auth::login($user);
            return redirect('/');
        }

        // --- Обычный вход ---
        if (Auth::attempt(['login' => $request->login, 'password' => $request->password])) {
            return redirect('/');
        }

        return back()->withErrors(['login' => 'Неверный логин или пароль']);
    }


    // ВЫХОД
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
