<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin Command Center Login
     * Endpoint: POST /api/auth/login
     */
    public function loginAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial email atau password yang dimasukkan salah.'],
            ]);
        }

        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Akun Anda bukan Admin Command Center.',
            ], 403);
        }

        $token = $user->createToken('admin-token', ['role:admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Admin berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Sales Consultant Login
     * Endpoint: POST /api/auth/sales-login
     */
    public function loginSales(Request $request): JsonResponse
    {
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nik', $request->nik)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'nik' => ['NIK atau password Sales Consultant tidak sesuai.'],
            ]);
        }

        if ($user->role !== 'sales') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Akun Anda bukan Sales Consultant.',
            ], 403);
        }

        $token = $user->createToken('sales-token', ['role:sales'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Sales Consultant berhasil.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Logout & Revoke current token
     * Endpoint: POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diakhiri.',
        ]);
    }

    /**
     * Authenticated User Profile
     * Endpoint: GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }
}
