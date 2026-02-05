<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class SanctumIdleTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $lastUsed = $token->last_used_at ?? $token->created_at;

        // DEBUG (sementara)
        \Log::info('IDLE CHECK', [
            'token_id' => $token->id,
            'last_used_at' => $lastUsed,
            'now' => now(),
            'diff_seconds' => Carbon::parse($lastUsed)->diffInSeconds(now()),
        ]);

        if (Carbon::parse($lastUsed)->diffInSeconds(now()) > 60) {
            $token->delete();

            return response()->json([
                'message' => 'Session expired due to inactivity'
            ], 401);
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $next($request);
    }
}
