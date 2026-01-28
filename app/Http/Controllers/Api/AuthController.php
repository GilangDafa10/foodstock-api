<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Tambahkan ini
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
        // Validasi input sudah ditangani oleh RegisterRequest
        // Kita ambil data yang sudah tervalidasi agar aman
        $validated = $request->validated();

        // Cari ID role customer
        // Gunakan firstOrFail agar jika role hilang, sistem melempar error 404 (bukan error 500 aneh)
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']), // Gunakan Hash::make
            'role_id'  => $customerRole->id, // 🔒 HARDCODE: Paksa jadi customer demi keamanan
        ]);

        // Opsional: Jika ingin user langsung login setelah register, 
        // Anda bisa generate token di sini juga. Tapi return message saja sudah cukup.

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user
        ], 201);
    }

    // Login
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Email atau Password salah' // Pesan error yang user-friendly
            ], 401);
        }

        $user = Auth::user();

        // Hapus token lama jika ingin single-device login (Opsional)
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,  // Kita pakai nama 'access_token'
            'token_type'   => 'Bearer',
            'user'         => $user,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        // Menghapus HANYA token yang sedang dipakai saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // Fetch User
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
