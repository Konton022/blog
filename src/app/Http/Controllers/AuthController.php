<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request){
        // Проверяем наличие пользователя с таким email
        $existingUser = User::whereEmail($request->email)->first();

        if ($existingUser) {
            return response()->json(['message' => 'Пользователь с таким email уже существует'], 409); // Код состояния 409 означает конфликт
        }

        // Валидируем данные
        $validatedData = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users' ],
            'password' => ['required'],
        ]);

        // Создаем нового пользователя
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Создаем токен для пользователя
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token]);
    }

    public function login(Request $request)
    {
    // Валидируем входящие данные
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    // Проверяем, существует ли пользователь с указанным email
    $user = User::where('email', $credentials['email'])->first();

    if (!$user) {
        return response()->json(['message' => 'Неверный email или пароль'], 401);
    }

    // Пробуем войти с указанными учетными данными
    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Неверный email или пароль'], 401);
    }

    // Если все прошло успешно, создаем токен
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json(['access_token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}

