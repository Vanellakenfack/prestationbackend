<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestataire_id',
        'titre',
        'description',
        'categorie',
        'prix',
        'unite_prix',
        'localisation',
        'disponible',
        'photos',
        'video'
    ];

    protected $casts = [
        'photos' => 'array',
        'disponible' => 'boolean'
    ];

    // Relation avec le prestataire (un User de type 'prestataire')
    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    // Méthode pour vérifier si le service appartient à un prestataire donné
    public function belongsToPrestataire($userId)
    {
        return $this->prestataire_id === $userId;
    }

    // Méthode pour formater le prix avec l'unité
    public function getFormattedPriceAttribute()
    {
        return "{$this->prix} fc/" . $this->unite_prix;
    }
    // app/Models/Service.php
public function bookings()
{
    return $this->hasMany(Booking::class);
}

// app/Models/Service.php
public function reviews()
{
    return $this->hasMany(Review::class);
}

public function averageRating()
{
    return $this->reviews()->avg('rating');
}
}