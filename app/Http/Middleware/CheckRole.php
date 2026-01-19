<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Cek apakah user sudah login
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Cek apakah user memiliki role yang sesuai
        // Asumsi: Di model User ada relasi 'role' yang mengarah ke tabel roles
        if ($request->user()->role->name !== $role) {
            return response()->json(['message' => 'Forbidden: Access denied.'], 403);
        }

        return $next($request);
    }
}
