<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{

    // Register
    public function register(RegisterRequest $request)
    {
        $customerId = Role::where('name', 'customer')->firstOrFail()->id;

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'role_id' => $request['role_id'] ?? $customerId,
        ]);
        return response()
            ->json([
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
    }

    // Login
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()
                ->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'message' => 'login Berhasil Sebagai ' . $user->name,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()
            ->json(['message' => 'Logged out successfully']);
    }
}
