<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Affiche le profil de l'utilisateur authentifié
     */
    public function show()
    {
        $user = auth()->user();
        
        $data = $user->load([
            'prestataire' => function($query) {
                $query->withDefault([
                    'metier' => null,
                    'bio' => null,
                    'competences' => null,
                    'tarif_horaire' => null
                ]);
            },
            'client' => function($query) {
                $query->withDefault([
                    'adresse' => null,
                    'telephone' => null,
                    'ville' => null,
                    'code_postal' => null
                ]);
            },
            'services' // Charge les services pour les prestataires
        ]);

        // Structure de réponse standardisée
        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'type' => $user->type,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ],
            'profile' => $user->type === 'prestataire' 
                ? $data->prestataire 
                : $data->client,
            'services' => $user->type === 'prestataire' ? $data->services : null
        ]);
    }

    /**
     * Met à jour le profil de l'utilisateur
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'current_password' => 'sometimes|required_with:new_password|current_password',
            'new_password' => 'sometimes|required_with:current_password|min:8|confirmed',
            
            // Champs prestataire
            'metier' => 'sometimes|required_if:type,prestataire|string|max:100',
            'bio' => 'sometimes|nullable|string|max:500',
            'competences' => 'sometimes|nullable|array',
            'tarif_horaire' => 'sometimes|nullable|numeric|min:0',
            
            // Champs client
            'adresse' => 'sometimes|required_if:type,client|string|max:255',
            'telephone' => 'sometimes|nullable|string|max:20',
            'ville' => 'sometimes|nullable|string|max:100',
            'code_postal' => 'sometimes|nullable|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mise à jour des infos de base
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('new_password')) {
            $user->password = bcrypt($request->new_password);
        }

        $user->save();

        // Mise à jour du profil spécifique
        if ($user->type === 'prestataire') {
            $user->prestataire()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only(['metier', 'bio', 'competences', 'tarif_horaire'])
            );
        } else {
            $user->client()->updateOrCreate(
                ['user_id' => $user->id],
                $request->only(['adresse', 'telephone', 'ville', 'code_postal'])
            );
        }

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'data' => $user->fresh()->load(['prestataire', 'client', 'services'])
        ]);
    }

    /**
     * Supprime le compte utilisateur
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        
        // Validation du mot de passe pour confirmation
        $request->validate([
            'password' => 'required|current_password'
        ]);

        $user->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès'
        ], 204);
    }
}