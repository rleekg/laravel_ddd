<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Presentation\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('app');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'login' => $request->input('login'),
            'password' => $request->input('password'),
        ];

        if (! Auth::attempt($credentials, false)) {
            return response()->json(['message' => 'Неверный логин или пароль.'], 401);
        }

        $request->session()->regenerate();

        return response()->json(['redirect' => '/']);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['redirect' => '/login']);
    }
}
