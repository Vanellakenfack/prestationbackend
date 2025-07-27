<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profil extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'adresse',
        'ville',
        'quartier',
        'competences',
        'experiences',
        'reseaux',
        'photo',
        'cv',
        'portfolio',
        'video',
        // Nouveaux champs ajoutés
        'bio',
        'website',
        'hourly_rate',
    ];

    protected $casts = [
        'competences' => 'array', // Si vous voulez qu'il soit automatiquement un tableau
        'experiences' => 'string', // Si vous le stockez comme une chaîne de texte
        'reseaux' => 'array',     // Si vous voulez qu'il soit automatiquement un tableau associatif
        'hourly_rate' => 'float', // Pour s'assurer que c'est un nombre décimal
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

