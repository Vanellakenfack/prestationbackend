<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    // BookingController.php
public function store(Request $request)
{
    $user = Auth::user();
    
    if (!$user->isClient()) {
        return response()->json(['message' => 'Accès refusé'], 403);
    }

    $validated = $request->validate([
        'service_id' => 'required|exists:services,id',
        'prestataire_id' => 'required|exists:prestataires,id', // Ajouté
        'start_time' => 'required|date|after:now',
        'end_time' => 'required|date|after:start_time',
        'notes' => 'nullable|string'
    ]);

    // Vérification que le service appartient bien au prestataire
    $service = Service::find($validated['service_id']);
    if ($service->prestataire_id != $validated['prestataire_id']) {
        return response()->json(['message' => 'Incohérence service/prestataire'], 400);
    }

    // Vérification disponibilité
    $isAvailable = !Booking::where('prestataire_id', $validated['prestataire_id'])
        ->where(function($q) use ($validated) {
            $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
              ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
        })
        ->exists();

    if (!$isAvailable) {
        return response()->json(['message' => 'Créneau déjà réservé'], 409);
    }

    $booking = Booking::create([
        'client_id' => $user->client->id,
        'service_id' => $validated['service_id'],
        'prestataire_id' => $validated['prestataire_id'], // Ajouté
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'notes' => $validated['notes']
    ]);

    return response()->json($booking->load(['service', 'prestataire']), 201);
}
}