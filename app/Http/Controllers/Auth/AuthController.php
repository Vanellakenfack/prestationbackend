<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log; // Ajoutez cette ligne d'importation


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'type' => 'required|in:client,prestataire'
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'type' => $validated['type']
        ]);

        if ($validated['type'] === 'prestataire') {
            $user->prestataire()->create($request->only(['metier', 'bio']));
        } else {
            $user->client()->create();
        }

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

   // app/Http/Controllers/Auth/AuthController.php
// app/Http/Controllers/Auth/AuthController.php
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