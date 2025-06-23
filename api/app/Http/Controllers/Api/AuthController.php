<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    /**
     * Login and get token
     *
     * Authenticate a user and return a Bearer token for API usage.
     *
     * @header Accept-Language en
     * @bodyParam email string required The user's email. Example: user@example.com
     * @bodyParam password string required The user's password. Example: secret
     *
     * @response 200 {"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."}
     * @response 401 {"message": "Invalid credentials"}
     *
     * @group Authentication
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => __('messages.invalid_credentials')], 401);
        }
        $user = Auth::user();
        // Eliminar cualquier token anterior llamado 'api-token'
        if (method_exists($user, 'tokens')) {
            $user->tokens()->where('name', 'api-token')->delete();
        }
        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json(['token' => $token]);
    }

    /**
     * Logout (revoke token)
     *
     * Revoke the current user's token. Requires authentication.
     *
     * @header Accept-Language en
     * @authenticated
     * @response 200 {"message": "Logged out"}
     *
     * @group Authentication
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => __('messages.logged_out')]);
    }
}
