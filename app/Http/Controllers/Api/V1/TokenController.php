<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TokenController extends Controller
{
    public function generateToken(Request $request)
    {
        $user = $request->user() ?? \App\Models\User::first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $token = $user->createToken('registration_token', ['create-user'])->plainTextToken;

        $expiresAt = Carbon::now()->addMinutes(40);
        $user->tokens()->where('token', hash('sha256', explode('|', $token)[1]))->update([
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'token' => $token,
            'expires_at' => $expiresAt,
        ], 200);
    }
}
