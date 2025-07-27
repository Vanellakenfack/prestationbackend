<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServiceController extends Controller
{
    /**
     * Liste paginée des services avec filtres
     */
    public function index(Request $request)
    {
        $cacheKey = 'services_'.md5(json_encode($request->all()));
        
        $services = Cache::remember($cacheKey, now()->addHours(1), function() use ($request) {
            $query = Service::query()->with(['prestataire', 'category']);

            // Filtres
            $query->when($request->filled('categorie_id'), fn($q) => 
                $q->where('categorie_id', $request->categorie_id)
            );

            $query->when($request->filled('localisation'), fn($q) =>
                $q->where('localisation', 'like', '%'.$request->localisation.'%')
            );

            $query->when($request->filled('prix_max'), fn($q) =>
                $q->where('prix', '<=', $request->prix_max)
            );

            $query->when($request->filled('search'), fn($q) =>
                $q->where(function($q) use ($request) {
                    $q->where('titre', 'like', '%'.$request->search.'%')
                      ->orWhere('description', 'like', '%'.$request->search.'%');
                })
            );

            // Tri
            $sort = $request->get('sort', 'created_at');
            $direction = $request->get('direction', 'desc');
            $query->orderBy($sort, $direction);

            return $query->paginate($request->get('per_page', 15));
        });

        return response()->json($services);
    }

    /**
     * Création d'un nouveau service
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->isPrestataire(), 403, 'Seuls les prestataires peuvent créer des services');

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'categorie_id' => 'required|exists:categories,id',
            'prix' => 'required|numeric|min:0',
            'unite_prix' => ['required', Rule::in(['heure', 'jour', 'forfait', 'unite'])],
            'localisation' => 'required|string|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'url', // Valide que chaque élément du tableau est une URL
            'video' => 'nullable|url'
        ]);

        // Assurez-vous que le service est lié à l'ID de l'utilisateur authentifié
        $service = $user->services()->create($validated);
        
        // Invalider le cache
        Cache::forget('services_*');

        return response()->json($service->load(['prestataire', 'category']), 201);
    }

    /**
     * Affichage d'un service spécifique
     */
    public function show(Service $service)
    {
        return response()->json($service->load(['prestataire', 'category']));
    }

    /**
     * Mise à jour d'un service
     */
    public function update(Request $request, Service $service)
    {
        $user = Auth::user();

        // Vérifiez que le prestataire connecté est bien le propriétaire du service
        abort_unless(
            $user->isPrestataire() && $service->prestataire_id === $user->id, 
            403, 
            'Action non autorisée'
        );

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'categorie_id' => 'sometimes|exists:categories,id',
            'prix' => 'sometimes|numeric|min:0',
            'unite_prix' => ['sometimes', Rule::in(['heure', 'jour', 'forfait', 'unite'])],
            'localisation' => 'sometimes|string|max:255',
            'disponible' => 'sometimes|boolean', // Ajouté si vous avez un champ 'disponible'
            'photos' => 'nullable|array',
            'photos.*' => 'url',
            'video' => 'nullable|url'
        ]);

        $service->update($validated);
        
        // Invalider le cache
        Cache::forget('services_*');

        return response()->json($service->fresh()->load(['prestataire', 'category']));
    }

    /**
     * Suppression d'un service
     */
    public function destroy(Service $service)
    {
        $user = Auth::user();

        abort_unless(
            $user->isPrestataire() && $service->prestataire_id === $user->id, 
            403, 
            'Action non autorisée'
        );

        $service->delete();
        
        // Invalider le cache
        Cache::forget('services_*');

        return response()->json(null, 204);
    }

    /**
     * Liste des services d'un prestataire spécifique
     * Cette fonction est celle appelée par le frontend Services.js
     */
    public function byPrestataire($prestataireId)
    {
        try {
            // S'assurer que l'ID du prestataire correspond à l'utilisateur authentifié pour la sécurité
            $user = Auth::user();
            if (!$user || $user->id != $prestataireId) {
                // Si l'utilisateur n'est pas authentifié ou ne correspond pas à l'ID demandé
                abort(403, 'Accès non autorisé aux services de ce prestataire.');
            }

            // Correction: Charger les services du prestataire avec la catégorie associée
            // Assurez-vous que la relation 'category' est définie dans votre modèle Service
            $services = Service::where('prestataire_id', $prestataireId)
                               ->with(['prestataire', 'category']) // Inclure 'category' et 'prestataire'
                               ->latest() // Garder le tri par défaut
                               ->get();

            return response()->json($services);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Prestataire non trouvé ou aucun service pour ce prestataire.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des services: ' . $e->getMessage()], 500);
        }
    }
}
