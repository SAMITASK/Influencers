<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function firebaseLogin(Request $request)
    {
        $request->validate([
            'uid' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        $uid = $request->input('uid');
        $phoneNumber = $request->input('phone_number');

        // 🔹 Buscar usuario existente
        $user = Influencer::where('firebase_uid', $uid)
            ->orWhere('phone_number', $phoneNumber)
            ->first();

        if (!$user) {
            // 🔹 Usuario no encontrado → retornar error
            return response()->json([
                'message' => 'No se encontró un usuario con ese teléfono.'
            ], 404);
        }

        // 🔹 Usuario encontrado → generar token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
