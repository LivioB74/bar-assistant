<?php
declare(strict_types=1);

namespace Kami\Cocktail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken('web_app_login');

            return response()->json(['token' => $token->plainTextToken]);
        }

        return response()->json(['type' => 'api_error', 'message' => 'Login failed']);
    }
}
