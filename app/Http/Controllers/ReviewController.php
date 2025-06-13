<?php
// app/Http/Controllers/ReviewController.php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(Request $request, Service $service)
    {
        $user = Auth::user();
        
        abort_unless($user->isClient(), 403, 'Seuls les clients peuvent laisser des avis');

        // Vérifier si l'utilisateur a déjà réservé ce service
        $hasBooked = $service->bookings()
            ->where('client_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        abort_unless($hasBooked, 403, 'Vous devez avoir réservé ce service pour pouvoir le noter');

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500'
        ]);

        $review = $service->reviews()->updateOrCreate(
            ['client_id' => $user->id],
            $validated
        );

        return response()->json([
            'message' => 'Avis enregistré',
            'review' => $review->load('client'),
            'average_rating' => $service->fresh()->averageRating()
        ], 201);
    }
}