<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'type' => 'required|in:client,prestataire',
            'telephone' => 'required|string|max:20',
            'ville' => 'required|string|max:255'
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'type' => $validated['type'],
            'telephone' => $validated['telephone'],
            'ville' => $validated['ville']
        ]);

        if ($validated['type'] === 'prestataire') {
            $request->validate([
                'metier' => 'required|string|max:255',
                'bio' => 'nullable|string'
            ]);
            
            $user->prestataire()->create([
                'metier' => $request->metier,
                'bio' => $request->bio
            ]);
        } else {
            $user->client()->create();
        }

        return response()->json([
            'status' => 'success',
            'user' => $user->load(['client', 'prestataire'])
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user->load(['client', 'prestataire'])
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                Log::warning('Tentative de déconnexion sans authentification', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authentification requise',
                    'code' => 'UNAUTHENTICATED'
                ], 401);
            }

            $user->currentAccessToken()->delete();

            Log::info('Utilisateur déconnecté', ['user_id' => $user->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'Déconnexion réussie',
                'data' => [
                    'user_id' => $user->id,
                    'logout_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }
}