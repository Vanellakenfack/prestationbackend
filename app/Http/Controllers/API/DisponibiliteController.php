<?php

// app/Http/Controllers/API/DisponibiliteController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Disponibilite;
use App\Models\Prestataire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DisponibiliteController extends Controller
{
    // Ajoutez cette méthode dans DisponibiliteController.php
public function index($prestataireId)
{
    // Validation du prestataire
    $prestataire = Prestataire::find($prestataireId);
    
    if (!$prestataire) {
        return response()->json([
            'status' => 'error',
            'message' => 'Prestataire non trouvé'
        ], 404);
    }

    // Récupération de toutes les disponibilités
    $disponibilites = Disponibilite::where('prestataire_id', $prestataireId)
                        ->orderBy('jour', 'asc')
                        ->get();

    return response()->json([
        'status' => 'success',
        'prestataire' => $prestataire->only(['id', 'metier']),
        'disponibilites' => $disponibilites
    ]);
}
    public function creneauxDisponibles(Request $request, $prestataireId, $date)
    {
        // Validation
        $validator = Validator::make([
            'prestataire_id' => $prestataireId,
            'date' => $date,
            'duration' => $request->input('duration', 1)
        ], [
            'prestataire_id' => 'required|exists:prestataires,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'duration' => 'sometimes|integer|min:1|max:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Récupération du prestataire
        $prestataire = Prestataire::findOrFail($prestataireId);
        $duration = $request->input('duration', 1);

        // Gestion de la disponibilité
        $disponibilite = Disponibilite::firstOrCreate(
            ['prestataire_id' => $prestataireId, 'jour' => $date],
            array_merge(
                $prestataire->horairesParDefaut(),
                ['est_disponible' => true]
            )
        );

        if (!$disponibilite->est_disponible) {
            return response()->json([
                'status' => 'success',
                'message' => 'Prestataire non disponible ce jour',
                'creneaux' => []
            ]);
        }

        // Génération des créneaux
        $creneaux = $this->genererCreneaux(
            $date,
            $disponibilite->heure_debut,
            $disponibilite->heure_fin,
            $duration
        );

        return response()->json([
            'status' => 'success',
            'prestataire' => $prestataire->only(['id', 'metier']),
            'date' => $date,
            'duration_hours' => $duration,
            'creneaux' => $creneaux
        ]);
    }

    private function genererCreneaux($date, $heureDebut, $heureFin, $duration)
    {
        $creneaux = [];
        $start = Carbon::parse("$date $heureDebut");
        $end = Carbon::parse("$date $heureFin");
        $interval = 15; // minutes entre créneaux

        while ($start->copy()->addHours($duration) <= $end) {
            $creneaux[] = [
                'debut' => $start->format('H:i'),
                'fin' => $start->copy()->addHours($duration)->format('H:i'),
                'debut_iso' => $start->toIso8601String(),
                'fin_iso' => $start->copy()->addHours($duration)->toIso8601String(),
                'duration_minutes' => $duration * 60
            ];
            $start->addMinutes($interval);
        }

        return $creneaux;
    }
}