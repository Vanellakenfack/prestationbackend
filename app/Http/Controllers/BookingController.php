<?php

// app/Http/Controllers/BookingController.php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        
        abort_unless($user->isClient(), 403, 'Seuls les clients peuvent créer des réservations');

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'notes' => 'nullable|string'
        ]);

        // Vérification disponibilité
        $isAvailable = Booking::where('service_id', $validated['service_id'])
            ->where(function($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->doesntExist();

        if (!$isAvailable) {
            return response()->json(['message' => 'Créneau indisponible'], 409);
        }

        $booking = $user->clientBookings()->create([
            'service_id' => $validated['service_id'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'notes' => $validated['notes'] ?? null
        ]);

        return response()->json($booking->load('service'), 201);
    }

    public function getDisponibilites($prestataireId)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'required|integer|min:1' // en heures
        ]);

        $bookings = Booking::whereHas('service', fn($q) => 
                $q->where('prestataire_id', $prestataireId)
            )
            ->whereDate('start_time', $request->date)
            ->get();

        // Logique de calcul des créneaux disponibles
        $disponibilites = $this->calculateSlots($request->date, $request->duration, $bookings);

        return response()->json($disponibilites);
    }

    private function calculateSlots($date, $duration, $bookings)
    {
        // Implémentez votre logique de calcul des créneaux
        // Retourne un tableau des créneaux disponibles
    }
}