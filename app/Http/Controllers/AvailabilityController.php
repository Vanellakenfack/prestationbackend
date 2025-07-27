<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disponibilite; // Assurez-vous d'avoir ce modèle
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Ajouté pour une meilleure gestion des erreurs

class AvailabilityController extends Controller
{
    /**
     * Récupère les disponibilités d'un prestataire pour une période donnée.
     * Le frontend appelle cette méthode pour afficher le calendrier.
     */
    public function index(Request $request, $prestataireId)
    {
        $user = Auth::user();
        // Vérification de sécurité: S'assurer que le prestataire authentifié correspond à l'ID demandé
        if (!$user || $user->id != $prestataireId) {
            abort(403, 'Accès non autorisé aux disponibilités de ce prestataire.');
        }

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $availabilities = Disponibilite::where('prestataire_id', $prestataireId)
                                        ->when($startDate, fn($query) => $query->where('date', '>=', $startDate))
                                        ->when($endDate, fn($query) => $query->where('date', '<=', $endDate))
                                        ->get();

        // Formater la réponse pour correspondre à l'état du frontend
        // Le frontend attend un objet où la clé est la date (YYYY-MM-DD) et la valeur est un objet de créneaux
        $formattedAvailabilities = $availabilities->mapWithKeys(function ($item) {
            // Grâce au cast dans le modèle Disponibilite, $item->date est maintenant un objet Carbon
            return [
                $item->date->format('Y-m-d') => [ // Ligne 32 de votre erreur
                    'morning' => (bool)$item->morning,
                    'afternoon' => (bool)$item->afternoon,
                    'evening' => (bool)$item->evening,
                ]
            ];
        });

        return response()->json($formattedAvailabilities);
    }

    /**
     * Met à jour ou crée en masse les disponibilités d'un prestataire.
     * Le frontend appelle cette méthode lors de la sauvegarde des disponibilités.
     */
    public function batchUpdate(Request $request, $prestataireId)
    {
        $user = Auth::user();
        // Vérification de sécurité
        if (!$user || $user->id != $prestataireId) {
            abort(403, 'Action non autorisée.');
        }

        $data = $request->json()->all(); // Le frontend envoie un tableau d'objets disponibilité

        // Vous pouvez ajouter une validation ici pour chaque élément du tableau $data
        // foreach ($data as $item) {
        //     $validator = Validator::make($item, [
        //         'date' => 'required|date',
        //         'slots.morning' => 'boolean',
        //         'slots.afternoon' => 'boolean',
        //         'slots.evening' => 'boolean',
        //     ]);
        //     if ($validator->fails()) { /* gérer l'erreur */ }
        // }

        foreach ($data as $item) {
            Disponibilite::updateOrCreate(
                ['prestataire_id' => $prestataireId, 'date' => $item['date']],
                [
                    'morning' => $item['slots']['morning'] ?? false,
                    'afternoon' => $item['slots']['afternoon'] ?? false,
                    'evening' => $item['slots']['evening'] ?? false
                ]
            );
        }

        return response()->json(['message' => 'Disponibilités mises à jour avec succès.'], 200);
    }
}
