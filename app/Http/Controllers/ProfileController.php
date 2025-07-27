<?php
// app/Http/Controllers/ProfilController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profil; // Assurez-vous que ce modèle existe
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Afficher le profil du prestataire connecté
    public function show()
    {
        // Tente de trouver le profil de l'utilisateur authentifié
        $profil = Profil::where('user_id', Auth::id())->first();

        // Si le profil n'existe pas, renvoie une réponse 404 ou un tableau vide
        // Le frontend peut alors interpréter cela comme l'absence de profil et passer en mode création
        if (!$profil) {
            return response()->json(null, 200); // Renvoie null avec un statut 200 OK
            // Ou vous pouvez renvoyer une 404 si vous voulez une gestion d'erreur plus stricte
            // abort(404, 'Profil non trouvé.');
        }

        // Décodage des champs JSON avant de les envoyer au frontend
        // Assurez-vous que ces champs sont stockés en JSON dans la DB
        if ($profil->competences && is_string($profil->competences)) {
            $profil->competences = json_decode($profil->competences, true);
        }
        if ($profil->experiences && is_string($profil->experiences)) {
            $profil->experiences = json_decode($profil->experiences, true);
        }
        if ($profil->reseaux && is_string($profil->reseaux)) {
            $profil->reseaux = json_decode($profil->reseaux, true);
        }

        return response()->json($profil);
    }

    // Mettre à jour le profil (ou le créer s'il n'existe pas)
    public function update(Request $request)
    {
        // Utilise firstOrCreate pour trouver le profil ou en créer un nouveau
        // Si le profil n'existe pas, il sera créé avec user_id
        $profil = Profil::firstOrCreate(['user_id' => Auth::id()]);

        // Valider les données entrantes
        $validatedData = $request->validate([
            'phone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'quartier' => 'nullable|string|max:255',
            'competences' => 'nullable|string', // Reçoit une chaîne séparée par des virgules
            'experiences' => 'nullable|string',
            'reseaux' => 'nullable|string', // Reçoit une chaîne JSON
            'photo' => 'nullable|image|max:2048', // max 2MB
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'portfolio' => 'nullable|string', // Peut être un fichier ou une URL
            'video' => 'nullable|string',     // Peut être un fichier ou une URL
            // Nouveaux champs ajoutés pour la validation
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $dataToUpdate = $validatedData;

        // Gestion des uploads de fichiers (photo, cv, portfolio, video)
        // Pour les fichiers, stockez-les et mettez à jour le chemin
        // Pour les liens (portfolio, video), stockez l'URL directement

        if ($request->hasFile('photo')) {
            if ($profil->photo) Storage::disk('public')->delete($profil->photo);
            $dataToUpdate['photo'] = $request->file('photo')->store('profils/photos', 'public');
        }

        if ($request->hasFile('cv')) {
            if ($profil->cv) Storage::disk('public')->delete($profil->cv);
            $dataToUpdate['cv'] = $request->file('cv')->store('profils/cv', 'public');
        }

        // Pour portfolio et video, vérifiez si un fichier a été uploadé ou si un lien a été fourni
        if ($request->hasFile('portfolio')) {
            if ($profil->portfolio && str_starts_with($profil->portfolio, 'profils/portfolio')) {
                Storage::disk('public')->delete($profil->portfolio);
            }
            $dataToUpdate['portfolio'] = $request->file('portfolio')->store('profils/portfolio', 'public');
        } elseif ($request->filled('portfolio') && ! $request->hasFile('portfolio')) {
            $dataToUpdate['portfolio'] = $request->input('portfolio');
        } else {
            // Si ni fichier ni lien n'est fourni, et qu'il y avait un ancien fichier, le supprimer
            if ($profil->portfolio && str_starts_with($profil->portfolio, 'profils/portfolio')) {
                Storage::disk('public')->delete($profil->portfolio);
            }
            $dataToUpdate['portfolio'] = null;
        }

        if ($request->hasFile('video')) {
            if ($profil->video && str_starts_with($profil->video, 'profils/videos')) {
                Storage::disk('public')->delete($profil->video);
            }
            $dataToUpdate['video'] = $request->file('video')->store('profils/videos', 'public');
        } elseif ($request->filled('video') && ! $request->hasFile('video')) {
            $dataToUpdate['video'] = $request->input('video');
        } else {
            // Si ni fichier ni lien n'est fourni, et qu'il y avait un ancien fichier, le supprimer
            if ($profil->video && str_starts_with($profil->video, 'profils/videos')) {
                Storage::disk('public')->delete($profil->video);
            }
            $dataToUpdate['video'] = null;
        }

        // Encodage JSON pour competences, experiences, reseaux
        // Le frontend envoie déjà des chaînes JSON pour 'reseaux' et une chaîne pour 'competences'
        // 'competences' est une chaîne séparée par des virgules, il faut la convertir en tableau JSON
        if (isset($dataToUpdate['competences'])) {
            $dataToUpdate['competences'] = json_encode(explode(',', $dataToUpdate['competences']));
        }
        // 'experiences' est une chaîne de texte simple
        // 'reseaux' est déjà une chaîne JSON
        if (isset($dataToUpdate['reseaux']) && !empty($dataToUpdate['reseaux'])) {
            // Valider que c'est un JSON valide avant d'encoder
            json_decode($dataToUpdate['reseaux']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['message' => 'Le format des réseaux sociaux est invalide.'], 422);
            }
        } else {
            $dataToUpdate['reseaux'] = json_encode([]); // Si vide, stocker un JSON vide
        }
        
        // Supprimer les champs de fichiers temporaires du tableau de données si non utilisés
        unset($dataToUpdate['photo_file'], $dataToUpdate['cv_file'], $dataToUpdate['portfolio_file'], $dataToUpdate['video_file']);


        $profil->update($dataToUpdate);

        // Recharger le profil avec les relations ou les casts mis à jour
        return response()->json($profil->fresh());
    }
}
