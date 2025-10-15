<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserInfluencer;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Auth as FirebaseAuth;

class AuthController extends Controller
{

    protected $firebaseAuth;

    public function __construct()
    {
        // Usar base_path con la ruta del .env
        $path = base_path(env('FIREBASE_CREDENTIALS'));

        if (!file_exists($path)) {
            throw new \Exception("Firebase file not found: {$path}");
        }

        if (is_dir($path)) {
            throw new \Exception("Firebase path is a directory, not a file: {$path}");
        }

        $factory = (new Factory)->withServiceAccount($path);
        $this->firebaseAuth = $factory->createAuth();
    }
    /**
     * Verificar el token de Firebase y autenticar al usuario
     */

    public function verifyToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            // Verificar el token con Firebase
            $verified = $this->firebaseAuth->verifyIdToken($request->token);

            // Obtener datos del token
            $uid = $verified->claims()->get('sub');
            $phone = $verified->claims()->get('phone_number');

            // Limpiar número (quitar +51 y espacios)
            $clean = str_replace(['+51', ' '], '', $phone);

            // Buscar usuario en BD
            $user = UserInfluencer::where('phone_number', $clean)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'No se encontró un usuario con ese teléfono.'
                ], 404);
            }

            // Actualizar firebase_uid solo en el primer login
            if (!$user->firebase_uid) {
                $user->firebase_uid = $uid;
                $user->save();
            }

            // Autenticar al usuario en Laravel
            Auth::login($user);

            // Crear token de Sanctum
            $token = $user->createToken('Personal Access Token')->plainTextToken;

            // 🔥 Retornar en el MISMO formato que tu login que funciona
            return response()->json([
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'userData' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'social_handle' => $user->social_handle,
                    'code' => $user->code,
                    'code_description' => $user->code_description,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
            ]);
        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al verificar el token: ' . $e->getMessage()], 500);
        }
    }

    public function checkPhoneNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $phoneNumber = preg_replace('/\s+/', '', $request->input('phone_number'));
        $phoneNumber = str_replace('+51', '', $phoneNumber); // Elimina el +51

        $exists = UserInfluencer::where('phone_number', $phoneNumber)->exists();

        if (!$exists) {
            return response()->json([
                'exists' => false,
                'message' => 'El número ingresado no está registrado.',
            ], 404);
        }

        return response()->json([
            'exists' => true,
            'message' => 'Número válido. Puede continuar con la verificación por SMS.',
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // Eliminar el token actual del usuario
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Sesión cerrada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión: ' . $e->getMessage()
            ], 500);
        }
    }
}
