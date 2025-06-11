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
            $query = Service::query()->with('prestataire');

            // Filtres
            $query->when($request->filled('categorie'), fn($q) => 
                $q->where('categorie', $request->categorie)
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
            'categorie' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'unite_prix' => ['required', Rule::in(['heure', 'jour', 'forfait', 'unite'])],
            'localisation' => 'required|string|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'url',
            'video' => 'nullable|url'
        ]);

        $service = $user->services()->create($validated);
        
        // Invalider le cache
        Cache::forget('services_*');

        return response()->json($service->load('prestataire'), 201);
    }

    /**
     * Affichage d'un service spécifique
     */
    public function show($id)
    {
        try {
            $service = Service::with('prestataire')->findOrFail($id);
            return response()->json($service);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Service non trouvé',
                'errors' => ['id' => ['Le service demandé n\'existe pas']]
            ], 404);
        }
    }

    /**
     * Mise à jour d'un service
     */
    public function update(Request $request, Service $service)
    {
        $user = Auth::user();

        abort_unless(
            $user->isPrestataire() && $service->prestataire_id === $user->id, 
            403, 
            'Action non autorisée'
        );

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'categorie' => 'sometimes|string|max:255',
            'prix' => 'sometimes|numeric|min:0',
            'unite_prix' => ['sometimes', Rule::in(['heure', 'jour', 'forfait', 'unite'])],
            'localisation' => 'sometimes|string|max:255',
            'disponible' => 'sometimes|boolean',
            'photos' => 'nullable|array',
            'photos.*' => 'url',
            'video' => 'nullable|url'
        ]);

        $service->update($validated);
        
        // Invalider le cache
        Cache::forget('services_*');

        return response()->json($service->fresh()->load('prestataire'));
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
     */
    public function byPrestataire($prestataireId)
    {
        $services = Service::where('prestataire_id', $prestataireId)
            ->with('prestataire')
            ->latest()
            ->get();

        return response()->json($services);
    }
}